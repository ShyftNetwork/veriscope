<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Constant extends Model
{
    protected $fillable = ['name', 'description', 'type', 'value'];

}
