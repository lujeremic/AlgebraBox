<?php

namespace App\Models;

use \Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

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

	public static function getUserDirName($userID) {
		$result = self::where('user_id', '=', $userID)->get(array('name'))->first();
		$publicDisk = Storage::disk('public');
		if (is_null($result)) 
			return $result;
		
		$directories = $publicDisk->allDirectories();
		$dirName = $result->name;
		// create user dir if it doesn't exist
		if (!in_array($dirName, $directories)) {
			$publicDisk->makeDirectory($dirName);
		}
		return $dirName;
	}

	public function saveDir($dir_name, $user_id) {

		$this->fill(['name' => $dir_name, 'user_id' => $user_id]);
		$this->save();
	}

}
