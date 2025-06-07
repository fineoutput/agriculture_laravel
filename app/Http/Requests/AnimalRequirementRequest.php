<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnimalRequirementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */


     public function authorize(): bool
    {
        return true; // or add logic to authorize
    }

    public function rules(): array
    {
        return [
            'group' => 'required|string',
            'feeding_system' => 'required|string',
            'weight' => 'required|string',
            'milk_production' => 'required|string',
            'days_milk' => 'required|string',
            'milk_fat' => 'required|string',
            'milk_protein' => 'required|string',
            'milk_lactose' => 'required|string',
            'weight_variation' => 'required|string',
            'bcs' => 'required|string',
            'gestation_days' => 'required|string',
            'temp' => 'required|string',
            'humidity' => 'required|string',
            'thi' => 'required|string',
            'fat_4' => 'required|string',
        ];
    }

}
