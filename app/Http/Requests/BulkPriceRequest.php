<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-products');
    }

    public function rules(): array
    {
        return [
            'percent'       => ['required','numeric','min:0'],
            'direction'     => ['required','in:up,down'],
            'column'        => ['required','in:price,sale_price'],
            'round_to'      => ['nullable','integer','min:0','max:4'],
            'apply_to_all'  => ['required','boolean'],
            'product_ids'   => ['array','nullable'],
            'product_ids.*' => ['integer','exists:products,id'],
        ];
    }
}
