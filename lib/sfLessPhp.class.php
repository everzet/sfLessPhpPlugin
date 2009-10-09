<?php

// Loading lessphp (http://github.com/leafo/lessphp)
require_once dirname(__FILE__) . '/vendor/lessphp/lessc.inc.php';

/**
 * Helper class for LESS files compiling
 *
 * @package     sfLessPhpPlugin
 * @subpackage  helper
 * @author      ever.zet <ever.zet@gmail.com>
 */
class sfLessPhp
{
  /**
   * Returns path to CSS files
   *
   * @return string a path to CSS files directory
   */
  static public function getCssPath()
  {
    return sfConfig::get('sf_root_dir') . '/web/css';
  }

  /**
   * Returns all CSS files under the CSS directory
   *
   * @return array an array of CSS files
   */
  static public function findCssFiles()
  {
    return sfFinder::type('file')
      ->name('*.css')
      ->in(self::getCssPath());
  }

  /**
   * Returns path to LESS files
   *
   * @return string a path to LESS files directory
   */
  static public function getLessPath()
  {
    return sfConfig::get(
      'app_sf_less_php_plugin_path',
      sfConfig::get('sf_root_dir') . '/data/stylesheets'
    );
  }

  /**
   * Returns all LESS files under the LESS directory
   *
   * @return array an array of LESS files
   */
  static public function findLessFiles()
  {
    return sfFinder::type('file')
      ->name('*.less')
      ->in(self::getLessPath());
  }

  /**
   * Listens to the routing.load_configuration event. Finds & compiles LESS files to CSS
   *
   * @param sfEvent $event an sfEvent instance
   */
  static public function findAndCompile(sfEvent $event)
  {
    $lessFiles = self::findLessFiles();
    foreach ($lessFiles as $lessFile)
    {
      self::compile($lessFile);
    }
  }

  /**
   * Compiles LESS file to CSS
   *
   * @param string $lessFile a LESS file
   * @return boolean true if succesfully compiled & false in other way
   */
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
