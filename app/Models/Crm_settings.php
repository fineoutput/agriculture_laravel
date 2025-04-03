<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Crm_settings extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'sitename','instagram_link','facebook_link','youtube_link','phone','address','power','logo','ip','added' 
    ];
}
