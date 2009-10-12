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
      new sfCommandOption('with-clean', null, sfCommandOption::PARAMETER_NONE, 'Removing all CSS in web/css before compile'),
      new sfCommandOption('with-plugins', null, sfCommandOption::PARAMETER_NONE, 'Including all plugins'),
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
      $cssFiles = sfLessPhp::findCssFiles(
        isset($options['with-plugins']) && $options['with-plugins']
      );
      foreach ($cssFiles as $cssFile)
      {
        unlink($cssFile);
        $this->logSection('removed', str_replace(
          array(sfConfig::get('sf_root_dir') . '/', 'plugins/', 'web/css/'),
          '',
          $cssFile)
        );
      }
    }

    $lessFiles = sfLessPhp::findLessFiles(
      isset($options['with-plugins']) && $options['with-plugins']
    );
    foreach ($lessFiles as $lessFile)
    {
      if (sfLessPhp::compile($lessFile, false))
      {
        $this->logSection('compiled', str_replace(
          array(sfConfig::get('sf_root_dir') . '/', 'plugins/', 'data/stylesheets/'),
          '',
          $lessFile
        ));
      }
    }
  }
}
