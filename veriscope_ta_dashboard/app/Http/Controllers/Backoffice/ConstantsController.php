<?php

namespace App\Http\Controllers\Backoffice;

use App\User;
use HttpOz\Roles\Models\Role;
use Session;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Str;
use App\Constant;
use App\Jobs\MaintenanceMode;
use Log;
use App\Jobs\SendgridEnable;


class ConstantsController extends Controller
{

    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        // TODO: an array or object of constant settings
        // $constants = (Object)[
        //   'maintenance' => false,
        //   'lang' => 'en',
        //   'sendgrid' => true,
        //   'slack' => false,
        //   'compliance' => true,
        //   'wallet' => true,
        //   'auto_invite' => true,
        // ];

        $constants = Constant::all();

        return view('.backoffice.constants.index', compact('constants'));
    }

    /**
    * Update the Constant Config Settings/Actions
    *
    * @param \Illuminate\Http\Request
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request)
    {
        $input = $request->all();
        $constants = Constant::all();
        foreach($constants as $constant) {
            if($constant->type == 'boolean'){
                if(isset($input[$constant->name])) {
                    if($constant->value == false) {
                        $constant->value = (boolean) $input[$constant->name];

                        activity()
                         ->log('Updating '.$constant->name .' to: '.$constant->value);

                        if($constant->name == 'maintenance') {
                            // updated
                            MaintenanceMode::dispatch($constant);
                        } elseif($constant->name == 'sendgrid'){
                            SendgridEnable::dispatch();
                        }
                    }

                } else {
                    if($constant->value == true) {
                        $constant->value = false;

                        activity()
                         ->log('Updating '.$constant->name .' to: '.$constant->value);

                        Log::info('Updating '.$constant->name .' to: '.$constant->value);
                        if($constant->name == 'maintenance') {
                            // updated
                            MaintenanceMode::dispatch($constant);
                        }
                    }
                }
            } elseif($constant->type == 'text') {
                if(isset($input[$constant->name])) {
                    $constant->value = $input[$constant->name];
                }
            } elseif($constant->type == 'select') {
                if(isset($input[$constant->name])) {
                    $constant->value = $input[$constant->name];
                }
            }
            $constant->save();
        }

        Session::flash('flash_message', 'Successfully updated Constants!');
        Session::flash('flash_type', 'success');

        return redirect()->route('constants.index');
    }

}
