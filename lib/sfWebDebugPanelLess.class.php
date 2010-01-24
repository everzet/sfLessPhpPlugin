<?php

/*
 * This file is part of the sfLessPhpPlugin.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelLess implements LESS web debug panel.
 *
 * @package    sfLessPhpPlugin
 * @subpackage debug
 * @author     Konstantin Kudryashov <ever.zet@gmail.com>
 * @version    1.3.2
 */
class sfWebDebugPanelLess extends sfWebDebugPanel
{
  /**
   * Array of stylesheets
   *
   * @var array
   **/
  protected static $stylesheets = array();

  /**
   * Listens to LoadDebugWebPanel event & adds this panel to the Web Debug toolbar
   *
   * @param sfEvent $event
   * @return void
   */
  public static function listenToLoadDebugWebPanelEvent(sfEvent $event)
  {
    $event->getSubject()->setPanel(
      'documentation',
      new self($event->getSubject())
    );
  }

  /**
   * Adds stylesheet info to panel
   *
   * @param string $lessFile LESS style file
   * @param string $cssFile CSS style file
   * @param integer $compileTime time in miliseconds
   * @param boolean $isCompiled is style compiled
   * @return void
   */
  public static function addStylesheetInfo($lessFile, $cssFile, $compileTime, $isCompiled)
  {
    self::$stylesheets[$lessFile] = array(
      'cssFile'     => $cssFile,
      'compileTime' => $compileTime,
      'isCompiled'  => $isCompiled
    );
  }

  /**
   * @see sfWebDebugPanel
   */
  public function getTitle()
  {
    return '<img src="/sfLessPhpPlugin/images/css_go.png" alt="LESS helper" height="16" width="16" /> less';
  }

  /**
   * @see sfWebDebugPanel
   */
  public function getPanelTitle()
  {
    return 'LESS Stylesheets';
  }

  /**
   * @see sfWebDebugPanel
   */
  public function getPanelContent()
  {
    $panel = $this->getConfigurationContent() . '<table class="sfWebDebugLogs" style="width: 300px"><tr><th>less file</th><th>css file</th><th style="text-align:center;">time (ms)</th></tr>';
    foreach (self::$stylesheets as $lessFile => $info)
    {
      $panel .= $this->getInfoContent($lessFile, $info);
    }
    $panel .= '</table>';

    return $panel;
  }

  /**
   * Returns configuration information for LESS compiler
   *
   * @return string
   */
  protected function getConfigurationContent()
  {
    $debugInfo = '<dl id="less_debug" style="display: none;">';
    $lessHelper = new sfLessPhp;
    foreach ($lessHelper->getDebugInfo() as $name => $value)
    {
      $debugInfo .= sprintf('<dt style="float:left; width: 100px"><strong>%s:</strong></dt>
      <dd>%s</dd>', $name, $value);
    }
    $debugInfo .= '</dl>';

    return sprintf(<<<EOF
      <h2>configuration %s</h2>
      %s<br/>
EOF
      ,$this->getToggler('less_debug', 'Toggle debug info')
      ,$debugInfo
    );
  }

  /**
   * Returns information row for LESS style compilation
   *
   * @param string $lessFile LESS style file
   * @param array $info info of compilation process
   * @return string
   */
  protected function getInfoContent($lessFile, $info)
  {
    return sprintf(<<<EOF
      <tr style="%s">
        <td class="sfWebDebugLogType">%s</td>
        <td class="sfWebDebugLogType">%s</td>
        <td class="sfWebDebugLogNumber" style="text-align:center;">%.2f</td>
      </tr>
EOF
      ,($info['isCompiled'] ? 'background-color:#a1d18d;' : '')
      ,$this->formatFileLink($lessFile, 1, str_replace(sfLessPhp::getLessPaths(), '', $lessFile))
      ,str_replace(sfLessPhp::getCssPaths(), '', $info['cssFile'])
      ,($info['isCompiled'] ? $info['compileTime'] * 1000 : 0)
    );
  }
}