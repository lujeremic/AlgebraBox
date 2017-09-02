<?php

namespace App\Http\Controllers\User;

use Storage;
use Illuminate\Http\Request;
use App\Http\Requests\User\UploadRequest;
use App\Http\Requests\User\MakeDirectoryRequest;
use App\Http\Requests\User\RenameFileRequest;
use App\Http\Requests\User\DeleteFilesRequest;
use App\Http\Controllers\Controller;
use App\Models\UserRoot as userRootDirectory;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;

class UploadController extends Controller {

	protected $_referer_request = null;

	public function __construct() {
		// referer(previous) url scheme (When in post this is path from where post was called)
		$this->_referer_request = \GuzzleHttp\Psr7\uri_for(URL::previous());
	}

	protected function getUsersDirectoryPathFromRequestUrl($userRootDirName) {
		return urldecode(str_replace(array('/home'), array(''), $userRootDirName . $this->_referer_request->getPath()));
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
		if (strlen($noticeMsg) > 0) {
			$request->session()->flash('upload_warning_messages', $noticeMsg);
		}
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

	public function renameFile(RenameFileRequest $request) {
		$userRootDirName = userRootDirectory::getUserDirectoryName($request->user()->id);
		// user dir is missing
		if (is_null($userRootDirName)) {
			abort(404, 'Seems like your main user directory is missing, please contact support!');
		}
		$storageDisk = Storage::disk('public');
		$storeDirPath = $this->getUsersDirectoryPathFromRequestUrl($userRootDirName);
		$renamedFilePath = $request->get('renamed_file_path');
		$renamedFileName = $request->get('renamed_file_name');

		$oldFileName = basename($renamedFilePath);
		$oldFileFullPath = $storeDirPath . '/' . $oldFileName;
		$extension = File::extension($oldFileFullPath);
		// determine what is user trying to change directory or file ?
		$change = $storageDisk->get($oldFileFullPath) !== '' ? 'file' : 'directory';
		$newFileName = $change !== 'file' ? $renamedFileName : $renamedFileName . '.' . $extension; // preserve old extension
		$newFileNameFullPath = $storeDirPath . '/' . $newFileName;
		// check if new file name already exists 
		if ($storageDisk->exists($newFileNameFullPath)) {
			$request->session()->flash('upload_warning_messages', $newFileName . " already exists! <br>");
		} else if (!$storageDisk->exists($oldFileFullPath)) { // check if data was tempered and file doesn't exist
			$request->session()->flash('upload_warning_messages', $oldFileName . ": File that you are trying to rename doesn't exist! <br>");
		} else {
			$storageDisk->move($oldFileFullPath, $newFileNameFullPath);
		}
		return redirect($this->_referer_request->getPath());
	}

	public function deleteFiles(DeleteFilesRequest $request) {
		$deleteFileList = json_decode($request->get('delete_files'));
		$numOfFilesToDelete = is_array($deleteFileList) ? count($deleteFileList) : 0;
		if (!$numOfFilesToDelete) {
			$request->session()->flash('upload_warning_messages', "Nothing to delete! <br>");
			return redirect($this->_referer_request->getPath());
		}
		$userRootDirName = userRootDirectory::getUserDirectoryName($request->user()->id);
		// user dir is missing
		if (is_null($userRootDirName)) {
			abort(404, 'Seems like your main user directory is missing, please contact support!');
		}
		$storageDisk = Storage::disk('public');
		$storeDirPath = $this->getUsersDirectoryPathFromRequestUrl($userRootDirName);
		// set success messages
		$msg = array(
			'success',
			'errors',
		);
		for ($i = 0; $i < $numOfFilesToDelete; $i++) {
			$file = $deleteFileList[$i];
			$fullFilePath = $storeDirPath . '/' . $file;
			if ($storageDisk->exists($fullFilePath)) {
				// check if file or directory
				if ($storageDisk->get($fullFilePath) !== '') {
					$storageDisk->delete($fullFilePath);
					$msg['success'][] = "<strong>Deleted file:</strong> $file <br>";
				} else {
					$msg['success'][] = "<strong>Deleted directory:</strong> $file <br>";
					$storageDisk->deleteDirectory($fullFilePath);
				}
			}
		}
		if (count($msg['success'])) {

			$request->session()->flash('upload_success_messages', implode('', $msg['success']));
		}
		return redirect($this->_referer_request->getPath());
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
