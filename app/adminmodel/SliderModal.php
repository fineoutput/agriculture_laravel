<?php

namespace App\adminmodel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SliderModal extends Model
{
    protected $table = 'sliders';
    public $timestamps = true;
    protected $primaryKey = 'id';

    protected $fillable = [
        'image', 'ip', 'added_by', 'is_active'
    ];
    use SoftDeletes;
    protected $del = ['deleted_at'];
}
