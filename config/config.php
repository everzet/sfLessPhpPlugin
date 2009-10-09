<?php

if (sfConfig::get('app_sf_less_php_plugin_compile', true))
{
  $this->dispatcher->connect('routing.load_configuration', array('sfLessPhp', 'findAndCompile'));
}
