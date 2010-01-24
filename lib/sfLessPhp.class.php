<?php

/*
 * This file is part of the sfLessPhpPlugin.
 * (c) 2009 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfLessPhp is helper class to provide LESS compiling in symfony projects.
 *
 * @package    sfLessPhpPlugin
 * @subpackage lib
 * @author     Konstantin Kudryashov <ever.zet@gmail.com>
 * @version    1.3.2
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
   * Current LESS file to be parsed. This var used to help output errors in callCompiler()
   *
   * @var string
   */
  protected $currentFile;

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
   * Returns debug info of the current state
   *
   * @return array state
   */
  public function getDebugInfo()
  {
    return array(
      'dates'       => var_export($this->isCheckDates(), true),
      'lessc'       => var_export($this->isUseLessc(), true),
      'growl'       => var_export($this->isUseGrowl(), true),
      'compress'    => var_export($this->isUseCompression(), true),
      'less'        => $this->getLessPaths(),
      'css'         => $this->getCssPaths()
    );
  }

  /**
   * Returns path with changed directory separators to unix-style (\ => /)
   *
   * @param string $path basic path
   * @return string unix-style path
   */
  public static function getSepFixedPath($path)
  {
    return str_replace(DIRECTORY_SEPARATOR, '/', $path);
  }

  /**
   * Returns relative path from the project root dir
   *
   * @param string $fullPath full path to file
   * @return string relative path from the project root
   */
  public static function getProjectRelativePath($fullPath)
  {
    return str_replace(
      self::getSepFixedPath(sfConfig::get('sf_root_dir')) . '/',
      '',
      self::getSepFixedPath($fullPath)
    );
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
    return self::getSepFixedPath(sfConfig::get('sf_web_dir')) . '/css/';
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
      self::getSepFixedPath(sfConfig::get('sf_data_dir')) . '/stylesheets/'
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
      ->discard('_*')
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
    // Start compilation timer for debug info
    $timer = sfTimerManager::getTimer('Less compilation');

    // Create new helper object & compile LESS stylesheets with it
    $lessHelper = new self;
    foreach (self::findLessFiles() as $lessFile)
    {
      $lessHelper->compile($lessFile);
    }

    // Stop timer
    $timer->addTime();
  }

  /**
   * Compiles LESS file to CSS
   *
   * @param string $lessFile a LESS file
   * @return boolean true if succesfully compiled & false in other way
   */
  public function compile($lessFile)
  {
    // Creates timer
    $timer = new sfTimer;

    // Gets CSS file path
    $cssFile = self::getCssPathOfLess($lessFile);

    // Checks if path exists & create if not
    if (!is_dir(dirname($cssFile)))
    {
      mkdir(dirname($cssFile), 0777, true);
    }

    // Is file compiled
    $isCompiled = false;

    // If we check dates - recompile only really old CSS
    if ($this->isCheckDates())
    {
      if (!is_file($cssFile) || filemtime($lessFile) > filemtime($cssFile))
      {
        $isCompiled = $this->callCompiler($lessFile, $cssFile);
      }
    }
    else
    {
      $isCompiled = $this->callCompiler($lessFile, $cssFile);
    }

    // Stops timer
    $timer->addTime();

    // Adds debug info to debug panel if on
    if (sfConfig::get('app_sf_less_php_plugin_toolbar', true))
    {
      sfWebDebugPanelLess::addStylesheetInfo($lessFile, $cssFile, $timer->getElapsedTime(), $isCompiled);
    }

    return $isCompiled;
  }

  /**
   * Compress CSS by removing whitespaces, tabs, newlines, etc.
   *
   * @param string $css CSS to be compressed
   * @return string compressed CSS
   */
  static public function getCompressedCss($css)
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
  public function callCompiler($lessFile, $cssFile)
  {
    // Setting current file. We will output this var if compiler throws error
    $this->currentFile = $lessFile;

    // Use proper compiler
    if ($this->isUseLessc())
    {
      $buffer = $this->callLesscCompiler($lessFile, $cssFile);
    }
    else
    {
      $buffer = $this->callLessphpCompiler($lessFile, $cssFile);
    }

    // Compress CSS if we use compression
    if ($this->isUseCompression())
    {
      $buffer = self::getCompressedCss($buffer);
    }

    // Add compiler header to CSS & writes it to file
    file_put_contents($cssFile, self::getCssHeader() . "\n\n" . $buffer);

    // Setting current file to null
    $this->currentFile = null;

    return true;
  }

  /**
   * Calls Lessphp compiler for LESS file
   *
   * @param string $lessFile a LESS file
   * @param string $cssFile a CSS file
   * @return string output
   */
  public function callLessphpCompiler($lessFile, $cssFile)
  {
    // Loading lessphp (http://github.com/leafo/lessphp)
    require_once dirname(__FILE__) . '/vendor/lessphp/lessc.inc.php';
    $less = new lessc($lessFile);

    // Compile with lessphp
    try
    {
      $output = $less->parse();
    }
    catch (exception $e)
    {
      $this->throwCompilerError($e->getMessage());
    }

    return $output;
  }

  /**
   * Calls lessc compiler for LESS file
   *
   * @param string $lessFile a LESS file
   * @param string $cssFile a CSS file
   * @return string output
   */
  public function callLesscCompiler($lessFile, $cssFile)
  {
    // Compile with lessc
    $fs = new sfFilesystem;
    $command = sprintf('lessc%s "%s" "%s"', 
      $this->isUseGrowl() ? ' -g' : '', $lessFile, $cssFile);

    if ('1.3.0' <= SYMFONY_VERSION)
    {
      $fs->execute($command, null, array($this, 'throwCompilerError'));
    }
    else
    {
      $fs->sh($command);
    }

    return file_get_contents($cssFile);
  }

  /**
   * Throws formatted compiler error
   *
   * @param string $line error line
   * @return void
   */
  public function throwCompilerError($line)
  {
    throw new RuntimeException(sprintf("LESS parser (%s) error in \"%s\":\n\n%s",
      $this->isUseLessc() ? 'lessc' : 'lessphp',
      $this->currentFile,
      $line
    ));
  }
}
