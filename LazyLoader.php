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
* LazyLoading Plugin
* 
* Loads JS and CSS files on demand
*/
class LazyLoader
{
	/**
	 * Tableau de configuration du plugin
	 */
	public static $config = array();

	/**
	 * Stack of JS scripts to load
	 */
	public static $js_stack = array();

	/**
	 * Stack of CSS Stylesheets to load
	 */
	public static $css_stack = array();

	/**
	 * Starts this class as a plugin
	 *
	 * @param array $config
	 */
	public static function start( &$config )
	{
		$config = array_merge(array(

			'scripts'       =>	array(
				'jquery'	=> 'https://code.jquery.com/jquery-1.11.3.min.js',
				'script',
			),
			'stylesheets'  =>	array(
				'bootstrap'	=> array(
					'url'         => 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css',
					'integrity'   => 'sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7',
					'crossorigin' => 'anonymous',
				),
				'layout',
				'style',
			),

		), $config);

		self::$config = &$config;

		self::$css_stack = self::$config['stylesheets'];
		self::$js_stack = self::$config['scripts'];

		Atomik::registerHelper('addJs', 'LazyLoader::addJs');
		Atomik::registerHelper('addCss', 'LazyLoader::addCss');

	}

	/**
	 * Listens for Layout 'head' and 'footer' events to print HTML tags
	 *
	 * @param string $event Event name
	 */
	public static function onAtomikStart() 
	{
		Atomik::listenEvent('head', 'LazyLoader::print_css', 1);
		Atomik::listenEvent('footer', 'LazyLoader::print_js', 1);
	}


	/**
	 * Print HTML tags for JS
	 */
	public static function print_js()
	{
		foreach (self::$js_stack as $key => $js) {
			if ( !is_numeric($key) ) {
				if ( is_array($js)) {
					$crossorigin = (isset($js['crossorigin'])) ? "crossorigin=\"{$js['crossorigin']}\"" : '' ;
					$integrity   = (isset($js['integrity'])) ? "integrity=\"{$js['integrity']}\"" : '' ;

					echo "<script src=\"{$js['url']}\" {$integrity} {$crossorigin} ></script>";
				}
				else {
					echo "<script src=\"{$js}\"></script>";
				}
			}
			else {
				echo "<script src=\"". Atomik::asset('assets/js/'. $js .'.js') ."\"></script>";
			}
		}
	}


	/**
	 * Print HTML tags for CSS
	 */
	public static function print_css()
	{
		foreach (self::$css_stack as $key => $css) {
			if ( !is_numeric($key) ) {
				if ( is_array($css)) {
					$crossorigin = (isset($css['crossorigin'])) ? "crossorigin=\"{$css['crossorigin']}\"" : '' ;
					$integrity   = (isset($css['integrity'])) ? "integrity=\"{$css['integrity']}\"" : '' ;

					echo "<link rel=\"stylesheet\" href=\"{$css['url']}\" {$integrity} {$crossorigin} >";
				}
				else {
					echo "<link rel=\"stylesheet\" href=\"{$css}\">";
				}
			}
			else {
				echo "<link rel=\"stylesheet\" href=\"". Atomik::asset('assets/css/'. $css .'.css') ."\">";
			}
		}
	}


	/**
	 * Adds a Javascript script to the stack of loading scripts
	 *
	 * @param string $script Script name
	 */
	public static function addJs($script, $priority = 10) 
	{
		self::$js_stack[] = $script;
	}

	/**
	 * Adds a CSS Stylesheet to the stack of loading stylesheets
	 *
	 * @param string $stylesheet Stylesheet name
	 */
	public static function addCss($stylesheet, $priority = 10) 
	{
		self::$css_stack[] = $stylesheet;
	}

}