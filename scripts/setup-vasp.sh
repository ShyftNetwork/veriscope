#!/bin/bash
set -e

VERISCOPE_SERVICE_HOST="${VERISCOPE_SERVICE_HOST:=unset}"
VERISCOPE_COMMON_NAME="${VERISCOPE_COMMON_NAME:=unset}"
VERISCOPE_TARGET="${VERISCOPE_TARGET:=unset}"
# INSTALL_ROOT="${VERISCOPE_INSTALL_ROOT:=/opt/veriscope}"
INSTALL_ROOT="/opt/veriscope"

# Check script is run with sudo
if [[ $EUID -ne 0 ]]; then
	echo "This script must be run with sudo or as root"
	exit 1
fi

# Check location of install
cd $INSTALL_ROOT
if [ $? -ne 0 ]; then
	echo "$INSTALL_ROOT not found"
	exit 1
fi
echo "+ Install root will be $INSTALL_ROOT"

# Load .env file
if [ -f ".env" ]; then
	set -o allexport
	source .env
	set +o allexport
fi

# Ensure necessary information is provided
if [ $VERISCOPE_SERVICE_HOST = 'unset' ]; then
	echo "Please set VERISCOPE_SERVICE_HOST in .env"
	exit 1
fi
if [ $VERISCOPE_COMMON_NAME = 'unset' ]; then
	echo "Please set VERISCOPE_COMMON_NAME in .env"
	exit 1
fi

# Rig variables based on chosen target
case "$VERISCOPE_TARGET" in
	"veriscope_testnet")
		ETHSTATS_HOST="wss://fedstats.veriscope.network/api"
		ETHSTATS_GET_ENODES="wss://fedstats.veriscope.network/primus/?_primuscb=1627594389337-0"
		ETHSTATS_SECRET="Oogongi4"
		;;

	"fed_testnet")
		ETHSTATS_HOST="wss://stats.testnet.shyft.network/api"
		ETHSTATS_SECRET="Ish9phieph"
		ETHSTATS_GET_ENODES="wss://stats.testnet.shyft.network/primus/?_primuscb=1627594389337-0"
		;;

	"fed_mainnet")
		ETHSTATS_HOST="wss://stats.shyft.network/api"
		ETHSTATS_SECRET="uL4tohChia"
		ETHSTATS_GET_ENODES="wss://stats.shyft.network/primus/?_primuscb=1627594389337-0"
		;;

	*)
		echo "Please set VERISCOPE_TARGET to veriscope_testnet, fed_testnet, or fed_mainnet"
		exit 1
		;;
esac

# No harm copying this file over and over. In fact, it might be necessary
# to copy this file in case of changes in contracts. However, the trust anchor PK,
# account, prefname etc. should be preserved as it is supplied by the user.
# This should happen after nethermind install step during node app install step
cp -n chains/$VERISCOPE_TARGET/ta-node-env veriscope_ta_node/.env


if [ -z "$(logname)" ]
then
SERVICE_USER=serviceuser
else
SERVICE_USER=$(logname)
fi


CERTFILE=/etc/letsencrypt/live/$VERISCOPE_SERVICE_HOST/fullchain.pem
CERTKEY=/etc/letsencrypt/live/$VERISCOPE_SERVICE_HOST/privkey.pem
SHARED_SECRET=

NETHERMIND_DEST=/opt/nm
NETHERMIND_CFG=$NETHERMIND_DEST/config.cfg
NETHERMIND_TARBALL="https://github.com/NethermindEth/nethermind/releases/download/1.20.4/nethermind-1.20.4-d06ec791-linux-x64.zip"
NETHERMIND_RPC="http://localhost:8545"

REDISBLOOM_DEST=/opt/RedisBloom
REDISBLOOM_TARBALL="https://github.com/ShyftNetwork/RedisBloom/archive/refs/tags/v2.4.5.zip"


NGINX_CFG=/etc/nginx/sites-enabled/ta-dashboard.conf

echo "+ Service user will be $SERVICE_USER"

