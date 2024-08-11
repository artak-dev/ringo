<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportDataRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'data' => 'required|array|min:9',
            'data.*.manufactured_quantity' => 'required|integer|min:0',
            'data.*.sold_quantity' => 'required|integer|min:0',
        ];
    }
}
