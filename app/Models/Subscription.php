<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $table = 'tbl_subscription';

    protected $fillable = [
        'id',
        'service_name',
        'doctor_calls',
        'animals',
        'monthly_price',
        'monthly_description',
        'monthly_service',
        'quarterly_price',
        'quarterly_description',
        'quarterly_service',
        'halfyearly_price',
        'halfyearly_description',
        'halfyearly_service',
        'yearly_price',
        'yearly_description',
        'yearly_service',
        'ip',
        'date',
        'is_active',
        'added_by',
    ];
    
    public $timestamps = false;
}