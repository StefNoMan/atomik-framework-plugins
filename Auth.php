<?php

/*
 * This file is part of the MVC PLUGIN for Atomik Framework.
 *
 * (c) 2015 Stephan Audonnet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Auth class
 * 
 * Allows basic user authentication
 * WARNING: 
 * _ This plugin requires Session plugin (bundled with Atomik) to be started before.
 * _ User password must be encrypted with MD5 method
 */
class Auth
{

	/**
	 * Plugin config array
	 */
	public static $config = array();


	/**
	 * Database
	 */
	private static $db;


	/**
	 * Starts this class as a plugin
	 *
	 * @param array $config
	 */
	public static function start( &$config )
	{
		// Default configuration
		$config = array_merge(array(

			// Salt key
			'auth_salt'       => ';HX +Cd>Ii9E|&A{4w[>YGt;,IXt@)FcnbxU.+-sv0m~y>k%rH!fNao}Y,--Ue$A',
			
			// Encryption method ('md5' or 'sha1')
			'encryption' => 'sha1',

			// Time to live en secondes
			'ttl'        => 60 * 60,

			// Users table name
			'users_table_name' => 'users',

			// Users table description (required fields)
			'users_table_description' => array(
				'id'              => 'user_email',
				'password'        => 'user_password',
				'username'        => 'user_prenom',
				'token'           => 'user_token',
				'last_action_time' => 'user_updated_at',
				),

			), $config);

		self::$config = &$config;

		self::$db = Atomik::get('db');
		
		Atomik::registerHelper('isLogged', 'Auth::isLogged');
		Atomik::registerHelper('login', 'Auth::login');
		Atomik::registerHelper('logout', 'Auth::logout');
	 }


	/**
	 * Tries to authenticate a user with an id (usually his email) & password
	 * @return true if success, false either
	 */
	public static function login( $id, $password ) 
	{
		$allowed_encryption_methods = array( 'md5', 'sha1' );

		if ( ! in_array( self::$config['encryption'], $allowed_encryption_methods ) ) {
			throw new AtomikException("Invalid encryption mode", 1);
		}
		else {
			$encryption_method = self::$config['encryption'];
		}

		$hashed_pwd = $encryption_method( $password );

		$query_params = array( 
			self::$config['users_table_description']['id'] => $id, 
			self::$config['users_table_description']['password'] => $hashed_pwd 
			);

		$result = self::$db->selectOne( self::$config['users_table_name'], $query_params );

		if ( $result !== false )
		{
			$user = array(
				'id' => $result[self::$config['users_table_description']['id']],
				'token' => self::_generateToken(),
				'username' => $result[self::$config['users_table_description']['username']],
				);
			
			$query_values = array( 
				self::$config['users_table_description']['token'] => $user['token'],
				self::$config['users_table_description']['last_action_time'] => date('Y-m-d H:i:s')
				);
			$query_where = array(
				self::$config['users_table_description']['id'] => $user['id']
				);

			self::$db->update( self::$config['users_table_name'], $query_values, $query_where );
			
			Atomik::set( 'session.auth', $user );
			
			return true;
		}
		return false;
	}



	/**
	 * Determines if a user is logged comparing session stored infos (token & id) with db infos
	 * @return boolean true if connected, false either
	 */
	public static function isLogged()
	{
		if ( ! Atomik::has('session.auth') ) {
			return false;
		}

		$user = Atomik::get('session.auth');

		if ( !is_array($user) || !isset( $user['token'] ) || !isset( $user['id'] ) ) {
			Atomik::delete('session.auth');
			return false;
		}
		$query_params = array( 
			self::$config['users_table_description']['token'] => $user['token'],
			self::$config['users_table_description']['id'] => $user['id']
			);

		$result = self::$db->selectOne( self::$config['users_table_name'], $query_params );

		if ( empty( $result ) ) {
			Atomik::delete('session.auth');
			return false;
		}

		$last_action_time = strtotime( $result[self::$config['users_table_description']['last_action_time']] );
		$time = time();

		if ( ( $time - $last_action_time ) > self::$config['ttl'] ) {
			Atomik::delete('session.auth');
			return false;
		}
		
		$query_values = array( 
			self::$config['users_table_description']['last_action_time'] => date('Y-m-d H:i:s')
			);
		$query_where = array(
			self::$config['users_table_description']['id'] => $user['id']
			);

		self::$db->update( self::$config['users_table_name'], $query_values, $query_where );

		return true;
	}



	/**
	 * Disconnects the user if connected
	  * @return boolean true if user was NOT connected, his session infos either
	 */
	public static function logout()
	{
		if ( Atomik::has('session.auth')) {
			return Atomik::delete('session.auth');
		}

		return true;
	}



	/**
	 * Generates a unique token
	 * @return string the token
	 */
	protected static function _generateToken() 
	{
		return md5( self::$config['auth_salt'] . time() );
	}


}
