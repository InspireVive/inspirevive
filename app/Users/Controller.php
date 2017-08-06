<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace App\Users;

use App\Users\Models\User;
use App\Volunteers\Models\Volunteer;
use App\Volunteers\Models\VolunteerHour;
use Infuse\Auth\Exception\AuthException;
use Infuse\HasApp;
use Infuse\View;
use MongoDB\Driver\Exception\AuthenticationException;

class Controller
{
    use HasApp;

    public static $viewsDir;

    public function __construct()
    {
        self::$viewsDir = __DIR__.'/views';
    }

    public function loginForm($req, $res, $error = false)
    {
        $this->ensureHttps($req, $res);

        if ($this->app['user']->isSignedIn()) {
            return $res->redirect('/');
        }

        $redir = urldecode($req->query('redir'));

        if (!$redir) {
            $redir = $req->request('redir');
        }

        if (!$redir) {
            $redir = $req->session('redir');
        }

        return new View('login', [
            'redir' => $redir,
            'title' => 'Login',
            'loginUsername' => $req->request('username'),
            'loginForm' => true,
            'error' => $error
        ]);
    }

    public function login($req, $res)
    {
        try {
            $this->app['auth']->authenticate('traditional');
        } catch (AuthException $e) {
            $message = $e->getMessage();
            return $this->loginForm($req, $res, $message);
        }

        $redir = ($req->request('redir')) ? $req->request('redir') : $req->cookies('redirect');

        if (!empty($redir)) {
            $res->setCookie('redirect', '', time() - 86400, '/');
            $res->redirect($redir);
        } else {
            // prompt user to fill out volunteer application
            $currentUser = $this->app['user'];
            if (!$currentUser->hasCompletedVolunteerApplication() && $currentUser->invited_by) {
                $res->redirect('/volunteers/application');
            } else {
                $res->redirect('/profile');
            }
        }
    }

    public function forgotForm($req, $res, $error = false)
    {
        $this->ensureHttps($req, $res);

        if ($this->app['user']->isSignedIn()) {
            $this->app['auth']->logout();
        }

        $user = false;
        if (!$req->params('success') && $token = $req->params('id')) {
            $user = $this->app['auth']->getUserFromForgotToken($token);

            if (!$user) {
                return $res->setCode(404);
            }
        }

        return new View('forgot', [
            'success' => $req->params('success'),
            'title' => 'Forgot Password',
            'id' => $req->params('id'),
            'email' => $req->request('email'),
            'user' => $user,
            'error' => $error
        ]);
    }

    public function forgotStep1($req, $res)
    {
        if ($this->app['user']->isSignedIn()) {
            return $res->redirect('/');
        }

        try {
            $this->app['auth']->forgotStep1($req->request('email'));
        } catch (AuthenticationException $e) {
            return $this->forgotForm($req, $res, $e->getMessage());
        }

        $req->setParams(['success' => true]);

        return $this->forgotForm($req, $res);
    }

    public function forgotStep2($req, $res)
    {
        try {
            $this->app['auth']->forgotStep2($req->params('id'), $req->request('password'));
        } catch (AuthenticationException $e) {
            return $this->forgotForm($req, $res, $e->getMessage());
        }

        $req->setParams(['success' => true]);

        return $this->forgotForm($req, $res);
    }

    public function logout($req, $res)
    {
        $this->app['auth']->logout();

        $res->setCookie('redirect', '', time() - 86400, '/');

        $res->redirect('/');
    }

    public function signupForm($req, $res)
    {
        $this->ensureHttps($req, $res);

        if ($this->app['user']->isSignedIn()) {
            $this->app['auth']->logout();
        }

        $redir = urldecode($req->query('redir'));

        if (!$redir) {
            $redir = $req->request('redir');
        }

        if (!$redir) {
            $redir = $req->session('redir');
        }

        return new View('signup', [
            'title' => 'Join',
            'redir' => $redir,
            'name' => $req->request('name'),
            'signupUsername' => ($req->request('username')) ? $req->request('username') : $req->query('username'),
            'signupEmail' => ($req->request('email')) ? $req->request('email') : $req->query('email'),
            'signupForm' => true,
        ]);
    }

