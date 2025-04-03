<?php

namespace App\adminmodel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order1Modal extends Model
{
    protected $table = 'order1';
    public $timestamps = true;
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', 'final_amount', 'payment_status', 'order_status', 'payment_type', 'address', 'ip'
    ];
    use SoftDeletes;
    protected $del = ['deleted_at'];
     public function user()
    {
        return $this->belongsTo(UserModal::class, 'user_id')->withTrashed();
    }
}