# this function sets some globals to have a new ethereum PK, ACCT
# should be run only once, but we will ask this be done by the user, i.e. generate an ETH public/private key pair
# and enter the information/provide it to ansible via variable values
function create_sealer_pk {
	pushd >/dev/null $INSTALL_ROOT/veriscope_ta_node

	su $SERVICE_USER -c "npm install web3 dotenv"
	local OUTPUT=$(node -e 'require("./create-account").trustAnchorCreateAccount()')
	SEALERACCT=$(echo $OUTPUT | jq -r '.address')
	SEALERPK=$(echo $OUTPUT | jq -r '.privateKey');
	[[ $SEALERPK =~ 0x(.+) ]]
	SEALERPK=${BASH_REMATCH[1]}

	ENVDEST=.env
	sed -i "s#TRUST_ANCHOR_ACCOUNT=.*#TRUST_ANCHOR_ACCOUNT=$SEALERACCT#g" $ENVDEST
	sed -i "s#TRUST_ANCHOR_PK=.*#TRUST_ANCHOR_PK=$SEALERPK#g" $ENVDEST
	sed -i "s#TRUST_ANCHOR_PREFNAME=.*#TRUST_ANCHOR_PREFNAME=\"$VERISCOPE_COMMON_NAME\"#g" $ENVDEST
	sed -i "s#WEBHOOK_CLIENT_SECRET=.*#WEBHOOK_CLIENT_SECRET=$SHARED_SECRET#g" $ENVDEST

	popd >/dev/null
}

function install_redis {
	DEBIAN_FRONTEND=noninteractive apt-get -qq -y -o Acquire::https::AllowRedirect=false install redis-server
	cp /etc/redis/redis.conf /etc/redis/redis.conf.bak
	sed 's/^supervised.*/supervised systemd/' /etc/redis/redis.conf >> /etc/redis/redis.conf.new
	cp /etc/redis/redis.conf.new /etc/redis/redis.conf

	systemctl restart redis.service
}

function install_redis_bloom {

	if [ -f "/etc/redis/redis.conf" ]; then
	 cd /opt
	 rm -rf RedisBloom /tmp/buildresult
	 apt-get install -y cmake build-essential

	 wget -q -O /tmp/redisbloom-dist.zip "$REDISBLOOM_TARBALL"
	 unzip -qq -o -d $REDISBLOOM_DEST /tmp/redisbloom-dist.zip

	 cd RedisBloom/RedisBloom-2.4.5 && make | tee /tmp/buildresult
	 export MODULE=`tail -n1 /tmp/buildresult | awk '{print $2}' | sed 's/\.\.\.//'`
	 grep -v redisbloom /etc/redis/redis.conf >/tmp/redis.conf
	 echo "loadmodule $MODULE" | sudo tee --append /tmp/redis.conf
	 mv /tmp/redis.conf /etc/redis/redis.conf
	 systemctl restart redis-server

	 sed -i 's/^.*post_max_size.*/post_max_size = 128M/' /etc/php/8.0/fpm/php.ini
	 sed -i 's/^.*upload_max_filesize .*/upload_max_filesize = 128M/'  /etc/php/8.0/fpm/php.ini
	 if grep -q client_max_body_size $NGINX_CFG; then
    echo "NGINX config already has been already updated"
	 else
  	sed -i 's/listen 443 ssl;/listen 443 ssl;\n	client_max_body_size 128M;/' $NGINX_CFG
	 fi

	 pushd >/dev/null $INSTALL_ROOT/veriscope_ta_dashboard
 	 chown -R $SERVICE_USER .
	 su $SERVICE_USER -c "composer update"

	 systemctl restart php8.0-fpm
 	 systemctl restart nginx

	else
		echo "Redis server is not installed"
	fi

}

