<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;
use App\Http\Controllers\KycTemplateController;
use App\{KycTemplateState, KycTemplate};

class KycTemplateResponse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kyc:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if there is a kyc template response to send.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::debug('KycTemplateResponse handle');

        $kts = KycTemplateState::where('state', 'BENEFICIARY_KYC')->firstOrFail();

        $kt = KycTemplate::where('kyc_template_state_id', $kts->id)->first();

        if($kt && !is_null($kt->sender_kyc) && !is_null($kt->beneficiary_kyc)) {
            $kyc = new KycTemplateController();
            $kyc->kyc_template_response($kt->attestation_hash);
            $kts = KycTemplateState::where('state', 'DONE')->firstOrFail();
            $kt->kyc_template_state_id = $kts->id;
            $kt->save();
        }

        $kts = KycTemplateState::where('state', 'SENDER_KYC')->firstOrFail();

        $kt = KycTemplate::where('kyc_template_state_id', $kts->id)->firstOrFail();

        if($kt && is_null($kt->beneficiary_kyc)) {

            $kyc = new KycTemplateController();
            $kyc->kyc_template_response($kt->attestation_hash);
        }
        else if($kt && !is_null($kt->beneficiary_kyc) && !is_null($kt->sender_kyc)) {
            $kts = KycTemplateState::where('state', 'DONE')->firstOrFail();
            $kt->kyc_template_state_id = $kts->id;
            $kt->save();
        }
    }
}
