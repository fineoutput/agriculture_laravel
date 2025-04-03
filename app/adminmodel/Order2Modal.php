<?php

namespace App\adminmodel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order2Modal extends Model
{
    protected $table = 'order2';
    public $timestamps = true;
    protected $primaryKey = 'id';

    protected $fillable = [
        'main_id', 'product_id', 'quantity', 'price','total_price','gst_percentage'
    ];
    use SoftDeletes;
    protected $del = ['deleted_at'];
    public function product()
    {
        return $this->belongsTo(ProductModal::class, 'product_id')->withTrashed();
    }
}