function create_postgres_trustanchor_db {
	if su postgres -c "psql -t -c '\du'" | cut -d \| -f 1 | grep -qw trustanchor; then
		echo "Postgres user trustanchor already exists."
	else
		PGPASS=$(pwgen -B 20 1)
		PGDATABASE=trustanchor
		PGUSER=trustanchor

		sudo -u postgres psql -c "create user $PGUSER  with createdb login password '$PGPASS'" || { echo "Postgres user creation failed"; exit 1; }
		sudo -u postgres psql -c "create database $PGDATABASE owner $PGUSER" || { echo "Postgres database creation failed"; exit 1; }

		ENVDEST=$INSTALL_ROOT/veriscope_ta_dashboard/.env
		sed -i "s#DB_CONNECTION=.*#DB_CONNECTION=pgsql#g" $ENVDEST
		sed -i "s#DB_HOST=.*#DB_HOST=localhost#g" $ENVDEST
		sed -i "s#DB_PORT=.*#DB_PORT=5432#g" $ENVDEST
		sed -i "s#DB_DATABASE=.*#DB_DATABASE=$PGDATABASE#g" $ENVDEST
		sed -i "s#DB_USERNAME=.*#DB_USERNAME=$PGUSER#g" $ENVDEST
		sed -i "s#DB_PASSWORD=.*#DB_PASSWORD=$PGPASS#g" $ENVDEST

		echo "  New postgres user $PGUSER / password $PGPASS / database $PGDATABASE "
	fi
}

function refresh_dependencies() {
  apt-get -y  update
  apt-get install -y software-properties-common curl sudo wget build-essential systemd netcat
	add-apt-repository >/dev/null -yn ppa:ondrej/php
	add-apt-repository >/dev/null -yn ppa:ondrej/nginx
	# nodesource's script does an apt update
	curl -fsSL https://deb.nodesource.com/setup_14.x | sudo -E bash -

	DEBIAN_FRONTEND=noninteractive apt -y upgrade

	DEBIAN_FRONTEND=noninteractive apt-get -qq -y -o Acquire::https::AllowRedirect=false install  vim git libsnappy-dev libc6-dev libc6 unzip make jq ntpdate moreutils php8.0-fpm php8.0-dom php8.0-zip php8.0-mbstring php8.0-curl php8.0-dom php8.0-gd php8.0-imagick php8.0-pgsql php8.0-gmp php8.0-redis php8.0-mbstring nodejs build-essential postgresql nginx pwgen certbot
	apt-get install -y protobuf-compiler libtiff5-dev libjpeg8-dev libopenjp2-7-dev zlib1g-dev \
    libfreetype6-dev liblcms2-dev libwebp-dev tcl8.6-dev tk8.6-dev python3-tk python3-pip \
    libharfbuzz-dev libfribidi-dev libxcb1-dev
  git config --global url."https://github.com/".insteadOf git@github.com:
	git config --global url."https://".insteadOf git://
	pg_ctlcluster 12 main start
	if ! command -v wscat; then
		npm install -g wscat
	fi

	SHARED_SECRET=$(pwgen -B 10 1)

	# force upgrade composer by reinstalling
	# from https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md
	EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"
	if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]
	then
		>&2 echo 'ERROR: Invalid php conmposer installer checksum'
		rm composer-setup.php
		exit 1
	fi
	php composer-setup.php --install-dir="/usr/local/bin/" --filename=composer --2
	rm composer-setup.php

  if [ $SERVICE_USER == "serviceuser" ]; then
   chown -R $SERVICE_USER /opt/veriscope/
  fi


	cp scripts/ntpdate /etc/cron.daily/
	cp scripts/journald /etc/cron.daily/
	chmod +x /etc/cron.daily/journald
	chmod +x /etc/cron.daily/ntpdate

	/etc/cron.daily/ntpdate
	return 0
}

