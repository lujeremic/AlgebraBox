<?php

namespace App\Http\Controllers\User;

use Cartalyst\Sentinel\Sentinel;
use Illuminate\Support\Facades\Storage;
use App\Models\UserRoot as UserDirectory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller {

	protected $sentinel = null;

	/**
	 * Set middleware to quard controller.
	 *
	 * @return void
	 */
	public function __construct(Sentinel $sentinel) {
		$this->middleware('sentinel.auth');
		$this->sentinel = $sentinel;
		//$userInterface = new UserInterface();
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		$urlSegments = $request->segments();
		$currentRequestDirectory = array_pop($urlSegments);
		$viewData = array();
		$dirName = UserDirectory::getUserDirName($this->sentinel->getUser()->getUserId());
		$userDiskData = new \stdClass();
		if ($currentRequestDirectory !== 'home') {
			if (count($urlSegments) > 1) {
				unset($urlSegments[0]);
				$dirName = $dirName . '/' . implode('/', $urlSegments) . '/' . $currentRequestDirectory;
			} else {
				$dirName = $dirName . '/' . $currentRequestDirectory;
			}
		}
		$directories = Storage::disk('public')->directories($dirName);
		$numOfDirectories = count($directories);
		$userDiskData->number_of_directories = $numOfDirectories;
		$userDiskData->directories = array();
		// set directories 
		if ($numOfDirectories) {
			for ($i = 0; $i < $numOfDirectories; $i++) {
				$directory = $directories[$i];
				$dir = str_replace($dirName . '/', '', $directory);
				$userDiskData->directories[$i] = array('path' => url($request->getPathInfo(), array($dir)), 'name' => $dir);
			}
		}
		// set home directory files 
		$files = Storage::disk('public')->files($dirName);
		$numOfFiles = count($files);
		$userDiskData->number_of_files = $numOfFiles;
		$userDiskData->files = array();
		for ($i = 0; $i < $numOfFiles; $i++) {
			$file = $files[$i];
			$fileName = str_replace($dirName . '/', '', $file);
			$userDiskData->files[$i] = array('path' => $file, 'name' => $fileName);
		}
		// push user disk data 
		$viewData['user_disk'] = $userDiskData;
		//die('<pre>' . print_r($viewData, 1) . '</pre>');
		return view('user.home', $viewData);
	}

}
