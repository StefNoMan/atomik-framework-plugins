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
 * AUTH CLASS
 * 
 * Permet la gestion des sessions utilisateurs
 */
class Auth
{
	/**
	 * Tableau de configuration du plugin
	 */
    public static $config = array();

	/**
	 * Variable de stockage de la connexion à la base de données
	 */
    private static $db;


    /**
     * Starts this class as a plugin
     *
     * @param array $config
     */
    public static function start( &$config )
    {
        $config = array_merge(array(

			'salt'       =>	'r^mS3FS%7-9g', // clé de salage à modifier avant mise en production // attention ! modifier cette clé pendant la production invalidera tous les passwords utilisateurs
			'encryption' => 'sha1', 		// ou 'md5'
			'ttl'        => 60 * 60,		// en secondes (= 1 heure)

        ), $config);

        self::$config = &$config;

    	self::$db = Atomik::get('db');
        
        Atomik::registerHelper('isLogged', 'Auth::isLogged');
        Atomik::registerHelper('login', 'Auth::login');
        Atomik::registerHelper('logout', 'Auth::logout');
        Atomik::registerHelper('register', 'Auth::register');
    }


    /**
     * Défini si le visiteur est loggé
     * @return true si connecté, false dans tous les autres cas
     */
    public static function isLogged()
	{
		if ($user = Atomik::get('auth.user') == null) {
			return false;
		}

		if ( !isset( $user['user_token'] ) || !isset( $user['user_id'] ) ) {
			return false;
		}

		$result = self::$db->selectOne( 'users', array( 'user_token' => $user['user_token'], 'user_id' => $user['user_id'] ) );

		if ( empty( $result )) {
			return false;
		}
		
		return true;
	}


	/**
	 * Tente une procédure de login avec un couple email/pwd
	 * @return true si réussie, false dans tous les autres cas
	 */
	public static function login( $email, $password ) 
	{
		if ( self::$config['encryption'] != 'md5' && self::$config['encryption'] != 'sha1') {
			throw new AtomikException("Invalid encryption mode", 1);
		}
		else {
			$encryption_mode = self::$config['encryption'];
		}

		$hashed_pwd = $encryption_mode( self::$config['salt'] . $password );
		$result = self::$db->selectOne( 'users', array( 'user_email' => $email, 'user_password' => $hashed_pwd ) );
		
		if ( $result !== false )
		{
			$user = array(
				'user_id' => $result['user_id'],
				'user_token' => self::_generateToken(),
				'user_prenom' => $result['user_prenom'],
			);
		
			self::$db->update(
				'users', 
				array( 
					'user_token' => $user['user_token'],
					'user_updated_at' => date('Y-m-d H:i:s')
				), 
				array('user_id' => $user['user_id'])
			);
		
			Atomik::set( 'auth.user', $user );
		
			return true;
		}
		return false;
	}


	/**
	 * Déconnecte l'utilisateur actuel
	 */
	public static function logout()
	{
		if ( Atomik::has('auth.user')) {
			Atomik::delete('auth.user');
		}
	}


	/**
	 * Génère un token unique
	 * @return string le token
	 */
	protected static function _generateToken() 
	{
		return md5( self::$config['salt'] . time() );
	}




}