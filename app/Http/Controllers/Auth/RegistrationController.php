<?php

namespace App\Http\Controllers\Auth;

use Lang;
use Carbon\Carbon;
use InvalidArgumentException;
use Centaur\Replies\FailureReply;
use Centaur\Replies\SuccessReply;
use Mail;
use Session;
use Sentinel;
use Activation;
use Storage;
use App\Http\Requests;
use Centaur\AuthManager;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserRoot;

class RegistrationController extends Controller {

	/** @var Centaur\AuthManager */
	protected $authManager;

	/**
	 * Create a new authentication controller instance.
	 *
	 * @return void
	 */
	public function __construct(AuthManager $authManager) {
		$this->middleware('sentinel.guest');
		$this->authManager = $authManager;
	}

	/**
	 * Show the registration form
	 * @return View
	 */
	public function getRegister() {
		return view('auth.register');
	}

	/**
	 * Create a new user instance after a valid registration.
	 *
	 * @param  array  $data
	 * @return Response|Redirect
	 */
	protected function postRegister(Request $request) {
		// Validate the form data
		$result = $this->validate($request, [
			'email' => 'required|email|max:255|unique:users',
			'password' => 'required|confirmed|min:6',
		]);

		// Assemble registration credentials
		$credentials = [
			'email' => trim($request->get('email')),
			'password' => $request->get('password'),
		];

		// Attempt the registration
		$result = $this->authManager->register($credentials);

		if ($result->isFailure()) {
			return $result->dispatch();
		}

		// Set user role
		$role = Sentinel::findRoleBySlug('basic');
		$role->users()->attach($result->user->id);

		// Send the activation email
		$code = $result->activation->getCode();
		$email = $result->user->email;
		Mail::queue(
				'email.welcome', ['code' => $code, 'email' => $email], function ($message) use ($email) {
			$message->to($email)
					->subject('Your account has been created');
		}
		);

		// Ask the user to check their email for the activation link
		$result->setMessage('Registration complete.  Please check your email for activation instructions.');

		// There is no need to send the payload data to the end user
		$result->clearPayload();

		// Return the appropriate response
		return $result->dispatch(route('auth.login.form'));
	}

	/**
	 * Activate a user if they have provided the correct code
	 * @param  string $code
	 * @return Response|Redirect
	 */
	public function getActivate(Request $request, $code) {
		// Attempt the registration
		//$result = $this->authManager->activate($code);
		$result = $this->activateUserSentinel($code);
		if ($result['message']->isFailure()) {
			// Normally an exception would trigger a redirect()->back() However,
			// because they get here via direct link, back() will take them
			// to "/";  I would prefer they be sent to the login page.
			$result['message']->setRedirectUrl(route('auth.login.form'));
			return $result['message']->dispatch();
		}

		// Create user root directory
		$dir_name = md5(uniqid());
		$directories = Storage::disk('public')->allDirectories();
		if (!in_array($dir_name, $directories)) {
			Storage::disk('public')->makeDirectory($dir_name);
			$user_root = new UserRoot();
			$user_root->saveDir($dir_name, $result['user']->id);
		} else {
			session()->flash('error', 'The directory already exists.');
		}


		// Ask the user to check their email for the activation link
		$result['message']->setMessage('Registration complete.  You may now log in.');

		// There is no need to send the payload data to the end user
		$result['message']->clearPayload();

		// Return the appropriate response
		return $result['message']->dispatch(route('auth.login.form'));
	}

	/**
	 * This method will replace activation method from Centaur AuthManager
	 * 
	 * @param type $code
	 * @return SuccessReply|\App\Http\Controllers\Auth\FailureReply
	 * @throws InvalidArgumentException
	 */
	public function activateUserSentinel($code) {
		try {
			// Attempt to fetch the user via the activation code
			$activation = $this->authManager->activations
					->createModel()
					->newQuery()
					->where('code', $code)
					->where('completed', false)
					->where('created_at', '>', Carbon::now()->subSeconds(259200))
					->first();
			if (!$activation) {
				$message = $this->translate("activation_problem", "Invalid or expired activation code.");
				//die('<pre>' . print_r($message, 1) . '</pre>');
				//return array('message' => new FailureReply($message));
				throw new InvalidArgumentException($message);
			}
			$sentinel = app()->make('sentinel');
			$user = $sentinel->findUserById($activation->user_id);
			// Complete the user's activation
			$this->authManager->activations->complete($user, $code); // uncomment
			// While we are here, lets remove any expired activations
			$this->authManager->activations->removeExpired();
		} catch (Exception $e) {
			return $this->authManager->returnException($e);
		}

		if ($user) {
			$message = $this->translate("activation_success", "Activation successful.");
			return array('user' => $user, 'message' => new SuccessReply($message));
		}

		$message = $this->translate('activation_failed', 'There was a problem activating your account.');
		return new FailureReply(array($message));
	}

	/**
	 * Show the Resend Activation form
	 * @return View
	 */
	public function getResend() {
		return view('auth.resend');
	}

	/**
	 * Handle a resend activation request
	 * @return Response|Redirect
	 */
	public function postResend(Request $request) {
		// Validate the form data
		$result = $this->validate($request, [
			'email' => 'required|email|max:255'
		]);

		// Fetch the user in question
		$user = Sentinel::findUserByCredentials(['email' => $request->get('email')]);

		// Only send them an email if they have a valid, inactive account
		if (!Activation::completed($user)) {
			// Generate a new code
			$activation = Activation::create($user);

			// Send the email
			$code = $activation->getCode();
			$email = $user->email;
			Mail::queue(
					'email.welcome', ['code' => $code, 'email' => $email], function ($message) use ($email) {
				$message->to($email)
						->subject('Account Activation Instructions');
			}
			);
		}

		$message = 'New instructions will be sent to that email address if it is associated with a inactive account.';

		if ($request->ajax()) {
			return response()->json(['message' => $message], 200);
		}

		Session::flash('success', $message);
		return redirect()->route('auth.login.form');
	}

	/**
	 * Helper method for facilitating string translation
	 * @param  string $key
	 * @param  string $message
	 * @return string
	 */
	protected function translate($key, $message) {
		$key = 'centaur.' . $key;

		if (Lang::has($key)) {
			$message = trans($key);
		}

		return $message;
	}

}
