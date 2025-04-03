<?php

namespace App\adminmodel;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class AdminSidebar2 extends Model
{
    protected $table='admin_sidebar2';
    public $timestamps=false;
	protected $primaryKey = 'id';

    protected $fillable = [
        'main_id','name','url'
    ];
    // use SoftDeletes;
    // protected $del = ['deleted_at'];
}
