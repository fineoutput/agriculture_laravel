<?php

namespace App\adminmodel;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class AdminSidebar extends Model
{
    protected $table='admin_sidebar';
    public $timestamps=false;
	protected $primaryKey = 'id';

    protected $fillable = [
        'name','url','icon'
    ];
    // use SoftDeletes;
    // protected $del = ['deleted_at'];
}
