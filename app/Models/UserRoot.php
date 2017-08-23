<?php

namespace App\Models;

use \Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class UserRoot extends Model {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'user_root';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'user_id'];

	public static function getUserDirectoryName($userID) {
		// retrive from session
		$storedSessionUserdirName = request()->session()->get('user.root_directory_name');
		// root directory name exists return name
		if (!is_null($storedSessionUserdirName)) {
			return $storedSessionUserdirName;
		}

		$result = self::where('user_id', '=', $userID)->get(array('name'))->first();
		// something is wrong!!!
		if (is_null($result))
			abort(402, 'Your directory is missing, please contact web administrator so we can fix your problem!');

		$publicDisk = Storage::disk('public');
		$directories = $publicDisk->allDirectories();
		$dirName = $result->name;
		// create user dir if it doesn't exist
		if (!in_array($dirName, $directories)) {
			$publicDisk->makeDirectory($dirName);
		}
		request()->session()->put('user.root_directory_name', $dirName);
		return $dirName;
	}

	public function saveDir($dir_name, $user_id) {

		$this->fill(['name' => $dir_name, 'user_id' => $user_id]);
		$this->save();
	}

}
