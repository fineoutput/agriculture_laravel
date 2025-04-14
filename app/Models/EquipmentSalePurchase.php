<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentSalePurchase extends Model
{
    protected $table = 'tbl_equipment_sale_purchese';
    protected $fillable = ['id','farmer_id', 'information_type', 'equipment_type', 'company_name', 'year_old', 'price', 'image1', 'image2', 'image3', 'image4', 'video', 'remark',   'status'];
}