function install_or_update_nethermind() {
	echo "Installing Nethermind to $NETHERMIND_DEST - target $VERISCOPE_TARGET chain - configuration will be in $NETHERMIND_CFG"

	wget -q -O /tmp/nethermind-dist.zip "$NETHERMIND_TARBALL"
	rm -rf $NETHERMIND_DEST/plugins
	unzip -qq -o -d $NETHERMIND_DEST /tmp/nethermind-dist.zip
	rm -rf $NETHERMIND_DEST/chainspec
	rm -rf $NETHERMIND_DEST/configs

	echo "Installing /opt/nm/shyftchainspec.json genesis file and static node list."
	cp chains/$VERISCOPE_TARGET/static-nodes.json $NETHERMIND_DEST
	cp chains/$VERISCOPE_TARGET/shyftchainspec.json $NETHERMIND_DEST

	if ! test -s "/etc/systemd/system/nethermind.service"; then
		echo "Installing systemd unit for nethermind"
		cp scripts/nethermind.service /etc/systemd/system/nethermind.service
		sed -i "s/User=.*/User=$SERVICE_USER/g" /etc/systemd/system/nethermind.service
		systemctl daemon-reload
	fi

	if ! test -s $NETHERMIND_CFG; then
		echo "Installing default $NETHERMIND_CFG"
		create_sealer_pk
		echo "New sealer ACCOUNT/PK will be $SEALERACCT, $SEALERPK"
		echo "MAKE A NOTE OF THIS SOMEPLACE SAFE"
		echo '{
			"Init": {
				"WebSocketsEnabled": true,
				"StoreReceipts" : true,
				"EnableUnsecuredDevWallet": false,
				"IsMining": true,
				"ChainSpecPath": "shyftchainspec.json",
				"BaseDbPath": "nethermind_db/vasp",
				"LogFileName": "/var/log/nethermind.log",
				"StaticNodesPath": "static-nodes.json",
				"DiscoveryEnabled": true,
	                        "PeerManagerEnabled": true
			},
			"Network": {
				"DiscoveryPort": 30303,
				"P2PPort": 30303,
				"OnlyStaticPeers": false,
                                "StaticPeers": null
			},
			"JsonRpc": {
				"Enabled": true,
				"Host": "0.0.0.0",
				"Port": 8545,
				"EnabledModules": ["Eth", "Parity", "Subscribe", "Trace", "TxPool", "Web3", "Personal", "Proof", "Net", "Health", "Rpc"]
			},
			"Aura": {
				"ForceSealing": true,
				"AllowAuRaPrivateChains": true
			},
		        "HealthChecks": {
                                "Enabled": true,
	                        "UIEnabled": false,
	                        "PollingInterval": 10,
	                        "Slug": "/health"
                        },
			"Pruning": {
                                "Enabled": false
                        },
			"EthStats": {
				"Enabled": true,
				"Contact": "not-yet",
				"Secret": "'$ETHSTATS_SECRET'",
				"Name": "'$VERISCOPE_SERVICE_HOST'",
				"Server": "'$ETHSTATS_HOST'"
			}
		}' >  $NETHERMIND_CFG
	fi

	      echo "Restarting nethermind...."
        if [ $SERVICE_USER == "serviceuser" ]; then
         chown -R $SERVICE_USER /opt/nm/
        else
         chown -R $SERVICE_USER:$SERVICE_USER /opt/nm/
        fi

        systemctl restart nethermind
}

# explore automatic SSL cert renewal
function setup_or_renew_ssl {
	systemctl stop nginx
	certbot certonly -n --agree-tos   --register-unsafely-without-email --standalone --preferred-challenges http   -d $VERISCOPE_SERVICE_HOST || { echo "Certbot failed to get a certificate"; exit 1; }
	if [ -f $CERTFILE ]; then
		echo "Found $CERTFILE";
	else
		echo "Couldn't find certificate file $CERTFILE"
	fi
	systemctl restart nginx
}

