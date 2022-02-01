#!/bin/bash

# Check script is run with sudo
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run with sudo or as root"
   exit 1
fi

if [ `pwd` = "/opt/veriscope" ]; then
	echo "+ Located in /opt/veriscope/"
else
	echo "Please move the veriscope checkout to /opt"
	exit 1
fi

set -o allexport; source .env; set +o allexport
if [ $VERISCOPE_SERVICE_HOST = 'unset' ]; then
	echo "Please set VERISCOPE_SERVICE_HOST in .env"
	exit 1
fi
if [ $VERISCOPE_COMMON_NAME = 'unset' ]; then
	echo "Please set VERISCOPE_COMMON_NAME in .env"
	exit 1
fi

SERVICE_USER=serviceuser
CERTFILE=/etc/letsencrypt/live/$VERISCOPE_SERVICE_HOST/fullchain.pem
CERTKEY=/etc/letsencrypt/live/$VERISCOPE_SERVICE_HOST/privkey.pem
SHARED_SECRET=


NETHERMIND_DEST=/opt/nm
NETHERMIND_CFG=$NETHERMIND_DEST/config.cfg
NETHERMIND_TARBALL="https://github.com/NethermindEth/nethermind/releases/download/1.11.1/nethermind-linux-amd64-1.11.1-919e4cc-20210831.zip"
NETHERMIND_RPC="http://localhost:8545"

NGINX_CFG=/etc/nginx/sites-enabled/ta-dashboard.conf

echo "+ Service user will be $SERVICE_USER"


# this function sets some globals to have a new ethereum PK, ACCT
function create_sealer_pk {
	pushd >/dev/null /opt/veriscope/veriscope_ta_node
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

  	apt-get -qq -y -o Acquire::https::AllowRedirect=false install redis-server
  	#Configure Redis
  	cp /etc/redis/redis.conf /etc/redis/redis.conf.bak
  	sed 's/^supervised.*/supervised systemd/' /etc/redis/redis.conf >> /etc/redis/redis.conf.new
  	cp /etc/redis/redis.conf.new /etc/redis/redis.conf

  	systemctl restart redis.service

}

function create_postgres_trustanchor_db {
	if su postgres -c "psql -t -c '\du'" | cut -d \| -f 1 | grep -qw trustanchor; then
	    echo "Postgres user trustanchor already exists."
	else
		PGPASS=$(pwgen -B 10 1)
		PGDATABASE=trustanchor
		PGUSER=trustanchor

		sudo -u postgres psql -c "create user $PGUSER  with createdb login password '$PGPASS'" || { echo "Postgres user creation failed"; exit 1; }
		sudo -u postgres psql -c "create database $PGDATABASE owner $PGUSER" || { echo "Postgres database creation failed"; exit 1; }

		ENVDEST=/opt/veriscope/veriscope_ta_dashboard/.env
		sed -i "s#DB_CONNECTION=.*#DB_CONNECTION=pgsql#g" $ENVDEST
		sed -i "s#DB_HOST=.*#DB_HOST=localhost#g" $ENVDEST
		sed -i "s#DB_PORT=.*#DB_PORT=5432#g" $ENVDEST
		sed -i "s#DB_DATABASE=.*#DB_DATABASE=$PGUSER#g" $ENVDEST
		sed -i "s#DB_USERNAME=.*#DB_USERNAME=$PGUSER#g" $ENVDEST
		sed -i "s#DB_PASSWORD=.*#DB_PASSWORD=$PGPASS#g" $ENVDEST

		echo "  New postgres user $PGUSER / password $PGPASS / database $PGDATABASE "
	fi
}

function refresh_dependencies() {
  	apt-get -y  update
  	apt-get install -y software-properties-common curl sudo wget build-essential systemd
	add-apt-repository >/dev/null -yn ppa:ondrej/php
	add-apt-repository >/dev/null -yn ppa:ondrej/nginx
	# nodesource's script does an apt update
	curl -fsSL https://deb.nodesource.com/setup_14.x | sudo -E bash -

	apt-get -y upgrade

  	apt-get -qq -y -o Acquire::https::AllowRedirect=false install  vim git libsnappy-dev libc6-dev libc6 unzip make jq ntpdate moreutils php8.0-fpm php8.0-dom php8.0-zip php8.0-mbstring php8.0-curl php8.0-dom php8.0-gd php8.0-imagick php8.0-pgsql php8.0-mbstring nodejs build-essential postgresql nginx pwgen certbot
	pg_ctlcluster 12 main start
	if ! command -v wscat; then
		npm install -g wscat
	fi

	SHARED_SECRET=$(pwgen -B 20 1)

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


  chown -R $SERVICE_USER /opt/veriscope/

	pushd >/dev/null /opt/veriscope/veriscope_ta_dashboard
	echo 'npm install'
	su $SERVICE_USER -c "npm install"
	echo 'composer install'
	su $SERVICE_USER -c "composer install"
	popd >/dev/null

	pushd >/dev/null /opt/veriscope/veriscope_ta_node
	echo 'npm install'
	su $SERVICE_USER -c "npm install"
	popd >/dev/null

	cp scripts/ntpdate /etc/cron.daily/
	cp scripts/journald /etc/cron.daily/
	chmod +x /etc/cron.daily/journald
	chmod +x /etc/cron.daily/ntpdate

	/etc/cron.daily/ntpdate
}


