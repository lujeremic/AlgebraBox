<?php

namespace App\Http\Controllers\User;

use Cartalyst\Sentinel\Sentinel;
use Illuminate\Support\Facades\Storage;
use App\Models\UserRoot as UserDirectory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\File;

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
		$storageDisk = Storage::disk('public');
		dump($storageDisk);
		$viewData = array();
		$urlSegments = $request->segments();
		$pathSegments = $urlSegments;
		// check num of segments, home will be always 0 index 
		$numOfSegments = count($urlSegments);
		$requestedDirectoryName = $pathSegments[$numOfSegments - 1];
		$userRootDirName = UserDirectory::getUserDirectoryName($this->sentinel->getUser()->getUserId());
		// replace home with users root directory name
		$pathSegments[0] = str_replace('home', $userRootDirName, $pathSegments[0]);
		if ($numOfSegments === 1) {
			$requestedDirectoryPath = '/' . $pathSegments[0] . '/';
		} else
			$requestedDirectoryPath = implode('/', $pathSegments) . '/';
		// check requested directory path exists or not
		if (!$storageDisk->exists($requestedDirectoryPath)) {
			// set message!
			$request->session()->flash('dangerMsg', 'Requested directory "' . $requestedDirectoryName . '" doesn\'t exist!');
			return redirect('home');
		}
		$viewData['user_disk'] = new \stdClass();
		$directories = $storageDisk->directories($requestedDirectoryPath);
		$numOfDirectories = count($directories);
		$viewData['user_disk']->number_of_directories = $numOfDirectories;
		$viewData['user_disk']->directories = array();
		// set directories structure 
		if ($numOfDirectories) {
			for ($i = 0; $i < $numOfDirectories; $i++) {
				$directoryPath = $directories[$i];
				$dirPathSlugs = explode('/', $directoryPath);
				$dirName = array_pop($dirPathSlugs);
				$viewData['user_disk']->directories[$i] = array('path' => $request->getRelativeUriForPath($request->getRequestUri() . '/' . $dirName), 'name' => $dirName);
			}
		}
		// set files data 
		$files = $storageDisk->files($requestedDirectoryPath);
		$numOfFiles = count($files);
		$viewData['user_disk']->number_of_files = $numOfFiles;
		$viewData['user_disk']->files = array();
		for ($i = 0; $i < $numOfFiles; $i++) {
			$filePath = $files[$i];
			$filePathSlugs = explode('/', $filePath);
			$file = array_pop($filePathSlugs);
			$fileName = pathinfo($file, PATHINFO_FILENAME);
			$viewData['user_disk']->files[$i] = array('path' => $request->getRelativeUriForPath($request->getRequestUri() . '/' . $fileName), 'name' => $fileName);
		}

		return view('user.home', $viewData);
	}

	public function filePreview(Request $request) {
		dump($request);
		return view('user.home_file_preview');
	}

}
