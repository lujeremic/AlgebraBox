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
Route::group(['prefix' => 'home'], function () {
	// Home page
	Route::get('/{slugs?}', function(Illuminate\Http\Request $request) {
		$controllerPath = '\App\Http\Controllers\User\HomeController';
		// check if request is file preview
		$filePreview = $request->input('preview');
		if (!is_null($filePreview)) {
			return App::call($controllerPath . '@filePreview');
		}
		return App::call($controllerPath . '@index');
	})->where('slugs', '(.*)')->name('home');
	// post Upload files
	//Route::post('/upload', 'User\UploadController@uploadFiles');
	Route::post('/{slugs?}', function(Illuminate\Http\Request $request) {
		$action = $request->get('action');
		$method = ''; // Just for being hard to get :)
		switch ($action) {
			case 'upload-files':
				$method = 'uploadFiles';
				break;
			case 'create-folder':
				$method = 'makeDirectory';
				break;
			default :
				abort(400, 'You have a bad request!');
				break;
		}
		return App::call('\App\Http\Controllers\User\UploadController@' . $method);
	})->where('slugs', '(.*/action)');
});
