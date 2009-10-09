<?php

require_once dirname(__FILE__) . '/vendor/lessphp/lessc.inc.php';

class sfLessPhp
{
  static public function getCssPath()
  {
    return sfConfig::get('sf_root_dir') . '/web/css';
  }

  static public function findCssFiles()
  {
    return sfFinder::type('file')
      ->name('*.css')
      ->in(self::getCssPath());
  }

  static public function getLessPath()
  {
    return sfConfig::get(
      'app_sf_less_php_plugin_path',
      sfConfig::get('sf_root_dir') . '/data/stylesheets'
    );
  }

  static public function findLessFiles()
  {
    return sfFinder::type('file')
      ->name('*.less')
      ->in(self::getLessPath());
  }

  static public function findAndCompile(sfEvent $event)
  {
    $lessFiles = self::findLessFiles();
    foreach ($lessFiles as $lessFile)
    {
      self::compile($lessFile);
    }
  }

  static public function compile($lessFile)
  {
    if ('_' !== substr(basename($lessFile), 0, 1))
    {
      $cssFile = str_replace(
        array(self::getLessPath(), '.less'),
        array(self::getCssPath(), '.css'),
        $lessFile
      );

      if (!is_dir(dirname($cssFile)))
      {
        mkdir(dirname($cssFile), 0777, true);
      }
      lessc::ccompile($lessFile, $cssFile);

      return true;
    }

    return false;
  }
}
