<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class AttestationController extends Controller
{

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    Log::debug('AttestationController loaded');
  }

  /**
   *
   * @return \Illuminate\Http\Response
   */


  public function attestations()
  {
      return response()->view('.auth.attestations');
  }

  public function manage_organization()
  {
      return response()->view('.auth.attestations');
  }
  
  public function manage_users()
  {
      return response()->view('.auth.attestations');
  }

  public function vasp_manager()
  {
      return response()->view('.auth.attestations');
  }

  public function fatf_travel_rule_reports()
  {
      return response()->view('.auth.attestations');
  }

  public function trust_anchor_setup()
  {
      return response()->view('.auth.attestations');
  }

  public function new()
  {
      return response()->view('.auth.attestations');
  }
  
  public function admin()
  {
      return response()->view('.auth.attestations');
  }

  public function attestation_logs()
  {
      return response()->view('.auth.attestations');
  }

}
