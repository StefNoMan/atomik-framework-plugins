<?php

/*
* This file is part of the MVC PLUGIN for Atomik Framework.
*
* (c) 2016 Stephan Audonnet
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

/**
* GoogleStyling Plugin
* 
* Adds Metas for Google Styling on Chrome for Android
* Requires 'head' event to be fired into layout template !!
*/
class GoogleStyling
{
	/**
	 * Tableau de configuration du plugin
	 */
	public static $config = array();

	/**
	 * Starts this class as a plugin
	 *
	 * @param array $config
	 */
	public static function start( &$config )
	{
		$config = array_merge(array(

			'defaults'       =>	array(
				'color'	=> '#ff0000',
				'image'	=> '/'
			),

		), $config);

		self::$config = &$config;

	}

	/**
	 * Listens for Layout 'head' event to print HTML tags
	 *
	 * @param string $event Event name
	 */
	public static function onAtomikStart() 
	{
		Atomik::listenEvent('head', 'GoogleStyling::print_head_tags', 10);
	}

	/**
	 * Loads defaults values for OpenGraph Tags if none are proposed
	 */
	public static function onAtomikDispatchBefore() 
	{
		if( !Atomik::has('gs.color') )	Atomik::set('gs.color', self::$config['defaults']['color']);
		if( !Atomik::has('gs.image') )	Atomik::set('gs.image', self::$config['defaults']['image']);
	}

	/**
	 * Prints OpenGraph head tags HTML
	 */
	public static function print_head_tags()
	{
		?>
		<!-- Chrome for android styling -->
		<meta name="theme-color" value="<?= Atomik::get('gs.color') ?>">
		<link rel="icon" sizes="192x192" href="<?= Atomik::get('gs.image') ?>">

		<?php
	}
}