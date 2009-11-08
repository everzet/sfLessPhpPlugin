<?php

/**
 * Compile LESS files
 *
 * @package     sfLessPhpPlugin
 * @subpackage  task
 * @author      ever.zet <ever.zet@gmail.com>
 */
class lessCompileTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('lessc', null, sfCommandOption::PARAMETER_NONE, 'Use lessc instead of phpless'),
      new sfCommandOption('clean', null, sfCommandOption::PARAMETER_NONE, 'Removing all compiled CSS in web/css before compile'),
      new sfCommandOption('compression', null, sfCommandOption::PARAMETER_NONE, 'Compress final CSS file')
    ));

    $this->namespace        = 'less';
    $this->name             = 'compile';
    $this->briefDescription = 'Recompiles LESS styles into web/css';
    $this->detailedDescription = <<<EOF
The [less:compile|INFO] task recompiles LESS styles and puts compiled CSS into web/css folder.
Call it with:

  [php symfony less:compile|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    // Remove old CSS files if --with-clean option specified
    if (isset($options['clean']) && $options['clean'])
    {
      foreach (sfLessPhp::findCssFiles() as $cssFile)
      {
        unlink($cssFile);
        $this->logSection('removed', str_replace(
          sfConfig::get('sf_root_dir') . '/web/css/', '', $cssFile
        ));
      }
    }

    // Inits sfLessPhp instance for compilation help
    $lessHelper = new sfLessPhp(false, isset($options['lessc']) && $options['lessc'],
                                false, isset($options['compression']) && $options['compression']);

    // Compiles LESS files
    foreach (sfLessPhp::findLessFiles() as $lessFile)
    {
      if ($lessHelper->compile($lessFile))
      {
        $this->logSection('compiled', str_replace(
          sfConfig::get('sf_root_dir') . '/data/stylesheets/', '', $lessFile
        ));
      }
    }
  }
}
