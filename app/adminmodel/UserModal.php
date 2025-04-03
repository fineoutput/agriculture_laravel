<?php

namespace App\adminmodel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserModal extends Model
{
    protected $table = 'users';
    public $timestamps = true;
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'ip', 'added_by', 'is_active'
    ];
    use SoftDeletes;
    protected $del = ['deleted_at'];
}
