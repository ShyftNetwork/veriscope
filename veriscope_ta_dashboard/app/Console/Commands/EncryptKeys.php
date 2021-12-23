<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\{TrustAnchor,TrustAnchorUser,CryptoWalletAddress};
use RichardStyles\EloquentEncryption\EloquentEncryption;

class EncryptKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encrypt:keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypt Private Keys';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle() {

        $trust_anchors = TrustAnchor::all();

        foreach ($trust_anchors as $ta) {
            $private_key = $ta->private_key;
            $eloquent_encryption = new EloquentEncryption();
            $encrypted = $eloquent_encryption->encrypt($private_key);
            $ta->private_key = "";
            $ta->private_key_encrypt = bin2hex($encrypted);
            $ta->save();
            $this->info("Encrypted private key for Trust Anchor Account: ".$ta->account_address);
        }

        $trust_anchor_users = TrustAnchorUser::all();

        foreach ($trust_anchor_users as $tau) {
            $private_key = $tau->private_key;
            $eloquent_encryption = new EloquentEncryption();
            $encrypted = $eloquent_encryption->encrypt($private_key);
            $tau->private_key_encrypt = bin2hex($encrypted);
            $tau->private_key = "";
            $tau->save();
            $this->info("Encrypted private key for Trust Anchor User account: ".$tau->account_address);
        }

        $crypto_wallets = CryptoWalletAddress::all();

        foreach ($crypto_wallets as $cw) {
            $private_key = $cw->private_key;
            if($private_key) {
                $eloquent_encryption = new EloquentEncryption();
                $encrypted = $eloquent_encryption->encrypt($private_key);
                $cw->private_key = "";
                $cw->private_key_encrypt = bin2hex($encrypted);
                $cw->save();
                $this->info("Encrypted private key for Crypto account: ".$cw->address);
            }
        }
    }

}
