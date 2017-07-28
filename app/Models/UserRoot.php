<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRoot extends Model
{
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
    protected $fillable = ['name','user_id'];
	
	
	public function saveDir($dir_name, $user_id) {
		
		$this->fill(['name' => $dir_name, 'user_id' => $user_id]);
		$this->save();
		
	}
}