    public function signup($req, $res)
    {
        if ($this->app['user']->isSignedIn()) {
            return $res->redirect('/');
        }

        $email = $req->request('email');
        $password = $req->request('password');
        $info = [
            'username' => $req->request('username'),
            'email' => $email,
            'password' => $password,
            'ip' => $req->ip(), ];

        $user = User::registerUser($info);

        if ($user) {
            $this->app['auth']->getStrategy('traditional')
                ->login($email, $password[0], true);
            return $res->redirect('/');
        } else {
            return $this->signupForm($req, $res);
        }
    }

    public function verifyEmail($req, $res)
    {
        $user = $this->app['auth']->verifyEmailWithToken($req->params('id'));

        // log the user in
        if ($user) {
            $this->app['auth']->signInUser($user, 'verification_email');
        }

        return new View('verifyEmail', [
            'title' => 'Verify Email',
            'success' => $user,
        ]);
    }

    public function sendVerifyEmail($req, $res)
    {
        $user = $this->app['user'];
        if (!$user->isSignedIn()) {
            return $res->setCode(404);
        }

        // check that the user is not verified
        if ($user->isVerified(false)) {
            return $res->setCode(404);
        }

        // send the email
        $this->app['auth']->sendVerificationEmail($user);

        return new View('verifyEmailSent', [
            'title' => 'Email Verification Sent', ]);
    }

    public function editAccountSettings($req, $res)
    {
        $user = $this->app['user'];
        if (!$user->isSignedIn()) {
            return $res->setCode(403);
        }

        $success = $user->set($req->request());

        if ($success) {
            $req->setParams(['success' => true]);

            return $this->accountSettings($req, $res);
        } else {
            return $this->accountSettings($req, $res);
        }
    }

    private function ensureHttps($req, $res)
    {
        if (!$req->isSecure() && $this->app['config']->get('app.ssl')) {
            $url = str_replace('http://', 'https://', $req->url());
            $res->redirect($url, 301);
        }
    }

    /* InspireVive Routes */

    public function userProfile($req, $res)
    {
        $user = User::where('username', $req->params('username'))
            ->first();

        if (!$user) {
            return $res->setCode(404);
        }

        return new View('profile', [
            'title' => $user->username,
            'user' => $user->toArray(),
            'userObj' => $user,
            'isMine' => $user->id() == $this->app['user']->id(),
        ]);
    }

    public function myProfile($req, $res)
    {
        $currentUser = $this->app['user'];

        if (!$currentUser->isSignedIn()) {
            return $res->redirect('/login');
        }

        // organizations user is volunteer at
        $volunteersAt = Volunteer::where('organization IN ( SELECT id FROM Organizations )')
            ->where('role', Volunteer::ROLE_VOLUNTEER, '>=')
            ->where('uid', $currentUser->id())
            ->all();

        // recent volunteer hours
        $recentVolunteerHours = VolunteerHour::where('uid', $currentUser->id())
            ->where('timestamp', strtotime('-1800 days'), '>=')
            ->sort('timestamp DESC')
            ->all();

        return new View('myProfile', [
            'title' => 'My Profile',
            'profileTab' => true,
            'tabClass' => 'profile',
            'recentVolunteerHours' => $recentVolunteerHours,
            'volunteersAt' => $volunteersAt,
            'completedApplication' => $currentUser->hasCompletedVolunteerApplication(),
        ]);
    }

    public function accountSettings($req, $res)
    {
        $user = $this->app['user'];
        if (!$user->isSignedIn()) {
            if ($req->isHtml()) {
                return $res->redirect('/');
            } else {
                return $res->setCode(403);
            }
        }

        $res->setCookie('redirect', '/account');

        return new View('account', [
            'success' => $req->params('success'),
            'title' => 'Account Settings',
            'section' => $req->request('section'),
        ]);
    }
}
