<?php

namespace App\adminmodel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductModal extends Model
{
    protected $table = 'products';
    public $timestamps = true;
    protected $primaryKey = 'id';

    protected $fillable = [
        'category_id','name','sku','description','mrp','price','gst_percentage','selling_price','gst','image','image2','image3','image4','is_top', 'ip', 'added_by', 'is_active'
    ];
    
    use SoftDeletes;
    protected $del = ['deleted_at'];
}
