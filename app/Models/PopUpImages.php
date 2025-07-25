<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PopUpImages extends Model
{
    use HasFactory;

    protected $table = 'popup_image';

    protected $fillable = [ 'id',
    'image',
    'created_at',
    'updated_at',
    'deleted_at']
    ;
}
