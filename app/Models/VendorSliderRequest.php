<?php
// app/Models/VendorSliderRequest.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorSliderRequest extends Model
{
    protected $table = 'tbl_sliders_vender';
    protected $fillable = [
        'image', 'ip', 'added_by', 'is_active', 'date' // Adjust fields as per your table structure
    ];

    protected $casts = [
        'is_active' => 'integer' // Using integer since it has values like 1, 2
    ];
}