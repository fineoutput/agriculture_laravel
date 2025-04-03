<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class Settings extends Authenticatable
{
 

    protected $fillable = [
        'sitename','instagram_link','facebook_link','youtube_link','phone','address','power','logo','ip', 
    ];
    use SoftDeletes;
    protected $del = ['deleted_at'];
}
