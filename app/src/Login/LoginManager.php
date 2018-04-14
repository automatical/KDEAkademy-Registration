<?php

namespace App\Login;

class LoginManager {

	private $configuration;
	private $database;
	private $override;

	public function __construct($configuration, $database, $override = false) {
		$this->configuration = $configuration;
		$this->database = $database;
		$this->override = $override;
	}

	public function login($username, $password) {
		if($override) {
			$this->createLoginCookie($this->override['dn'], $this->override['admin']);
			return true;
		}

		$ds = ldap_connect($this->configuration['ldap_host'], $this->configuration['ldap_port']);
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

		$login = ldap_bind($ds, implode("", ["uid=",$username,",",$this->configuration['base_dn']]), $password);

		if(!$login) {
			ldap_close($ds);
			return false;
		}

		$sr=ldap_search($ds, $this->configuration['base_dn'], implode("", ["uid=",$username]));
		$userDetails = ldap_get_entries($ds, $sr)[0];

		$cn = $userDetails['cn'][0];
		$email = $userDetails['mail'][0];
		$gender = $userDetails['gender'][0];
		$ircnick = $userDetails['ircnick'][0];
		$homepostaladdress = $userDetails['homepostaladdress'][0];
		$dn = $userDetails['dn'];

		$this->addOrUpdatePerson($cn, $email, $gender, $ircnick, $homepostaladdress, $dn);

		$sr=ldap_search($ds, 'ou=groups,dc=kde,dc=org', 'cn=akademy-team');
		$groupDetails = ldap_get_entries($ds, $sr)[0]['member'];
		
		$adminUser = false;
		foreach($groupDetails as $member) {
			if($member == $dn) {
				$adminUser = true;
			}
		}
		
		ldap_close($ds);

		$this->createLoginCookie($dn, $adminUser);

		return true;
	}

	public function logout() {
		setcookie('conf-registration', false, strtotime("-1 day"));
	}

	public function isLoggedIn() {
		if($this->override) {
			return true;
		}
		if(!isset($_COOKIE['conf-registration'])) {
			return false;
		}

		$cookie = $_COOKIE['conf-registration'];

		if(!$cookie) {
			return false;
		}

		$cookie = base64_decode($cookie);

		if(!$cookie) {
			return false;
		}

		$cookie = openssl_decrypt($cookie, 'AES-128-CBC', $this->configuration['salt']);

		if(!$cookie) {
			return false;
		}

		$cookie = json_decode($cookie);

		if(!$cookie || !is_object($cookie) || !property_exists($cookie, 'dn')) {
			return false;
		}

		$mapper = $this->database->mapper('Entity\User');

		$query = $mapper->where(['dn' => $cookie->dn]);

		if($query->count()) {
			return true;
		}

		return false;
	}

	public function getCurrentUser() {
		if($this->override) {
			return $this->override['dn'];
		}

		if(!isset($_COOKIE['conf-registration'])) {
			return false;
		}

		$cookie = $_COOKIE['conf-registration'];
		$cookie = base64_decode($cookie);
		$cookie = openssl_decrypt($cookie, 'AES-128-CBC', $this->configuration['salt']);
		$cookie = json_decode($cookie);
		
		return $cookie->dn;
	}

	public function isAdminUser() {
		return false;
		if($this->override) {
			return $this->override['admin'];
		}

		return true;

		if(!$this->isLoggedIn()) {
			return false;
		}

		if(!isset($_COOKIE['conf-registration'])) {
			return false;
		}

		$cookie = $_COOKIE['conf-registration'];
		$cookie = base64_decode($cookie);
		$cookie = openssl_decrypt($cookie, 'AES-128-CBC', $this->configuration['salt']);
		$cookie = json_decode($cookie);
		
		return (boolean)$cookie->admin;
	}

	private function createLoginCookie($dn, $adminUser) {
		$userobject = [ 'dn' => $dn, 'admin' => $adminUser ];
		$cookie_string = base64_encode(openssl_encrypt(json_encode($userobject), 'AES-128-CBC', $this->configuration['salt']));

		setcookie('conf-registration', $cookie_string, strtotime("+1 Year"));
	}

	private function addOrUpdatePerson($cn, $email, $gender, $ircnick, $homepostaladdress, $dn) {
		$mapper = $this->database->mapper('Entity\User');

		$query = $mapper->where(['dn' => $dn]);
		if($query->count() > 0) {
			$user = $query->first();
		} else {
			$user = $mapper->build([
			    'dn' => $dn
			]);
		}

		$user->cn = $cn;
		$user->email = $email;
		$user->gender = $gender;
		$user->ircnick = $ircnick;
		$user->homepostaladdress = $homepostaladdress;

		$mapper->save($user);
	}

}
