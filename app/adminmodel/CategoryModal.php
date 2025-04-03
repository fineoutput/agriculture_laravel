<?php

namespace App\adminmodel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryModal extends Model
{
    protected $table = 'category';
    public $timestamps = true;
    protected $primaryKey = 'id';

    protected $fillable = [
        'name','image', 'ip', 'added_by', 'is_active'
    ];
    use SoftDeletes;
    protected $del = ['deleted_at'];
}
