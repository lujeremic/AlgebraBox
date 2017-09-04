<?php

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | This file is where you may define all of the routes that are handled
  | by your application. Just tell Laravel the URIs it should respond
  | to using a Closure or controller method. Build something great!
  |
 */
// Index page
Route::get('/', ['as' => 'index', 'uses' => 'IndexController@index']);


// Authorization
Route::get('login', ['as' => 'auth.login.form', 'uses' => 'Auth\SessionController@getLogin']);
Route::post('login', ['as' => 'auth.login.attempt', 'uses' => 'Auth\SessionController@postLogin']);
Route::get('logout', ['as' => 'auth.logout', 'uses' => 'Auth\SessionController@getLogout']);

// Registration
Route::get('register', ['as' => 'auth.register.form', 'uses' => 'Auth\RegistrationController@getRegister']);
Route::post('register', ['as' => 'auth.register.attempt', 'uses' => 'Auth\RegistrationController@postRegister']);

// Activation
Route::get('activate/{code}', ['as' => 'auth.activation.attempt', 'uses' => 'Auth\RegistrationController@getActivate']);
Route::get('resend', ['as' => 'auth.activation.request', 'uses' => 'Auth\RegistrationController@getResend']);
Route::post('resend', ['as' => 'auth.activation.resend', 'uses' => 'Auth\RegistrationController@postResend']);

// Password Reset
Route::get('password/reset/{code}', ['as' => 'auth.password.reset.form', 'uses' => 'Auth\PasswordController@getReset']);
Route::post('password/reset/{code}', ['as' => 'auth.password.reset.attempt', 'uses' => 'Auth\PasswordController@postReset']);
Route::get('password/reset', ['as' => 'auth.password.request.form', 'uses' => 'Auth\PasswordController@getRequest']);
Route::post('password/reset', ['as' => 'auth.password.request.attempt', 'uses' => 'Auth\PasswordController@postRequest']);



/* ############# ADMIN ############## */
Route::group(['prefix' => 'admin'], function () {
	// Dashboard
	Route::get('/', ['as' => 'admin.dashboard', 'uses' => 'Admin\DashboardController@index']);
	// Users
	Route::resource('users', 'Admin\UserController');
	// Roles
	Route::resource('roles', 'Admin\RoleController');
});
/* ############# Regular User ############## */
Route::group(['middleware' => ['web']], function () {
	// Home page
	Route::get('home/{slugs?}', function(Illuminate\Http\Request $request) {
		$controllerPath = '\App\Http\Controllers\User\HomeController';
		// check if request is file preview
		$filePreview = $request->input('preview');
		if (!is_null($filePreview)) {
			return App::call($controllerPath . '@filePreview');
		}
		return App::call($controllerPath . '@index');
	})->where('slugs', '(.*)')->name('home')->middleware('sentinel.auth');
	// post Upload files
	//Route::post('/upload', 'User\UploadController@uploadFiles');
	Route::post('home/{slugs?}', function(Illuminate\Http\Request $request) {
		$action = $request->get('action');
		$allowedRequests = array(// prevent unnecessary requests
			'upload-files' => array('controller' => '\App\Http\Controllers\User\UploadController', 'method' => 'uploadFiles', 'params' => array()),
			'create-folder' => array('controller' => '\App\Http\Controllers\User\UploadController', 'method' => 'makeDirectory', 'params' => array()),
			'download-files' => array('controller' => '\App\Http\Controllers\User\UploadController', 'method' => 'downloadFiles', 'params' => array()),
			'rename-file' => array('controller' => '\App\Http\Controllers\User\UploadController', 'method' => 'renameFile', 'params' => array()),
			'delete-files' => array('controller' => '\App\Http\Controllers\User\UploadController', 'method' => 'deleteFiles', 'params' => array()),
			'copy-files' => array('controller' => '\App\Http\Controllers\User\UploadController', 'method' => 'copyFiles', 'params' => array()),
			'move-files' => array('controller' => '\App\Http\Controllers\User\UploadController', 'method' => 'moveFiles', 'params' => array()),
		);
		// we only allow certain ajax methods in users home group
		if ($request->ajax()) {
			$allowedRequests['DM-LoadMoreMenuItems'] = array('controller' => '\App\Http\Controllers\User\UploadController', 'method' => 'DirectoryManagerLoadMoreMenuItemsAjax', 'params' => array());
		}
		// check our request 
		if (!isset($allowedRequests[$action])) {
			abort(400, 'You have a bad request!', array('location', URL::previous()));
		}
		// store, don't do unnecessary lookups
		$appCallData = $allowedRequests[$action];
		return App::call($appCallData['controller'] . '@' . $appCallData['method'], $appCallData['params']);
	})->where('slugs', '(.*)')->middleware('sentinel.auth');
});

