<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		return [
			'supplier_id' => [
				'required',
			],
			'number' => [
				'required',
				'numeric'
			],
			'series' => [
				'required',
			],
			'date_of_issue' => [
				'required',
			],
            'items' => [
                'required',
                'array',
            ],
            'note.note_credit_type_id' => [
                'required_if:document_type_id, "07"',
            ],
            'note.note_debit_type_id' => [
                'required_if:document_type_id, "08"',
            ],
            'note.note_description' => [
                'required_if:document_type_id,"07", "08"',
            ],
		];
	}
}
