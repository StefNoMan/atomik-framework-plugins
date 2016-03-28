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
 * SEO Plugin
 * 
 * Adds Metas for SEO management
 * Requires 'head' event to be fired into layout template !!
 */
class SEO
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

            'defaults'       => array(
                'title'       => 'My Super Title',
                'description' => 'My super description that is long enough to satisfy the great Google ...',
                'keywords'    => 'useless, but, funny',
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
        Atomik::listenEvent('head', 'SEO::print_head_tags', 1);
    }

    /**
     * Loads defaults values for SEO Tags if none are proposed
     */
    public static function onAtomikDispatchBefore() 
    {
        if( !Atomik::has('seo.title') )  Atomik::set('seo.title', self::$config['defaults']['title']);
        if( !Atomik::has('seo.description') )   Atomik::set('seo.description', self::$config['defaults']['description']);
        if( !Atomik::has('seo.keywords') )    Atomik::set('seo.keywords', self::$config['defaults']['keywords']);
    }

    /**
     * Prints SEO head tags HTML
     */
    public static function print_head_tags()
    {
?>
  <!-- SEO -->
  <title><?= Atomik::get('seo.title') ?></title>
  <meta name="description" value="<?= Atomik::get('seo.description') ?>">
  <meta name="keywords" value="<?= Atomik::get('seo.keywords') ?>">

<?php
    }
}