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
* TwitterCards Plugin
* 
* Adds Metas for TwitterCards management
	* Requires 'head' event to be fired into layout template !!
*/
class TwitterCards
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
				'card'        => 'summary',
				'site'        => 'flickr',
				'title'       => 'Small Island Developing States Photo Submission',
				'description' => 'View the album on Flickr.',
				'image'       => 'https://farm6.staticflickr.com/5510/14338202952_93595258ff_z.jpg',
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
		Atomik::listenEvent('head', 'TwitterCards::print_head_tags', 10);
	}

	/**
	 * Loads defaults values for OpenGraph Tags if none are proposed
	 */
	public static function onAtomikDispatchBefore() 
	{
		if( !Atomik::has('tw.card') )		 Atomik::set('tw.card', self::$config['defaults']['card']);
		if( !Atomik::has('tw.site') )		 Atomik::set('tw.site', self::$config['defaults']['site']);
		if( !Atomik::has('tw.title') )		 Atomik::set('tw.title', self::$config['defaults']['title']);
		if( !Atomik::has('tw.description') ) Atomik::set('tw.description', self::$config['defaults']['description']);
		if( !Atomik::has('tw.image') )		 Atomik::set('tw.image', self::$config['defaults']['image']);
	}

	/**
	 * Prints OpenGraph head tags HTML
	 */
	public static function print_head_tags()
	{
?>
	<!-- Twitter card -->
	<meta name="twitter:card" content="<?= Atomik::get('tw.card') ?>" />
	<meta name="twitter:site" content="<?= Atomik::get('tw.site') ?>" />
	<meta name="twitter:title" content="<?= Atomik::get('tw.title') ?>" />
	<meta name="twitter:description" content="<?= Atomik::get('tw.description') ?>" />
	<meta name="twitter:image" content="<?= Atomik::get('tw.image') ?>" />
<?php
	}
}