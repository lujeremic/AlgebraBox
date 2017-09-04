<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use Cartalyst\Sentinel\Sentinel;
use Illuminate\Support\Facades\Storage;
use App\Models\UserRoot as UserDirectory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\HtmlUtilitiesTrait as HtmlUtilities;
use Illuminate\Support\Facades\File;

class HomeController extends Controller {

	protected $sentinel = null;

	/**
	 * Set middleware to quard controller.
	 *
	 * @return void
	 */
	public function __construct(Sentinel $sentinel) {
		$this->sentinel = $sentinel;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		$storageDisk = Storage::disk('public');
		$viewData = array();
		$urlSegments = $request->segments();
		$pathSegments = $urlSegments;
		// check num of segments, home will be always 0 index 
		$numOfSegments = count($urlSegments);
		$userRootDirName = UserDirectory::getUserDirectoryName($this->sentinel->getUser()->getUserId());
		$requestedDirectoryName = str_replace('home', $userRootDirName, $pathSegments[$numOfSegments - 1]);
		$storageRequestedPath = str_replace('home', $userRootDirName, $request->path());
		// replace home with users root directory name
		$pathSegments[0] = str_replace('home', $userRootDirName, $pathSegments[0]);
		if ($numOfSegments === 1) {
			$requestedDirectoryPath = '/' . $pathSegments[0] . '/';
		} else
			$requestedDirectoryPath = implode('/', $pathSegments) . '/';
		$allDirectories = $storageDisk->allDirectories();
		// check requested directory path exists or not
		if (!in_array($storageRequestedPath, $allDirectories)) {
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
				$lastModified = $storageDisk->lastModified($directoryPath);
				$viewData['user_disk']->directories[$i] = array('path' => ltrim($request->getRelativeUriForPath($request->getRequestUri() . '/' . $dirName, '/')), 'name' => $dirName, 'last_modified' => Carbon::createFromTimestamp($lastModified)->toDateTimeString());
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
			//$fileName = pathinfo($file, PATHINFO_FILENAME); // don't need it for now !
			$lastModified = $storageDisk->lastModified($filePath);
			$viewData['user_disk']->files[$i] = array('path' => $request->getRelativeUriForPath($request->getRequestUri() . '/' . $file), 'name' => $file, 'last_modified' => Carbon::createFromTimestamp($lastModified)->toDateTimeString());
		}
		// create breadcrumb
		$viewData['breadcrumb'] = HtmlUtilities::createBreadCrumb($request->getPathInfo());
		// set data for dir manager
		$getAllBaseDirectories = $storageDisk->directories($userRootDirName);
		$numOfAllBaseDirectories = count($getAllBaseDirectories);
		$viewData['directory_manager'] = new \stdClass();
		$viewData['directory_manager']->total_menu_items = $numOfAllBaseDirectories;
		for ($i = 0; $i < $numOfAllBaseDirectories; $i++) {
			$directoryRealPath = $getAllBaseDirectories[$i];
			$directoryLinkPath = str_replace($userRootDirName, 'home', $directoryRealPath);
			$viewData['directory_manager']->menu_items[$i]['name'] = basename($directoryLinkPath);
			$viewData['directory_manager']->menu_items[$i]['link'] = $directoryLinkPath;
			$viewData['directory_manager']->menu_items[$i]['total_menu_items'] =count($storageDisk->directories($directoryRealPath));
		}
		//die(dump($viewData['directory_manager']));
		return view('user.home', $viewData);
	}

	public function filePreview(Request $request) {
		dump($request);
		return view('user.home_file_preview');
	}

}
