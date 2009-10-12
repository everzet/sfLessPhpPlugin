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
  static protected function getPluginsPathsFor($subdir)
  {
    $paths = array();
    $plugins = sfFinder::type('dir')
      ->maxdepth(0)
      ->discard('.*')
      ->in(sfConfig::get('sf_plugins_dir'));
    foreach ($plugins as $plugin)
    {
      $paths[] = $plugin . $subdir;
    }

    return $paths;
  }

  /**
   * Returns paths to CSS files
   *
   * @return string a path to CSS files directory
   */
  static public function getCssPaths($withPlugins = false)
  {  
    $paths = array(sfConfig::get('sf_root_dir') . '/web/css');
    if ($withPlugins)
    {
      $paths = array_merge($paths, self::getPluginsPathsFor('/web/css'));
    }

    return $paths;
  }

  /**
   * Returns all CSS files under the CSS directory
   *
   * @return array an array of CSS files
   */
  static public function findCssFiles($withPlugins = false)
  {
    return sfFinder::type('file')
      ->exec(array('sfLessPhp', 'isCssLessCompiled'))
      ->name('*.css')
      ->in(self::getCssPaths($withPlugins));
  }

  static public function getCssHeader()
  {
    return '/* This CSS is autocompiled by LESS parser. Don\'t edit it manually. */';
  }

  static public function isCssLessCompiled($dir, $entry)
  {
    $file = $dir . '/' . $entry;
    $fp = fopen( $file, 'r' );
    $line = stream_get_line($fp, 1024, "\n");
    fclose($fp);

    return (0 === strcmp($line, self::getCssHeader()));
  }

  /**
   * Returns paths to LESS files
   *
   * @return string a path to LESS files directories
   */
  static public function getLessPaths($withPlugins = false)
  {
    $paths = array(sfConfig::get(
      'app_sf_less_php_plugin_path',
      sfConfig::get('sf_root_dir') . '/data/stylesheets'
    ));
    if ($withPlugins)
    {
      $paths = array_merge($paths, self::getPluginsPathsFor('/data/stylesheets'));
    }

    return $paths;
  }

  /**
   * Returns all LESS files under the LESS directories
   *
   * @return array an array of LESS files
   */
  static public function findLessFiles($withPlugins = false)
  {
    return sfFinder::type('file')
      ->name('*.less')
      ->in(self::getLessPaths($withPlugins));
  }

  /**
   * Listens to the routing.load_configuration event. Finds & compiles LESS files to CSS
   *
   * @param sfEvent $event an sfEvent instance
   */
  static public function findAndCompile(sfEvent $event)
  {
    $lessFiles = self::findLessFiles(
      sfConfig::get('app_sf_less_php_plugin_compile_plugins', false)
    );
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
  static public function compile($lessFile, $checkDates = true)
  {
    if ('_' !== substr(basename($lessFile), 0, 1))
    {
      $cssFile = str_replace(
        array('data/stylesheets', '.less'),
        array('web/css', '.css'),
        $lessFile
      );

      if (!is_dir(dirname($cssFile)))
      {
        mkdir(dirname($cssFile), 0777, true);
      }

      if ($checkDates && sfConfig::get('app_sf_less_php_plugin_check_dates', true))
      {
        if (!is_file($cssFile) || filemtime($lessFile) > filemtime($cssFile)) {
          $less = new lessc($lessFile);
          file_put_contents($cssFile, self::getCssHeader() . "\n\n" . $less->parse());

          return true;
        }
      }
      else
      {
        $less = new lessc($lessFile);
        file_put_contents($cssFile, self::getCssHeader() . "\n\n" . $less->parse());

        return true;
      }
    }

    return false;
  }
}
