<?php

namespace App;

use HttpOz\Roles\Traits\HasRole;
use HttpOz\Roles\Contracts\HasRole as HasRoleContract;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\Statable;
use Laravel\Passport\HasApiTokens;
use App\Traits\Verifyable;
use App\Traits\Searchable;


class User extends Authenticatable implements HasRoleContract
{
    use Notifiable, HasRole, Statable, HasApiTokens, Verifyable, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'email',
        'password',
        'remember_token',
        'last_name',
        'dob',
        'gender',
        'telephone',
        'occupation',
        'address',
        'suite',
        'country',
        'state',
        'city',
        'zip',
        'reputation',
        'status',
        'marketing_subscribe',
        'legal_agree',
        'lockup_agree',
    ];

    /**
     * The attributes that are mass searchable.
     *
     * @var array
     */
    protected $searchable = ['first_name', 'last_name', 'middle_name', 'email'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    const HISTORY_MODEL = [
        'name' => 'App\UserState' // the related model to store the history
    ];
    const SM_CONFIG = 'shyftMember'; // the SM graph to use

    public function trustAnchor() {
        return $this->hasMany('App\TrustAnchor')->orderBy('created_at', 'DESC');
    }

    /**
     * @return password security model
     */
    public function passwordSecurity()
    {
        return $this->hasOne('App\PasswordSecurity');
    }
    
    /**
     * Reputation percentage
     * used on the dashboard and maybe other places?
     */

     public function getReputationPercentageAttribute() {
       $reputation_max = 100;

       return $this->reputation > $reputation_max ? 100 : (($this->reputation / $reputation_max)) * 100;
     }

     /**
      * @return string
      */
     public function getFullNameAttribute() {
         return $this->first_name . ' ' . $this->last_name;
     }

     /**
      * @return string
      */
     public function getTypeAttribute() {
         if($this->last_state == 'approved') {
           return 'Member';
         } elseif($this->last_state == 'new') {
           return 'Invitee';
         }
         return 'Applicant';
     }

     /**
     * @return boolean
     */
    public function getPurchaseAgreedAttribute() {
        return $this->legal_agree && $this->lockup_agree;
    }

}
