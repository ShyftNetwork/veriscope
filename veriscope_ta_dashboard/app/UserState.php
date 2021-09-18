<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Searchable;

class UserState extends Model
{
    use Searchable;

    protected $fillable = ['transition', 'from', 'user_id', 'order_id', 'to', 'payload', 'response', 'by_user', 'pass', 'reason'];

    /**
     * The attributes that are mass searchable.
     *
     * @var array
     */
    protected $searchable = ['transition', 'to', 'payload'];

    public function user() {
        return $this->belongsTo('App\User');
    }

    /**
     * return the Camel Case to
     * @return \\
     */
    public function getNiceFromAttribute()
    {
        return str_snake_title($this->from);
    }

    /**
     * return the Camel Case to
     * @return \\
     */
    public function getNiceToAttribute()
    {
        return str_snake_title($this->to);
    }

    /**
     * return the Camel Case transition
     * @return \\
     */
    public function getNiceTransitionAttribute()
    {
        return str_snake_title($this->transition);
    }

}
