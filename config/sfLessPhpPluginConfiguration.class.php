<?php

/*
 * This file is part of the sfLessPhpPlugin.
 * (c) 2009 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfLessPhpPluginConfiguration configures application to use LESS compiler.
 *
 * @package    sfLessPhpPlugin
 * @subpackage configuration
 * @author     Konstantin Kudryashov <ever.zet@gmail.com>
 * @version    1.3.2
 */
class sfLessPhpPluginConfiguration extends sfPluginConfiguration
{
  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    // If sf_less_php_plugin_compile in app.yml is set to true (by default)
    if (sfConfig::get('app_sf_less_php_plugin_compile', true))
    {
      // Register listener to routing.load_configuration event
      $this->dispatcher->connect(
        'context.load_factories',
        array('sfLessPhp', 'findAndCompile')
      );
    }

    // If app_sf_less_php_plugin_toolbar in app.yml is set to true (by default)
    if (sfConfig::get('sf_web_debug') && sfConfig::get('app_sf_less_php_plugin_toolbar', true))
    {
      // Add LESS toolbar to Web Debug toolbar
      $this->dispatcher->connect('debug.web.load_panels', array(
        'sfWebDebugPanelLess',
        'listenToLoadDebugWebPanelEvent'
      ));
    }
  }
}