<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class MakeDirectoryRequest extends FormRequest {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		return [
			'directory_name' => 'required|max:255|regex:/[\w-\\p{L}\s]+$/u' // allow only letters numbers _ and utf8 letters and space
		];
	}

}
