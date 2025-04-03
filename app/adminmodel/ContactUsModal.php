<?php

namespace App\adminmodel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactUsModal extends Model
{
    protected $table='contact_us';
    public $timestamps=true;
	protected $primaryKey = 'id';

    protected $fillable = [
        'name','email','phone','message','ip','added_by',
    ];
    use SoftDeletes;
    protected $del = ['deleted_at'];
}
