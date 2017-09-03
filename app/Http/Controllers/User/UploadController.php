<?php

namespace App\Http\Controllers\User;

use Storage;
use Illuminate\Http\Request;
use App\Http\Requests\User\UploadRequest;
use App\Http\Requests\User\MakeDirectoryRequest;
use App\Http\Requests\User\RenameFileRequest;
use App\Http\Requests\User\DeleteFilesRequest;
use App\Http\Requests\User\CopyFilesRequest;
use App\Http\Controllers\Controller;
use App\Models\UserRoot as userRootDirectory;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;

class UploadController extends Controller {

	protected $_referer_request = null;

	public function __construct() {
		// referer(previous) url scheme (When in post this is path from where post was called)
		$this->_referer_request = \GuzzleHttp\Psr7\uri_for(URL::previous());
	}

	protected function getUsersDirectoryPathFromRequestUrl($userRootDirName) {
		return urldecode(str_replace(array('/home'), array(''), $userRootDirName . $this->_referer_request->getPath()));
	}

	protected function isFileOrDirectory($path, $storage) {
		$allDirectories = $storage->allDirectories();
		//$path = '/Tucam/nesto.txt';
		if (in_array($path, $allDirectories)) {
			$type = 'directory';
		} elseif ($storage->exists($path)) {
			$type = 'file';
		} else
			$type = false;
		return $type;
	}

	/**
	 * This method will generate new available copy name (file path)
	 * 
	 * @param string $filePath add your storage path to file 
	 * @param type $storage Storage instance example: $storage = Storage::disk('public');
	 * @param type $type Contains file type it can be file or directory
	 * @return file path (storage path without prefix)
	 */
	protected function generateCopyOfFileName($filePath, $storage, $type = 'file') {
		$extension = '';
		if ($type === 'file') {
			$extension = File::extension($filePath);
			if (!empty($extension)) {
				$pos = strrpos($filePath, '.');
				if ($pos) {
					$filePath = substr_replace($filePath, '', $pos, strlen($filePath));
					$extension = '.' . $extension;
				} else {
					$extension = '';
				}
			}
		}
		$i = 1;
		// try to find available name
		do {
			$copyOfFilePathName = $filePath . '_copy_' . sprintf("%02d", $i) . $extension; // add 0 one digit numbers
			++$i;
		} while ($storage->exists($copyOfFilePathName));
		return $copyOfFilePathName;
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
		$oldFileName = $request->get('old_file_name');
		$renamedFilePath = $request->get('renamed_file_path');
		$renamedFileName = $request->get('renamed_file_name');
		$oldFileFullPath = $storeDirPath . '/' . $oldFileName;
		$extension = File::extension($oldFileFullPath);
		//$change = $storageDisk->get($oldFileFullPath) !== '' ? 'file' : 'directory';
		// determine what is user trying to change directory or file ?
		switch ($this->isFileOrDirectory($oldFileFullPath, $storageDisk)) {
			case('file'):
				$newFileName = $renamedFileName;
				if (!empty($extension)) {
					$newFileName = $newFileName . '.' . $extension; // preserve old extension
				}
				break;
			case('directory'):
				$newFileName = $renamedFileName;
				break;
			default:
				abort(404, 'Your file is missing!!'); // throw some random error ...
		}
		$newFileNameFullPath = $storeDirPath . '/' . $newFileName;
		// check if new file name already exists 
		if ($storageDisk->exists($newFileNameFullPath) && $newFileName !== $oldFileName) {
			$request->session()->flash('upload_warning_messages', $newFileName . " already exists! <br>");
		} else if (!$storageDisk->exists($oldFileFullPath) && $newFileName !== $oldFileName) { // check if data was tempered and file doesn't exist
			$request->session()->flash('upload_warning_messages', $oldFileName . ": File that you are trying to rename doesn't exist! <br>");
		} else {
			try {
				$storageDisk->move($oldFileFullPath, $newFileNameFullPath);
			} catch (\Exception $ex) {
				//die(dump($ex));
				// this is the same file because we had previous check, if file exists but it is not the same file
				if (!$ex instanceof \League\Flysystem\FileExistsException ||
						$ex instanceof \League\Flysystem\FileExistsException && $ex->getMessage() !== 'File already exists at path: ' . $ex->getPath()
				) {
					throw $ex;
				}
			}
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
			switch ($this->isFileOrDirectory($fullFilePath, $storageDisk)) {
				case('file'):
					$storageDisk->delete($fullFilePath);
					$msg['success'][] = "<strong>Deleted file:</strong> $file <br>";
					break;
				case('directory'):
					$msg['success'][] = "<strong>Deleted directory:</strong> $file <br>";
					$storageDisk->deleteDirectory($fullFilePath);
					break;
				default:
					break;
			}
		}
		if (!empty($msg['success'])) {
			$request->session()->flash('upload_success_messages', implode('', $msg['success']));
		}
		return redirect($this->_referer_request->getPath());
	}

	public function copyFiles(CopyFilesRequest $request) {
		$copyFileList = json_decode($request->get('copy_files'));
		$numOfFilesToHandle = is_array($copyFileList) ? count($copyFileList) : 0;
		if (!$numOfFilesToHandle) {
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
		$fileSystem = new Filesystem();
		$storageDiskPathPrefix = $storageDisk->getAdapter()->getPathPrefix();
		for ($i = 0; $i < $numOfFilesToHandle; $i++) {
			$file = $copyFileList[$i];
			$fullFilePath = $storeDirPath . '/' . $file;
			$type = $this->isFileOrDirectory($fullFilePath, $storageDisk);
			if ($type) {
				$fullFilePathCopy = $this->generateCopyOfFileName($fullFilePath, $storageDisk, $type);
				$msg['success'][] = "<strong>Copied $type:</strong> $file to " . basename($fullFilePathCopy) . "<br>";
				if ($type == 'directory') {
					$fileSystem->copyDirectory($storageDiskPathPrefix . $fullFilePath, $storageDiskPathPrefix . $fullFilePathCopy, false);
				} else
					$storageDisk->copy($fullFilePath, $fullFilePathCopy);
			}
		}
		if (count($msg['success'])) {
			$request->session()->flash('upload_success_messages', implode('', $msg['success']));
		}
		return redirect($this->_referer_request->getPath());
	}

}