# after SSL step. ONLY ONCE. If re-run same info should be set
function setup_nginx {
	sed -i "s/user .*;/user $SERVICE_USER www-data;/g" /etc/nginx/nginx.conf

	echo '
	server {
		listen 80;
		server_name '$VERISCOPE_SERVICE_HOST';
		rewrite ^/(.*)$ https://'$VERISCOPE_SERVICE_HOST'$1 permanent;
	}

	server {
		listen 443 ssl;
		server_name '$VERISCOPE_SERVICE_HOST';
		root '$INSTALL_ROOT'/veriscope_ta_dashboard/public;

		ssl_certificate     '$CERTFILE';
		ssl_certificate_key '$CERTKEY';
		ssl_protocols       TLSv1 TLSv1.1 TLSv1.2;
		ssl_ciphers         HIGH:!aNULL:!MD5;

		add_header X-Frame-Options "SAMEORIGIN";
		add_header X-XSS-Protection "1; mode=block";
		add_header X-Content-Type-Options "nosniff";

		index index.html index.htm index.php;

		charset utf-8;

		location /arena/ {
			proxy_pass  http://127.0.0.1:8080/arena/;
			proxy_set_header Host $host;
			proxy_set_header X-Real-IP $remote_addr;
			proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
		}

		location / {
			try_files $uri $uri/ /index.php?$query_string;
		}

		location = /favicon.ico { access_log off; log_not_found off; }
		location = /robots.txt  { access_log off; log_not_found off; }

		error_page 404 /index.php;

		location ~ \.php$ {
			fastcgi_split_path_info ^(.+\.php)(/.+)$;
			fastcgi_pass unix:/var/run/php/php-fpm.sock;
			fastcgi_index index.php;
			fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
			include fastcgi_params;
		}

		location ~ /\.(?!well-known).* {
			deny all;
		}

		location /app/websocketkey {
			proxy_pass             http://127.0.0.1:6001;
			proxy_set_header Host  $host;
			proxy_set_header X-Real-IP  $remote_addr;
			proxy_set_header X-VerifiedViaNginx yes;
			proxy_read_timeout                  60;
			proxy_connect_timeout               60;
			proxy_redirect                      off;

			# Allow the use of websockets
			proxy_http_version 1.1;
			proxy_set_header Upgrade $http_upgrade;
			proxy_set_header Connection 'upgrade';
			proxy_set_header Host $host;
			proxy_cache_bypass $http_upgrade;
		}
	} ' >$NGINX_CFG

	systemctl enable nginx
	systemctl restart php8.0-fpm
	systemctl restart nginx
}

# should be able to run this multiple times.
# first time is install.
# second time onwards it is considered an update operation
# Should copy the source (ensure .env is not overwritten), npm install,
function install_or_update_nodejs {
	echo "Updating node.js application & restarting"
	chown -R $SERVICE_USER $INSTALL_ROOT/veriscope_ta_node

	pushd >/dev/null $INSTALL_ROOT/veriscope_ta_node
	su $SERVICE_USER -c "npm install"
	popd >/dev/null

	pushd >/dev/null $INSTALL_ROOT/
	# ONLY ONCE. Not needed for an update
	echo "Doing chain-specific configuration"
	cp -r chains/$VERISCOPE_TARGET/artifacts $INSTALL_ROOT/veriscope_ta_node/

	# no need to re-copy the service files during update operation
	if ! test -s "/etc/systemd/system/ta-node-1.service"; then
		echo "Activating and restarting node.js services: ta-node-1"
		cp scripts/ta-node-1.service /etc/systemd/system/
		sed -i "s/User=.*/User=$SERVICE_USER/g" /etc/systemd/system/ta-node-1.service
		systemctl daemon-reload
		systemctl enable ta-node-1
	fi

	systemctl restart ta-node-1

	# this also does a restart of ta-node-1
	regenerate_webhook_secret;
}

