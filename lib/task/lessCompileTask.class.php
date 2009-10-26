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
      new sfCommandOption('with-clean', null, sfCommandOption::PARAMETER_NONE, 'Removing all compiled CSS in web/css before compile'),
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
    if (isset($options['with-clean']) && $options['with-clean'])
    {
      foreach (sfLessPhp::findCssFiles() as $cssFile)
      {
        unlink($cssFile);
        $this->logSection('removed', str_replace(
          sfConfig::get('sf_root_dir') . '/web/css/', '', $cssFile
        ));
      }
    }

    foreach (sfLessPhp::findLessFiles() as $lessFile)
    {
      if (sfLessPhp::compile($lessFile, false, (isset($options['lessc']) && $options['lessc'])))
      {
        $this->logSection('compiled', str_replace(
          sfConfig::get('sf_root_dir') . '/data/stylesheets/', '', $lessFile
        ));
      }
    }
  }
}
