<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTxn extends Model
{
    use HasFactory;

    protected $table = 'tbl_inventory_txn';
    protected $fillable = ['id', 'order_id', 'at_time', 'less_inventory', 'updated_inventory', 'date'];
}
