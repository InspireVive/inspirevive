<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

namespace app\users;

use infuse\View;
use app\facebook\models\FacebookProfile;
use app\instagram\models\InstagramProfile;
use app\twitter\models\TwitterProfile;
use app\users\models\User;
use app\volunteers\models\Volunteer;
use app\volunteers\models\VolunteerHour;

class Controller
{
    use \InjectApp;

    public static $properties = [
        'models' => [
            'User',
        ],
        'defaultModel' => 'User',
        'routes' => [
            'get /users/login' => 'loginForm',
            'post /users/login' => 'login',
            'get /users/logout' => 'logout',
            'get /users/signup' => 'signupForm',
            'post /users/signup' => 'signup',
            'get /users/verifyEmail/:id' => 'verifiyEmail',
            'get /users/verify/:id' => 'sendVerifyEmail',
            'get /users/forgot' => 'forgotForm',
            'post /users/forgot' => 'forgotStep1',
            'get /users/forgot/:id' => 'forgotForm',
            'post /users/forgot/:id' => 'forgotStep2',
            'get /users/account' => 'accountSettings',
            'post /users/account' => 'editAccountSettings',
            'get /users/:username' => 'userProfile',
        ],
    ];

    public static $scaffoldAdmin;

    public static $viewsDir;

    public function __construct()
    {
        self::$viewsDir = __DIR__.'/views';
    }

