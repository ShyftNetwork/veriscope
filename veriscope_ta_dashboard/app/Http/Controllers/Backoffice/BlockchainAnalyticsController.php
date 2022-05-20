<?php

namespace App\Http\Controllers\Backoffice;

use Session;
use App\{BlockchainAnalyticsAddress, BlockchainAnalyticsProvider, BlockchainAnalyticsSupportedNetworks};
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\Paginator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;


class BlockchainAnalyticsController extends Controller
{

    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Http\Response
    */
    

    public function analytics_report(Request $request, $id)
    {
        Log::debug('BlockchainAnalyticsController analytics_report');
        Log::debug($id);

        $report = BlockchainAnalyticsAddress::findOrFail($id);
        return view('.blockchainanalyticsaddresses.report', ['report' => $report]);
    }

    public function new_report()
    {
        $providers = BlockchainAnalyticsProvider::all();
        $providersData = [];
        foreach ($providers as $value) {
            $supportedNetworks = BlockchainAnalyticsSupportedNetworks::where('blockchain_analytics_provider_id', $value->id)->get();
            foreach ($supportedNetworks as $network) {
                $providersData[$value->name][$network->ticker] = $network->name;
            }
        };
        Log::debug($providersData);
        return view('.blockchainanalyticsaddresses.newReport', ['providers' => $providersData]);
    }
    
    public function blockchainAnalyticsSettings() {
        $providers = BlockchainAnalyticsProvider::orderBy('id')->get();
        return view('.backoffice.blockchainAnalytics.index', ['providers' => $providers]);
    }

    public function update(Request $request) {
        $input = $request->all();

        $providers = BlockchainAnalyticsProvider::orderBy('id')->get();
        foreach($providers as $provider) {
            
            $provider->enabled = isset($input[$provider->id . '_enabled']) ? $input[$provider->id . '_enabled'] : '0';
            $provider->key = $input[$provider->id . '_key'];
            $provider->save();
        }

        Session::flash('flash_message', 'Successfully updated api keys!');
        Session::flash('flash_type', 'success');

        return redirect()->route('blockchain.analytics');
    }
    
}
