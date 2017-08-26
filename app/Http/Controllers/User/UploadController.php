<?php

namespace App\Http\Controllers\User;

use Storage;
use Illuminate\Http\Request;
use App\Http\Requests\Users\UploadRequest;
use App\Http\Requests\Users\MakeDirectoryRequest;
use App\Http\Controllers\Controller;
use App\Models\UserRoot as userRootDirectory;
use Illuminate\Support\Facades\URL;

class UploadController extends Controller {

	protected $_referer_request = null;

	public function __construct() {
		// referer(previous) url scheme (When in post this is path from where post was called)
		$this->_referer_request = \GuzzleHttp\Psr7\uri_for(URL::previous());
	}

	public function uploadFiles(UploadRequest $request) {
		$userRootDirName = userRootDirectory::getUserDirectoryName($request->user()->id);
		// user dir is missing
		if (is_null($userRootDirName)) {
			abort(404, 'Seems like your main user directory is missing, please contact support!');
		}
		$storeDirPath = $this->getUsersDirectoryPathFromRequestUrl($userRootDirName);
		$allUploadedFiles = $request->file('files');
		$noticeMsg = "";
		foreach ($allUploadedFiles as $value) {
			$fileName = $value->getClientOriginalName();
			if (Storage::disk('public')->exists($storeDirPath . '/' . $fileName)) {
				$noticeMsg .= $fileName . " already exists! <br>";
				continue;
			}
			$value->storeAs($storeDirPath, $value->getClientOriginalName(), 'public');
		}
		$request->session()->flash('upload_warning_messages', $noticeMsg);
		return redirect($this->_referer_request->getPath());
	}

	public function makeDirectory(MakeDirectoryRequest $request) {
		$storage = Storage::disk('public');
		$userRootDirName = userRootDirectory::getUserDirectoryName($request->user()->id);
		// user dir is missing
		if (is_null($userRootDirName)) {
			abort(404, 'Seems like your main user directory is missing, please contact support!');
		}
		// get current directory path
		$storeDirPath = $this->getUsersDirectoryPathFromRequestUrl($userRootDirName);
		// set new directory path
		$newDirName = $request->get('directory_name');
		$newDirPath = $storeDirPath . '/' . $newDirName;
		// create directory path
		if ($storage->exists($newDirPath)) {
			$request->session()->flash('upload_warning_messages', 'This directory "' . $newDirName . '" already exists!');
		} else
			$storage->makeDirectory($newDirPath);
		return redirect($this->_referer_request->getPath());
	}

	protected function getUsersDirectoryPathFromRequestUrl($userRootDirName) {
		return urldecode(str_replace(array('/home'), array(''), $userRootDirName . $this->_referer_request->getPath()));
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		//
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id) {
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id) {
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		//
	}

}
