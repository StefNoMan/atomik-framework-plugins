<?php

/*
 * This file is part of the MVC PLUGIN for Atomik Framework.
 *
 * (c) 2015 Stephan Audonnet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Model
{
    public static $config = array();

    public static function start(&$config)
    {
        $config = array_merge(array(

        	// clé de salage à modifier avant mise en production
            'path'	=>	'\app\models\\',
        	// clé de salage à modifier avant mise en production
            'autoload'	=>	true,
            // les Models à charger
            'models' => array(),

        ), $config);

        self::$config = &$config;

        if ( self::$config['autoload'] ) {
        	self::load();
        }
    }


    public function __construct()
    {
    	self::$db = Atomik::get('db');
    }

    private static function load()
    {
    	if ( !empty( self::$config['models'] ) ) {
	    	foreach ( self::$config['models'] as $key => $value) {
	    		try {
    				require ROOT . self::$config['path'] . $value . '.php';
	    		}
	    		catch (Exception $e) {
				    var_dump( 'Exception reçue : ',  $e->getMessage(), "\n" );
				}
	    	}
    	}
    	else {
    		$files = scandir( ROOT . self::$config['path'] );
    		for ( $i = 2; $i < sizeof( $files ); $i++ ) { 
   				require ROOT . self::$config['path'] . $files[$i];
    		}
    	}
    }

}