function install_or_update_nethermind() {
	echo "Installing Nethermind to $NETHERMIND_DEST - configuration will be in $NETHERMIND_CFG"


	wget -q -O /tmp/nethermind-dist.zip "$NETHERMIND_TARBALL"
	unzip -qq -d $NETHERMIND_DEST /tmp/nethermind-dist.zip
	rm -rf $NETHERMIND_DEST/chainspec
	rm -rf $NETHERMIND_DEST/configs

	if ! test -s "/opt/nm/static-nodes.json"; then
		echo "Installing default /opt/nm/static-nodes.json"
		cp vasp_testnet/node_1/static-nodes.json $NETHERMIND_DEST
	fi
	if ! test -s "/opt/nm/VaspTestnet.json"; then
		echo "Installing /opt/nm/VaspTestnet.json genesis file"
		cp vasp_testnet/genesis/VaspTestnet.json $NETHERMIND_DEST
	fi
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
			    "ChainSpecPath": "VaspTestnet.json",
			    "BaseDbPath": "nethermind_db/vasp",
			    "LogFileName": "/var/log/nethermind.log",
			    "StaticNodesPath": "static-nodes.json"
			  },
			  "Network": {
			    "DiscoveryPort": 30303,
			    "P2PPort": 30303
			  },
			  "JsonRpc": {
			    "Enabled": true,
			    "Host": "0.0.0.0",
			    "Port": 8545,
			    "EnabledModules": ["Admin","Net", "Eth", "Trace","Parity", "Web3", "Debug","Subscribe"]
			  },
			  "KeyStoreConfig": {
			    "TestNodeKey": "'$SEALERPK'"
			  },
			  "Aura": {
			    "ForceSealing": true,
			    "AllowAuRaPrivateChains": true
			  },
			  "EthStats": {
				"Enabled": true,
				"Contact": "not-yet",
				"Secret": "Oogongi4",
				"Name": "'$VERISCOPE_SERVICE_HOST'",
				"Server": "ws://fedstats.veriscope.network/api"
			  }
			}' >  $NETHERMIND_CFG
	fi

	echo "Restarting nethermind..."
	chown -R $SERVICE_USER /opt/nm/
	systemctl restart nethermind
}

function setup_or_renew_ssl {
	systemctl stop nginx
	certbot certonly --agree-tos   --register-unsafely-without-email --standalone --preferred-challenges http   -d $VERISCOPE_SERVICE_HOST || { echo "Certbot failed to get a certificate"; exit 1; }
	if [ -f $CERTFILE ]; then
		echo "Found $CERTFILE";
	else
		echo "Couldn't find certificate file $CERTFILE"
	fi
	systemctl restart nginx
}

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
	    root /opt/veriscope/veriscope_ta_dashboard/public;

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
	}

	' >$NGINX_CFG
	systemctl enable nginx
  	systemctl restart php8.0-fpm
	systemctl restart nginx
}

function install_or_update_nodejs {
	echo "Updating node.js application & restarting"
	chown -R $SERVICE_USER /opt/veriscope/veriscope_ta_node

	if ! test -s "/etc/systemd/system/ta-node-1.service"; then
		echo "Deploying and restarting node.js services: ta-node-1 ta-node-2"
		cp scripts/ta-node-1.service /etc/systemd/system/
		cp scripts/ta-node-2.service /etc/systemd/system/
		sed -i "s/User=.*/User=$SERVICE_USER/g" /etc/systemd/system/ta-node-1.service
		sed -i "s/User=.*/User=$SERVICE_USER/g" /etc/systemd/system/ta-node-2.service
		systemctl daemon-reload
	fi

	systemctl restart ta-node-1
	systemctl restart ta-node-2
}

