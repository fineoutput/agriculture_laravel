<?php

namespace App\adminmodel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class Team extends Authenticatable
{
    protected $table='admin_teams';
    public $timestamps=true;
	protected $primaryKey = 'id';

    protected $fillable = [
        'name','email','password','phone','address','image','power','services','ip','added_by','is_active'
    ];
    use SoftDeletes;
    protected $del = ['deleted_at'];
}
