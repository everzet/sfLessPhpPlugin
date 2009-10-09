<?php

class lessCompileTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('with-clean', false, sfCommandOption::PARAMETER_REQUIRED, 'Removing all CSS in web/css before compile'),
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

  protected function execute($arguments = array(), $options = array())
  {
    if ('true' === $options['with-clean'])
    {
      $cssFiles = sfLessPhp::findCssFiles();
      foreach ($cssFiles as $cssFile)
      {
        unlink($cssFile);
        $this->logSection('removed', str_replace(sfLessPhp::getLessPath() . '/', '', $cssFile));
      }
    }
    $lessFiles = sfLessPhp::findLessFiles();
    foreach ($lessFiles as $lessFile)
    {
      if (sfLessPhp::compile($lessFile))
      {
        $this->logSection('compiled', str_replace(sfLessPhp::getLessPath() . '/', '', $lessFile));
      }
    }
  }
}
