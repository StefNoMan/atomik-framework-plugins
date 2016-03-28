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
	* GoogleAnalytics Plugin
	* 
	* Adds Metas for Google Analytics Tracking
	*/
	class GoogleAnalytics
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
					'uid'        => 'UA-53252331-1',
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
			Atomik::listenEvent('head', 'GoogleAnalytics::print_head_tags', 10);
		}

		/**
		 * Loads defaults values for OpenGraph Tags if none are proposed
		 */
		public static function onAtomikDispatchBefore() 
		{
			if( !Atomik::has('ga.uid') )	Atomik::set('ga.uid', self::$config['defaults']['uid']);
		}

		/**
		 * Prints OpenGraph head tags HTML
		 */
		public static function print_head_tags()
		{
?>
	<!-- Google Analytics -->
	<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		ga('create', '<?= Atomik::get('ga.uid') ?>', 'auto');
		ga('send', 'pageview');
	</script>
<?php
		}
	}