<?php

namespace App\adminmodel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class Crm extends Authenticatable
{
    protected $table='admin_teams';
    public $timestamps=true;
	protected $primaryKey = 'id';

    protected $fillable = [
        'sitename','instagram_link','facebook_link','youtube_link','phone','address','logo','ip',
    ];
    use SoftDeletes;
    protected $del = ['deleted_at'];
}
