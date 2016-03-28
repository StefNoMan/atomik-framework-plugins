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
 * OpenGraph Plugin
 * 
 * Adds Metas for OpenGraph management
 * Requires 'head' AND 'html_attr' event to be fired into layout template !!
 */
class OpenGraph
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
				'title'	=> 'My Default Title',
  				'type'	=> 'article',
  				'url'	=> '/',
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
		Atomik::listenEvent('html_attr', 'OpenGraph::print_html_attr', 10);
		Atomik::listenEvent('head', 'OpenGraph::print_head_tags', 10);
    }

    /**
     * Loads defaults values for OpenGraph Tags if none are proposed
     */
    public static function onAtomikDispatchBefore() 
    {
		if( !Atomik::has('og.title') )	Atomik::set('og.title', self::$config['defaults']['title']);
		if( !Atomik::has('og.type') ) 	Atomik::set('og.type', self::$config['defaults']['type']);
		if( !Atomik::has('og.url') )	Atomik::set('og.url', self::$config['defaults']['url']);
		if( !Atomik::has('og.image') )	Atomik::set('og.image', self::$config['defaults']['image']);
    }

    /**
     * Prints OpenGraph html tag attributes
     */
    public static function print_html_attr()
    {
		echo 'xmlns:og="http://ogp.me/ns#"';
    }

    /**
     * Prints OpenGraph head tags HTML
     */
    public static function print_head_tags()
    {
?>
	<!-- Open Graph  -->
	<meta property="og:title" content="<?= Atomik::get('og.title') ?>" />
	<meta property="og:type" content="<?= Atomik::get('og.type') ?>" />
	<meta property="og:url" content="<?= Atomik::get('og.url') ?>" />
	<meta property="og:image" content="<?= Atomik::get('og.image') ?>" />

<?php
    }
}