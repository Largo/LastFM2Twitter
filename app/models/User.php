<?php

class User extends Basemodel {

	public static function find($usernameOrEmail) {
		$user = Model::factory("User")
			->where_raw('(`email` = ? OR `username` = ?)', array($usernameOrEmail, $usernameOrEmail))
			->find_one();

		return $user;
	}

	public static function login($usernameOrEmail, $password, $rememberMe = false) {
		if ($usernameOrEmail == null or $password == null) {
			return false;
		}


		$user = User::find($usernameOrEmail);

		if ($user == null) {
			return false; // no user for this username, maybe return error code or something with a constant
		} else {
			if ($user->isInactive()) {
				return false;
			}
		}

		if ($user->authenticate($password)) {
            $user->setLoginCookie();

			return true;
		} else {
			return false;
		}
	}

    public function setLoginCookie() {
        $_SESSION['user'] = $this;
        $_SESSION['loggedin'] = true;
    }

    public static function logout() {
        setcookie("rememberMe", '', 1);
        setcookie("token", '', 1);
        $_SESSION['loggedin'] = false;
        unset($_SESSION['user']);

		$_SESSION = array();

		session_destroy();
		session_start();
	}


	/**
     * @return User
     */
	public static function getCurrentUser() {
		if(isset($_SESSION['user'])) {
			return $_SESSION['user'];
		} else {
			return null;
		}
	}

	public static function refreshCurrentUser() {
		$userId = self::getCurrentUserId();
		$user = Model::factory('User')->find_one($userId);

		if ($user != null) {
			$_SESSION['user'] = $user;
		}
	}

	public static function getCurrentUserId() {
		$user = User::getCurrentUser();

		if ($user != null) {
			return $user->id;
		} else {
			return 0;
		}
	}

	public static function isLoggedIn() {
        $user = User::getCurrentUser();
		$isLoggedIn = ($user == true);
        if ($isLoggedIn) {
            return true;
        } else {
            return self::loginByCookie(); // Try to login by cookie
        }
	}

	public static function findUsername($username) {
		return Model::factory('User')
				->where('username', $username)
				->find_one();
	}

	public static function findEmail($email) {
		return Model::factory('User')
				->where('email', $email)
				->find_one();
	}

	public function authenticate($password) {
		return password_verify($password, $this->hash) == true;
	}


	public function setPassword($password) {
		$this->hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 14]);
		$this->save();
	}

	public function isInactive() {
		return ($this->inactive == 1);
	}


	public function isCurrentUser() {
		$currentUser = self::getCurrentUser();
		return ($this->id == $currentUser->id);
	}

	public function isAdmin() {
		return ($this->admin == 1);
	}

	public function validate() {
        if (empty($this->username)) {
            return $this->getEmptyMessageFor('Username');
        }
        if($this->uniqueIndexCheckFail("username", $this->username)) {
            return $this->getUniqueIndexFailMessageFor("Username");
        }

        if (empty($this->hash)) {
            return $this->getEmptyMessageFor('Password');
        }

        // maybe validate if department exists, but this might make saving anything else impossible...
	}


	// make sure that username is not an email adress!
}