# be careful not to overwrite the .env file when updating
function install_or_update_laravel {
	echo "Deploying PHP application "

	pushd >/dev/null $INSTALL_ROOT/veriscope_ta_dashboard
	chown -R $SERVICE_USER .

	ENVDEST=.env
	sed -i "s#APP_URL=.*#APP_URL=https://$VERISCOPE_SERVICE_HOST#g" $ENVDEST
	sed -i "s#SHYFT_ONBOARDING_URL=.*#SHYFT_ONBOARDING_URL=https://$VERISCOPE_SERVICE_HOST#g" $ENVDEST
	regenerate_webhook_secret;

	echo "Setting up node.js elements of PHP application..."
	su $SERVICE_USER -c "npm install"
	su $SERVICE_USER -c "npm run development"

	echo "Setting up PHP and deploying..."
	su $SERVICE_USER -c "composer install"
	su $SERVICE_USER -c "php artisan migrate"

	# ONLY ONCE. SHOULD not run on update
	su $SERVICE_USER -c "php artisan db:seed"
	su $SERVICE_USER -c "php artisan key:generate"
	su $SERVICE_USER -c "php artisan passport:install"
	su $SERVICE_USER -c "php artisan encrypt:generate"
	su $SERVICE_USER -c "php artisan passportenv:link"
	# ONLY ONCE. SHOULD not run on update

	chgrp -R www-data ./
	chmod -R 0770 ./storage
	chmod -R g+s ./

	popd >/dev/null

	if ! test -s "/etc/systemd/system/ta.service"; then
		echo "Deploying systemd service definitions: ta ta-wss ta-schedule"
		cp scripts/ta-schedule.service /etc/systemd/system/
		cp scripts/ta-wss.service /etc/systemd/system/
		cp scripts/ta.service /etc/systemd/system/

		sed -i "s/User=.*/User=$SERVICE_USER/g" /etc/systemd/system/ta-schedule.service
		sed -i "s/User=.*/User=$SERVICE_USER/g" /etc/systemd/system/ta-wss.service
		sed -i "s/User=.*/User=$SERVICE_USER/g" /etc/systemd/system/ta.service
	fi


	systemctl daemon-reload

	echo "Restarting PHP-based services..."
	systemctl enable ta-schedule
	systemctl enable ta-wss
	systemctl enable ta
	systemctl restart ta-schedule
	systemctl restart ta-wss
	systemctl restart ta
}

function restart_all_services() {
	echo "Restarting all services..."
	systemctl restart nethermind
	systemctl restart ta
	systemctl restart ta-wss
	systemctl restart ta-schedule
	systemctl restart nginx
	systemctl restart postgresql
	systemctl restart redis.service
	systemctl restart ta-node-1
	systemctl restart horizon
	echo "All services restarted"
}

# idempotent func
function refresh_static_nodes() {
	echo "Refreshing static nodes from ethstats..."

	DEST=/opt/nm/static-nodes.json
	echo '[' >$DEST
	wscat -x '{"emit":["ready"]}' --connect $ETHSTATS_GET_ENODES | grep enode | jq '.emit[1].nodes' | grep  -oP '"enode://.*?"'   | sed '$!s/$/,/' | tee -a $DEST
	echo ']' >>$DEST
	cat $DEST

	echo
	echo "Cycling nethermind to obtain enode..."

	ENODE=`curl -s -X POST -d '{"jsonrpc":"2.0","id":1, "method":"admin_nodeInfo", "params":[]}' http://localhost:8545/ | jq '.result.enode'`
	echo "This enode: $ENODE . Updating ethstats setting..."
	jq ".EthStats.Contact = $ENODE" $NETHERMIND_CFG | sponge $NETHERMIND_CFG

	rm /opt/nm/nethermind_db/vasp/discoveryNodes/SimpleFileDb.db
	rm /opt/nm/nethermind_db/vasp/peers/SimpleFileDb.db
	systemctl restart nethermind
}

function daemon_status() {
	systemctl status nethermind ta ta-wss ta-schedule ta-node-1 nginx postgresql redis.service horizon | less
}

# ONLY ONCE. after lavarel install step
function create_admin() {
	pushd >/dev/null $INSTALL_ROOT/veriscope_ta_dashboard
	su $SERVICE_USER -c "php artisan createuser:admin"
	popd >/dev/null
}

function install_addressproof() {
	pushd >/dev/null $INSTALL_ROOT/veriscope_ta_dashboard
	su $SERVICE_USER -c "php artisan download:addressproof"
	popd >/dev/null
}

function install_passport_client_env(){
	pushd >/dev/null $INSTALL_ROOT/veriscope_ta_dashboard
	su $SERVICE_USER -c "php artisan passportenv:link"
	popd >/dev/null
}

function install_horizon() {
	pushd >/dev/null $INSTALL_ROOT/veriscope_ta_dashboard
	su $SERVICE_USER -c "composer update"
	# ONLY ONCE.
	su $SERVICE_USER -c "php artisan horizon:install"
	# ONLY ONCE.
	su $SERVICE_USER -c "php artisan migrate"
	popd >/dev/null

	pushd >/dev/null $INSTALL_ROOT/
	if ! test -s "/etc/systemd/system/horizon.service"; then
		echo "Deploying systemd service definitions: horizon"
		cp scripts/horizon.service /etc/systemd/system/
		sed -i "s/User=.*/User=$SERVICE_USER/g" /etc/systemd/system/horizon.service
	fi

	popd >/dev/null

	echo "Restarting horizon service..."
	systemctl daemon-reload
	systemctl enable horizon
	systemctl restart horizon
}

