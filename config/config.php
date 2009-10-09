<?php

// If sf_less_php_plugin_compile in app.yml is set to true (by default)
if (sfConfig::get('app_sf_less_php_plugin_compile', true))
{
  // Register listener to routing.load_configuration event
  $this->dispatcher->connect('routing.load_configuration', array('sfLessPhp', 'findAndCompile'));
}
