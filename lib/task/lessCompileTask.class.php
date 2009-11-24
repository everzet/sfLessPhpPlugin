<?php

/*
 * This file is part of the sfLessPhpPlugin.
 * (c) 2009 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * lessCompileTask compiles LESS files thru symfony cli task system.
 *
 * @package    sfLessPhpPlugin
 * @subpackage tasks
 * @author     Konstantin Kudryashov <ever.zet@gmail.com>
 * @version    1.2.3
 */
class lessCompileTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption(
        'application',  null, sfCommandOption::PARAMETER_OPTIONAL,
        'The application name', null
      ),
      new sfCommandOption(
        'env',          null, sfCommandOption::PARAMETER_REQUIRED,
        'The environment', 'prod'
      ),
      new sfCommandOption(
        'lessc',        null, sfCommandOption::PARAMETER_NONE,
        'Use lessc instead of phpless'
      ),
      new sfCommandOption(
        'clean',        null, sfCommandOption::PARAMETER_NONE,
        'Removing all compiled CSS in web/css before compile'
      ),
      new sfCommandOption(
        'compress',     null, sfCommandOption::PARAMETER_NONE,
        'Compress final CSS file'
      )
    ));

    $this->namespace            = 'less';
    $this->name                 = 'compile';
    $this->briefDescription     = 'Recompiles LESS styles into web/css';
    $this->detailedDescription  = <<<EOF
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
    // Remove old CSS files if --clean option specified
    if (isset($options['clean']) && $options['clean'])
    {
      foreach (sfLessPhp::findCssFiles() as $cssFile)
      {
        unlink($cssFile);
        $this->logSection('removed', str_replace(sfLessPhp::getCssPaths(), '', $cssFile));
      }
    }

    // Inits sfLessPhp instance for compilation help
    $lessHelper = new sfLessPhp(false, isset($options['lessc']) && $options['lessc'],
                                false, isset($options['compress']) && $options['compress']);

    // Compiles LESS files
    foreach (sfLessPhp::findLessFiles() as $lessFile)
    {
      if ($lessHelper->compile($lessFile))
      {
        $this->logSection('compiled', str_replace(sfLessPhp::getLessPaths(), '', $lessFile));
      }
    }
  }
}