# on-demand only to reset if compromised. Safe to run multiple times
function regenerate_webhook_secret() {

	echo "Generating new shared secret..."
	SHARED_SECRET=$(pwgen -B 20 1)

	ENVDEST=$INSTALL_ROOT/veriscope_ta_dashboard/.env
	sed -i "s#WEBHOOK_CLIENT_SECRET=.*#WEBHOOK_CLIENT_SECRET=$SHARED_SECRET#g" $ENVDEST

	ENVDEST=$INSTALL_ROOT/veriscope_ta_node/.env
	sed -i "s#WEBHOOK_CLIENT_SECRET=.*#WEBHOOK_CLIENT_SECRET=$SHARED_SECRET#g" $ENVDEST

	systemctl restart ta-node-1 || true
	systemctl restart ta || true

	echo "Shared secret saved"
}

function regenerate_passport_secret() {
	echo "Generating new passport secret..."

	pushd >/dev/null $INSTALL_ROOT/veriscope_ta_dashboard
	su $SERVICE_USER -c "php artisan --force passport:install"
	popd >/dev/null

	echo "Passport secret saved"
}

function regenerate_encrypt_secret() {

	echo "Generating new encrypt secret..."
	pushd >/dev/null $INSTALL_ROOT/veriscope_ta_dashboard
	su $SERVICE_USER -c "php artisan encrypt:generate"
	popd >/dev/null

	echo "encrypt secret saved"
}


function menu() {
	echo
	echo
	echo -ne "1) Refresh dependencies
2) Install/update nethermind
3) Set up new postgres user
4) Obtain/renew SSL certificate
5) Install/update NGINX
6) Install/update node.js web service
7) Install/update PHP web service
8) Update static node list for nethermind
9) Create admin user
10) Regenerate webhook secret
11) Regenerate oauth secret (passport)
12) Regenerate encrypt secret (EloquentEncryption)
13) Install Redis server
14) Install Passport Client Environment Variables
15) Install Horizon
16) Install Address Proofs
17) Install Redis Bloom Filter module
i) Install Everything
p) show daemon status
w) restart all services
r) reboot
q) quit
Choose what to do: "
	read choice
	echo
	case $choice in
		1) refresh_dependencies ; menu ;;
		2) install_or_update_nethermind ; menu ;;
		3) create_postgres_trustanchor_db ; menu ;;
		4) setup_or_renew_ssl ; menu ;;
		5) setup_nginx ; menu ;;
		6) install_or_update_nodejs ; menu ;;
		7) install_or_update_laravel ; menu ;;
		8) refresh_static_nodes ; menu ;;
		9) create_admin; menu ;;
		10) regenerate_webhook_secret; menu ;;
		11) regenerate_passport_secret; menu ;;
		12) regenerate_encrypt_secret; menu ;;
		13) install_redis; menu ;;
		14) install_passport_client_env; menu ;;
		15) install_horizon; menu ;;
		16) install_addressproof; menu ;;
		17) install_redis_bloom; menu ;;
		"i") refresh_dependencies ; install_or_update_nethermind ; create_postgres_trustanchor_db  ; install_redis ; setup_or_renew_ssl ; setup_nginx ; install_or_update_nodejs ; install_or_update_laravel ; install_horizon ; install_redis_bloom ; refresh_static_nodes; menu ;;
		"p") daemon_status ; menu ;;
		"w") restart_all_services ; menu ;;
		"q") exit 0; ;;
		"r") reboot; ;;
	esac
}

if [ $# -gt 0 ]; then
	for func in $@; do
		$func;
		RC=$?
		if [ $RC -ne 0 ]; then
			echo "$func returned $RC. Exiting."
			exit $RC
		fi
	done
	echo "$@ - completed successfully"
	exit 0
fi

while [ 1 ]; do
	menu
done
