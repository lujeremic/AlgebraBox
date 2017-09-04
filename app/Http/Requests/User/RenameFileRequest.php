<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class RenameFileRequest extends FormRequest {

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
			'old_file_name' => 'required|max:255',
			'renamed_file_name' => 'required|max:255|regex:/^[\w\\p{L}\s]+$/u',
			'renamed_file_path' => 'required|max:255'
		];
	}

	public function messages() {
		return [
			'renamed_file_name.regex' => 'Invalid file name: "' . $this->get('renamed_file_name') . '", allowed characters: a-ž, A-ž, space, _ and 0-9',
		];
	}

}
