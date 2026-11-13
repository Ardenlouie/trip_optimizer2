<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class TripOptimizerStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('trip optimizer create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'company_id' => [
            //     'required'
            // ],
            // 'control_number' => [
            //     'required'
            // ],
            // 'sales_orders' => [
            //     'required'
            // ],
            // 'truck_ids' => [
            //     'required'
            // ],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        session()->flash('message_error', 'Error saving.');

        parent::failedValidation($validator);
    }
}