function install_or_update_laravel {
	echo "Deploying PHP application "

	pushd >/dev/null /opt/veriscope/veriscope_ta_dashboard

  	touch ./storage/logs/laravel.log

  	chown -R $SERVICE_USER .
	chgrp -R www-data ./storage
  	chmod -R 0770 ./storage

	ENVDEST=.env
	sed -i "s#APP_URL=.*#APP_URL=https://$VERISCOPE_SERVICE_HOST#g" $ENVDEST
	sed -i "s#SHYFT_ONBOARDING_URL=.*#SHYFT_ONBOARDING_URL=https://$VERISCOPE_SERVICE_HOST#g" $ENVDEST
	sed -i "s#WEBHOOK_CLIENT_SECRET=.*#WEBHOOK_CLIENT_SECRET=$SHARED_SECRET#g" $ENVDEST

	echo "Setting up node.js and deploying...."
	su $SERVICE_USER -c "npm install"
	su $SERVICE_USER -c "npm run development"

	echo "Setting up PHP and deploying..."
	su $SERVICE_USER -c "composer install"
	su $SERVICE_USER -c "php artisan migrate"
	su $SERVICE_USER -c "php artisan db:seed"
	if grep -q '^APP_KEY=$' $ENVDEST; then
		su $SERVICE_USER -c "php artisan key:generate"
	else
		echo "App key already set"
	fi
  	su $SERVICE_USER -c "php artisan passport:install"
  	su $SERVICE_USER -c "php artisan encrypt:generate"
  	install_passport_client_env;

	popd >/dev/null

	if ! test -s "/etc/systemd/system/ta.service"; then
		echo "Deploying systemd service definitions: ta ta-wss ta-schedule"
    cp scripts/ta-schedule.service /etc/systemd/system/
		cp scripts/ta-wss.service /etc/systemd/system/
		cp scripts/ta.service /etc/systemd/system/

    sed -i "s/User=.*/User=$SERVICE_USER/g" /etc/systemd/system/ta-schedule.service
		sed -i "s/User=.*/User=$SERVICE_USER/g" /etc/systemd/system/ta-wss.service
		sed -i "s/User=.*/User=$SERVICE_USER/g" /etc/systemd/system/ta.service
		systemctl daemon-reload
	fi
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
  	systemctl restart ta
  	systemctl restart ta-wss
  	systemctl restart ta-schedule
  	systemctl restart nginx
  	systemctl restart postgresql
  	systemctl restart redis.service
  	systemctl restart ta-node-1
  	systemctl restart ta-node-2
	echo "All services restarted"
}


function refresh_static_nodes() {
	echo "Refreshing static nodes from ethstats..."

	DEST=/opt/nm/static-nodes.json
	echo '[' >$DEST
	wscat -x '{"emit":["ready"]}' --connect ws://fedstats.veriscope.network/primus/?_primuscb=1627594389337-0 | grep enode | jq '.emit[1].nodes' | grep  -oP '"enode://.*?"'   | sed '$!s/$/,/' | tee -a $DEST
	echo ']' >>$DEST

	echo
	echo "Cycling nethermind to obtain enode..."
	systemctl restart nethermind
	sleep 20
	MATCH=`journalctl -u nethermind -n 200 | grep This.node | tail -1`
	[[ $MATCH =~ (enode://.+) ]]
	ENODE=${BASH_REMATCH[1]}
	echo "This enode: $ENODE . Updating ethstats setting..."
	jq ".EthStats.Contact = \"$ENODE\"" $NETHERMIND_CFG | sponge $NETHERMIND_CFG
	systemctl restart nethermind
}

function daemon_status() {
	systemctl status nethermind ta ta-wss ta-node-1 ta-node-2 nginx postgresql redis.service | less
}

function create_admin() {

  	pushd >/dev/null /opt/veriscope/veriscope_ta_dashboard
  	su $SERVICE_USER -c "php artisan createuser:admin"
}


function install_passport_client_env(){
  	pushd >/dev/null /opt/veriscope/veriscope_ta_dashboard
  	su $SERVICE_USER -c "php artisan passportenv:link"
}

function regenerate_webhook_secret() {

  echo "Generating new shared secret..."

  SHARED_SECRET=$(pwgen -B 20 1)

  ENVDEST=/opt/veriscope/veriscope_ta_dashboard/.env
  sed -i "s#WEBHOOK_CLIENT_SECRET=.*#WEBHOOK_CLIENT_SECRET=$SHARED_SECRET#g" $ENVDEST

  ENVDEST=/opt/veriscope/veriscope_ta_node/.env
  sed -i "s#WEBHOOK_CLIENT_SECRET=.*#WEBHOOK_CLIENT_SECRET=$SHARED_SECRET#g" $ENVDEST

  echo "Shared secret saved"

}

function regenerate_passport_secret() {

  echo "Generating new passport secret..."

  pushd >/dev/null /opt/veriscope/veriscope_ta_dashboard
  su $SERVICE_USER -c "php artisan --force passport:install"
  install_passport_client_env;

  echo "Passport secret saved"
}

function regenerate_encrypt_secret() {

  echo "Generating new encrypt secret..."
  pushd >/dev/null /opt/veriscope/veriscope_ta_dashboard
  su $SERVICE_USER -c "php artisan encrypt:generate"

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
i) install everything
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
    	"i") refresh_dependencies ; install_or_update_nethermind ; create_postgres_trustanchor_db ; install_redis ; setup_or_renew_ssl ; setup_nginx ; install_or_update_nodejs ; install_or_update_laravel  ; refresh_static_nodes; menu ;;
    	"p") daemon_status ; menu ;;
		"w") restart_all_services ; menu ;;
		"q") exit 0; ;;
		"r") reboot; ;;
	esac
}

while [ 1 ]; do
menu
done
