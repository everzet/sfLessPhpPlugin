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
   * Returns paths to CSS files
   *
   * @return string a path to CSS files directory
   */
  static public function getCssPaths()
  {  
    return sfConfig::get('sf_web_dir') . '/css';
  }

  /**
   * Returns all CSS files under the CSS directory
   *
   * @return array an array of CSS files
   */
  static public function findCssFiles()
  {
    return sfFinder::type('file')
      ->exec(array('sfLessPhp', 'isCssLessCompiled'))
      ->name('*.css')
      ->in(self::getCssPaths());
  }

  /**
   * Returns header text for CSS files
   *
   * @return string a header text for CSS files
   */
  static protected function getCssHeader()
  {
    return '/* This CSS is autocompiled by LESS parser. Don\'t edit it manually. */';
  }

  /**
   * Checks if CSS file was compiled from LESS
   *
   * @param string $dir a path to file
   * @param string $entry a filename
   * @return boolean
   */
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
  static public function getLessPaths()
  {
    return sfConfig::get(
      'app_sf_less_php_plugin_path',
      sfConfig::get('sf_data_dir') . '/stylesheets'
    );
  }

  /**
   * Returns all LESS files under the LESS directories
   *
   * @return array an array of LESS files
   */
  static public function findLessFiles()
  {
    return sfFinder::type('file')
      ->name('*.less')
      ->in(self::getLessPaths());
  }

  /**
   * Listens to the routing.load_configuration event. Finds & compiles LESS files to CSS
   *
   * @param sfEvent $event an sfEvent instance
   */
  static public function findAndCompile(sfEvent $event)
  {
    foreach (self::findLessFiles() as $lessFile)
    {
      self::compile($lessFile);
    }
  }

  /**
   * Compiles LESS file to CSS
   *
   * @param string $lessFile a LESS file
   * @param boolean $checkDates do we need to check dates before compile?
   * @return boolean true if succesfully compiled & false in other way
   */
  static public function compile($lessFile, $checkDates = null, $useLessc = null)
  {
    if ('_' !== substr(basename($lessFile), 0, 1))
    {
      $useLessc = is_null($useLessc)
        ? sfConfig::get('app_sf_less_php_plugin_use_lessc', false)
        : $useLessc;
      $checkDates = is_null($checkDates)
        ? sfConfig::get('app_sf_less_php_plugin_check_dates', true)
        : $checkDates;
      $cssFile = str_replace(
        array('data/stylesheets', '.less'),
        array('web/css', '.css'),
        $lessFile
      );

      if (!is_dir(dirname($cssFile)))
      {
        mkdir(dirname($cssFile), 0777, true);
      }

      if ($checkDates)
      {
        if (!is_file($cssFile) || filemtime($lessFile) > filemtime($cssFile))
        {
          return self::callCompiler($lessFile, $cssFile, $useLessc);
        }
      }
      else
      {
        return self::callCompiler($lessFile, $cssFile, $useLessc);
      }
    }

    return false;
  }

  /**
   * Calls current LESS compiler for single file
   *
   * @param string $lessFile a LESS file
   * @param string $cssFile a CSS file
   * @return boolean true if succesfully compiled & false in other way
   */
  static protected function callCompiler($lessFile, $cssFile, $useLessc = false)
  {
    if (!$useLessc && !sfConfig::get('app_sf_less_php_plugin_use_lessc', false))
    {
      $less = new lessc($lessFile);
      file_put_contents($cssFile, self::getCssHeader() . "\n\n" . $less->parse());

      return true;
    }
    else
    {
      $command = sfConfig::get('app_sf_less_php_plugin_use_growl', false) ? 'lessc -g' : 'lessc';
      exec(sprintf('%s "%s" "%s"', $command, $lessFile, $cssFile));
      $css = file_get_contents($cssFile);
      file_put_contents($cssFile, self::getCssHeader() . "\n\n" . $css);

      return true;
    }
  }
}
