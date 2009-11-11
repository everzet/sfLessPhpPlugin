<?php

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
   * Do we need to check dates before compile
   *
   * @var boolean
   */
  protected $checkDates     = true;

  /**
   * Do we use ruby LESSC compiler
   *
   * @var boolean
   */
  protected $useLessc       = false;

  /**
   * Do we use LESSC GROWL notifications
   *
   * @var boolean
   */
  protected $useGrowl       = false;

  /**
   * Do we need compression for CSS files
   *
   * @var boolean
   */
  protected $useCompression = false;

  /**
   * Constructor
   *
   * @param boolean $checkDates Do we need to check dates before compile
   * @param boolean $useLessc Do we use ruby LESSC compiler
   * @param boolean $useGrowl Do we use LESSC GROWL notifications
   * @param boolean $useCompression Do we need compression for CSS files
   */
  public function __construct($checkDates = true, $useLessc = false,
                              $useGrowl = false, $useCompression = false)
  {
    $this->setIsCheckDates($checkDates);
    $this->setIsUseLessc($useLessc);
    $this->setIsUseGrowl($useGrowl);
    $this->setIsUseCompression($useCompression);
  }

  /**
   * Do we need to check dates before compile
   *
   * @return boolean
   */
  public function isCheckDates()
  {
    return sfConfig::get('app_sf_less_php_plugin_check_dates', $this->checkDates);
  }

  /**
   * Set need of check dates before compile
   *
   * @param boolean $checkDates Do we need to check dates before compile
   */
  public function setIsCheckDates($checkDates)
  {
    $this->checkDates = $checkDates;
  }

  /**
   * Do we use ruby LESSC compiler
   *
   * @return boolean
   */
  public function isUseLessc()
  {
    return sfConfig::get('app_sf_less_php_plugin_use_lessc', $this->useLessc);
  }

  /**
   * Set need of use ruby LESSC compiler
   *
   * @param boolean $useLessc Do we use ruby LESSC compiler
   */
  public function setIsUseLessc($useLessc)
  {
    $this->useLessc = $useLessc;
  }

  /**
   * Do we use LESSC GROWL notifications
   *
   * @return boolean
   */
  public function isUseGrowl()
  {
    return sfConfig::get('app_sf_less_php_plugin_use_growl', $this->useGrowl);
  }

  /**
   * Set need of LESSC GROWL notifications
   *
   * @param boolean $useGrowl Do we use LESSC GROWL notifications
   */
  public function setIsUseGrowl($useGrowl)
  {
    $this->useGrowl = $useGrowl;
  }

  /**
   * Do we need compression for CSS files
   *
   * @return boolean
   */
  public function isUseCompression()
  {
    return sfConfig::get('app_sf_less_php_plugin_use_compression', $this->useCompression);
  }

  /**
   * Set need of compression for CSS files
   *
   * @param boolean $useCompression Do we need compression for CSS files
   */
  public function setIsUseCompression($useCompression)
  {
    $this->useCompression = $useCompression;
  }

  /**
   * Returns paths to CSS files
   *
   * @return string a path to CSS files directory
   */
  static public function getCssPaths()
  {  
    return sfConfig::get('sf_web_dir') . '/css/';
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
      sfConfig::get('sf_data_dir') . '/stylesheets/'
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
   * Returns CSS file path by its LESS alternative
   *
   * @param string $lessFile LESS file path
   * @return string CSS file path
   */
  static public function getCssPathOfLess($lessFile)
  {
    return str_replace(
      array(self::getLessPaths(), '.less'),
      array(self::getCssPaths(), '.css'),
      $lessFile
    );
  }

  /**
   * Listens to the routing.load_configuration event. Finds & compiles LESS files to CSS
   *
   * @param sfEvent $event an sfEvent instance
   */
  static public function findAndCompile(sfEvent $event)
  {
    $lessHelper = new self;
    foreach (self::findLessFiles() as $lessFile)
    {
      $lessHelper->compile($lessFile);
    }
  }

  /**
   * Compiles LESS file to CSS
   *
   * @param string $lessFile a LESS file
   * @return boolean true if succesfully compiled & false in other way
   */
  public function compile($lessFile)
  {
    if ('_' !== substr(basename($lessFile), 0, 1))
    {
      // Gets CSS file path
      $cssFile = self::getCssPathOfLess($lessFile);

      // Checks if path exists & create if not
      if (!is_dir(dirname($cssFile)))
      {
        mkdir(dirname($cssFile), 0777, true);
      }

      // If we check dates - recompile only really old CSS
      if ($this->isCheckDates())
      {
        if (!is_file($cssFile) || filemtime($lessFile) > filemtime($cssFile))
        {
          return $this->callCompiler($lessFile, $cssFile);
        }
      }
      else
      {
        return $this->callCompiler($lessFile, $cssFile);
      }
    }

    return false;
  }

  /**
   * Compress CSS by removing whitespaces, tabs, newlines, etc.
   *
   * @param string $css CSS to be compressed
   * @return string compressed CSS
   */
  static protected function getCompressedCss($css)
  {
    return str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);
  }

  /**
   * Calls current LESS compiler for single file
   *
   * @param string $lessFile a LESS file
   * @param string $cssFile a CSS file
   * @return boolean true if succesfully compiled & false in other way
   */
  protected function callCompiler($lessFile, $cssFile)
  {
    // CSS out buffer
    $buffer = '';

    // Use proper compiler
    if (!$this->isUseLessc())
    {
      // Loading lessphp (http://github.com/leafo/lessphp)
      require_once dirname(__FILE__) . '/vendor/lessphp/lessc.inc.php';

      // Compile with lessphp
      $less = new lessc($lessFile);
      $buffer = $less->parse();
    }
    else
    {
      // Compile with lessc
      exec(sprintf('lessc%s "%s" "%s"', $this->isUseGrowl() ? ' -g' : '', $lessFile, $cssFile));
      $buffer = file_get_contents($cssFile);
    }

    // Compress CSS if we use compression
    if ($this->isUseCompression())
    {
      $buffer = self::getCompressedCss($buffer);
    }

    // Add compiler header to CSS & writes it to file
    file_put_contents($cssFile, self::getCssHeader() . "\n\n" . $buffer);

    return true;
  }
}