    public function loginForm($req, $res)
    {
        $this->ensureHttps($req, $res);

        if ($this->app['user']->isLoggedIn()) {
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
            'loginUsername' => $req->request('user_email'),
            'loginForm' => true, ]);
    }

    public function login($req, $res)
    {
        $password = $req->request('password');

        if (is_array($req->request('user_password'))) {
            $password = $req->request('user_password');
            $password = reset($password);
        }

        $success = $this->app['auth']->login($req->request('user_email'), $password, $req, true);

        if ($req->isHtml()) {
            if ($success) {
                $redir = ($req->request('redir')) ? $req->request('redir') : $req->cookies('redirect');

                if (!empty($redir)) {
                    $req->setCookie('redirect', '', time() - 86400, '/');
                    $res->redirect($redir);
                } else {
                    // prompt user to fill out volunteer application
                    $currentUser = $this->app['user'];
                    if (!$currentUser->hasCompletedVolunteerApplication() && $currentUser->get('invited_by')) {
                        $res->redirect('/volunteers/application');
                    } else {
                        $res->redirect('/profile');
                    }
                }
            } else {
                return $this->loginForm($req, $res);
            }
        } elseif ($req->isJson()) {
            if ($success) {
                $res->json(['success' => true]);
            } else {
                $res->json(['error' => true]);
            }
        } else {
            $res->setCode(404);
        }
    }

    public function forgotForm($req, $res)
    {
        $this->ensureHttps($req, $res);

        if ($this->app['user']->isLoggedIn()) {
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
            'user' => $user, ]);
    }

    public function forgotStep1($req, $res)
    {
        if ($this->app['user']->isLoggedIn()) {
            return $res->redirect('/');
        }

        $success = $this->app['auth']->forgotStep1($req->request('email'), $req->ip());

        $req->setParams([
            'success' => $success, ]);

        return $this->forgotForm($req, $res);
    }

    public function forgotStep2($req, $res)
    {
        $success = $this->app['auth']->forgotStep2($req->params('id'), $req->request('user_password'), $req->ip());

        $req->setParams([
            'success' => $success, ]);

        return $this->forgotForm($req, $res);
    }

    public function logout($req, $res)
    {
        $this->app['auth']->logout();

        $req->setCookie('redirect', '', time() - 86400, '/');

        if ($req->isHtml()) {
            $res->redirect('/');
        } elseif ($req->isJson()) {
            $res->json(['success' => true]);
        }
    }

    public function signupForm($req, $res)
    {
        $this->ensureHttps($req, $res);

        if ($this->app['user']->isLoggedIn()) {
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
            'signupEmail' => ($req->request('user_email')) ? $req->request('user_email') : $req->query('user_email'),
            'signupForm' => true,
        ]);
    }

    public function signup($req, $res)
    {
        if ($this->app['user']->isLoggedIn()) {
            return $res->redirect('/');
        }

        $info = [
            'username' => $req->request('username'),
            'user_email' => $req->request('user_email'),
            'user_password' => $req->request('user_password'),
            'ip' => $req->ip(), ];

        $user = User::registerUser($info);

        if ($user) {
            if ($req->isHtml()) {
                return $this->login($req, $res);
            } elseif ($req->isJson()) {
                $req->json([
                    'user' => $user->toArray(),
                    'success' => true, ]);
            } else {
                $res->setCode(404);
            }
        } else {
            return $this->signupForm($req, $res);
        }
    }

    public function verifiyEmail($req, $res)
    {
        $user = $this->app['auth']->verifyEmailWithLink($req->params('id'));

        // log the user in
        if ($user) {
            $this->app['auth']->signInUser($user->id());
        }

        return new View('verifyEmail', [
            'title' => 'Verify E-mail',
            'success' => $user, ]);
    }

    public function sendVerifyEmail($req, $res)
    {
        // look up user
        $user = new User($req->params('id'));

        // check that the user is not verified
        if ($user->isVerified(false)) {
            return $res->setCode(404);
        }

        // send the e-mail
        $this->app['auth']->sendVerificationEmail($user);

        return new View('verifyEmailSent', [
            'title' => 'E-mail Verification Sent', ]);
    }

    public function editAccountSettings($req, $res)
    {
        $user = $this->app['user'];
        if (!$user->isLoggedIn()) {
            return $res->setCode(403);
        }

        $success = $user->set($req->request());

        if ($success) {
            if ($req->isHtml()) {
                $req->setParams(['success' => true]);

                return $this->accountSettings($req, $res);
            } elseif ($req->isJson()) {
                $res->json(['success' => true]);
            }
        } else {
            if ($req->isHtml()) {
                return $this->accountSettings($req, $res);
            } elseif ($req->isJson()) {
                $res->json(['error' => true]);
            }
        }
    }

    private function ensureHttps($req, $res)
    {
        if (!$req->isSecure() && $this->app['config']->get('site.ssl-enabled')) {
            $url = str_replace('http://', 'https://', $req->url());
            $res->redirect($url, 301);
        }
    }

    /* InspireVive Routes */

    public function userProfile($req, $res)
    {
        $userSearch = User::find([
            'where' => [
                'username' => $req->params('username'),
            ], ]);

        if ($userSearch['count'] != 1) {
            return $res->setCode(404);
        }

        $user = $userSearch['models'][0];
        $user->load();

        return new View('profile', [
            'title' => $user->username,
            'user' => $user->toArray(),
            'userObj' => $user,
            'isMine' => $user->id() == $this->app['user']->id(),
            'facebookConnected' => $user->facebookConnected(),
            'twitterConnected' => $user->twitterConnected(),
            'instagramConnected' => $user->instagramConnected(),
        ]);
    }

    public function myProfile($req, $res)
    {
        $currentUser = $this->app['user'];

        if (!$currentUser->isLoggedIn()) {
            return $res->redirect('/login');
        }

        // organizations user is volunteer at
        $volunteersAt = Volunteer::find([
            'where' => [
                'organization IN ( SELECT id FROM Organizations )',
                'role >= '.Volunteer::ROLE_VOLUNTEER,
                'uid' => $currentUser->id(), ], ])['models'];

        // recent volunteer hours
        $recentVolunteerHours = VolunteerHour::find([
            'where' => [
                'uid' => $currentUser->id(),
                'timestamp >= '.strtotime('-1800 days'), ],
            'sort' => 'timestamp DESC',
            'limit' => 1000, ])['models'];

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
        if (!$user->isLoggedIn()) {
            if ($req->isHtml()) {
                return $res->redirect('/');
            } else {
                return $res->setCode(403);
            }
        }

        $req->setCookie('redirect', '/account');

        return new View('account', [
            'success' => $req->params('success'),
            'title' => 'Account Settings',
            'section' => $req->params('section'),
            'facebookConnected' => $user->facebookConnected(),
            'twitterConnected' => $user->twitterConnected(),
            'instagramConnected' => $user->instagramConnected(), ]);
    }

    public function finishSignup($req, $res)
    {
        $params = [
            'title' => 'Finish Signup',
            'userEmail' => $req->request('user_email'),
            'username_post' => $req->request('username'),
        ];

        if ($fbid = $req->session('fbid')) {
            $profile = new FacebookProfile($fbid);

            $profile->load();

            $params['profileUrl'] = $profile->url();
            $params['profilePic'] = $profile->profilePicture();
            $params['username'] = $profile->username;
        } elseif ($tid = $req->session('tid')) {
            $profile = new TwitterProfile($tid);

            $profile->load();

            $params['profileUrl'] = $profile->url();
            $params['profilePic'] = $profile->profilePicture();
            $params['username'] = $profile->username;
        } elseif ($iid = $req->session('iid')) {
            $profile = new InstagramProfile($iid);

            $profile->load();

            $params['profileUrl'] = $profile->url();
            $params['profilePic'] = $profile->profilePicture();
            $params['username'] = $profile->username;
        } else {
            return $res->setCode(404);
        }

        $params['username'] = preg_replace('/[^a-z0-9]+/i', '', $params['username']);

        return new View('finishSignup', $params);
    }

    public function finishSignupPost($req, $res)
    {
        $params = $req->request();
        $params['ip'] = $req->ip();

        if ($fbid = $req->session('fbid')) {
            $params['facebook_id'] = $fbid;
            $params['profile_picture_preference'] = 'facebook';
        } elseif ($tid = $req->session('tid')) {
            $params['twitter_id'] = $tid;
            $params['profile_picture_preference'] = 'twitter';
        } elseif ($iid = $req->session('iid')) {
            $params['instagram_id'] = $iid;
            $params['profile_picture_preference'] = 'instagram';
        } else {
            return $res->setCode(404);
        }

        // register
        $user = User::registerUser($params);

        if ($user) {
            // login
            $this->app['auth']->login($req->request('user_email'), $req->request('user_password')[0], $req, true);

            // cleanup session
            $req->setSession([
                'fbid' => null,
                'tid' => null,
                'iid' => null, ]);

            // redirect
            $redir = ($req->request('redir')) ? $req->request('redir') : $req->cookies('redirect');

            if (!empty($redir)) {
                $req->setCookie('redirect', '', time() - 86400, '/');

                return $res->redirect($redir);
            } else {
                return $res->redirect('/profile');
            }
        }

        return $this->finishSignup($req, $res);
    }
}
