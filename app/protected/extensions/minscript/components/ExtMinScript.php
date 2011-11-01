<?php

/**
 * minScript Application Component.
 *
 * Takes care of converting the groupMap and generating URLs.
 *
 * @package ext.minScript.components
 * @author TeamTPG
 * @copyright Copyright &copy; 2011 TeamTPG
 * @license BSD 3-clause
 * @link http://code.teamtpg.ch/minscript
 * @version 1.0.10
 *
 * @property array $groupMap Returns the minScript groupMap.
 */
class ExtMinScript extends CApplicationComponent {

  /**
   * @var string ID of the minScript Controller as defined in the controllerMap property.
   * Defaults to "min".
   */
  public $controllerID = 'min';

  /**
   * @var string Minify root directory.
   */
  protected $_minifyDir;

  /**
   * @var boolean Whether groupMap is read-only.
   */
  protected $_readOnlyGroupMap = false;

  protected $_groupMap = array();

  /**
   * Initialize minScript Component and convert groupMap.
   * @throws CException if minScript runtime folder not writable.
   * @throws CException if groupsConfig not writable.
   */
  public function init() {
    parent::init();
    $minifyDir = dirname(dirname(__FILE__)) . '/vendors/minify/min';
    $this -> _minifyDir = $minifyDir;
    if (!extension_loaded('apc')) {
      $cachePath = Yii::app() -> runtimePath . '/minScript/cache';
      if (!is_dir($cachePath)) {
        mkdir($cachePath, 0777, true);
      } else if (!is_writable($cachePath)) {
        throw new CException('ext.minScript: ' . $cachePath . ' is not writable.');
      }
      chmod(Yii::app() -> runtimePath . '/minScript' , 0777);
      chmod(Yii::app() -> runtimePath . '/minScript/cache' , 0777);
    }
    if (!is_writable($minifyDir . '/groupsConfig.php')) {
      throw new CException('ext.minScript: ' . $minifyDir . '/groupsConfig.php is not writable.');
    }
    $this -> _processGroupMap();
    $this -> _readOnlyGroupMap = true;
  }

  /**
   * Get the minScript groupMap.
   * @return array The minScript groupMap.
   */
  public function getGroupMap() {
    return $this -> _groupMap;
  }

  /**
   * Set the minScript groupMap. This method needs to be executed before the
   * component is initialized.
   * @param array $groupMap Array containing groups with files that need to be served. Files with asterisks
   * in their filenames will be skipped and logged.
   */
  public function setGroupMap($groupMap) {
    if (!$this -> _readOnlyGroupMap) {
      $this -> _groupMap = $groupMap;
    }
  }

  /**
   * Process groupMap and generate groupsConfig
   */
  protected function _processGroupMap() {
    $groupMap = $this -> getGroupMap();
    $groupsConfig = '&lt;?php return array(';
    //Groups
    foreach ($groupMap as $group => $items) {
      if ($groupsConfig == '&lt;?php return array(') {
        $groupsConfig .= '\'' . $group . '\'=>array(';
      } else {
        $groupsConfig .= '),\'' . $group . '\'=>array(';
      }
      //Files
      foreach ($items as $index => $path) {
        $filename = basename($path);
        if (strpos($filename, '*') !== false) {
          Yii::log('No asterisks in filename, skipping file ' . $path, 'warning', 'ext.minScript');
          unset($groupMap[$group][$index]);
          continue;
        }
        $groupsConfig .= '\'' . $path . '\',';
      }
    }
    if ($groupsConfig == '&lt;?php return array(') {
      $groupsConfig .= ');';
    } else {
      $groupsConfig .= '));';
    }
    if ($this -> _compareGroupsConfig($groupsConfig)) {
      $this -> _writeGroupsConfig($groupsConfig);
    }
    $this -> setGroupMap($groupMap);
  }

  /**
   * Generate Yii's scriptMap from minScript's groupMap
   * @param string $group Group to convert to scriptMap. Defaults to all groups.
   */
  public function generateScriptMap($group = '') {
    $groupMap = $this -> getGroupMap();
    if (!empty($group)) {
      if (isset($groupMap[$group])) {
        $minScriptUrl = $this -> generateUrl($group);
        //Files
        foreach ($groupMap[$group] as $path) {
          $filename = basename($path);
          Yii::app() -> clientScript -> scriptMap[$filename] = $minScriptUrl;
        }
      }
    } else {
      //Groups
      foreach ($groupMap as $group => $items) {
        $minScriptUrl = $this -> generateUrl($group);
        //Files
        foreach ($items as $path) {
          $filename = basename($path);
          Yii::app() -> clientScript -> scriptMap[$filename] = $minScriptUrl;
        }
      }
    }
  }

  /**
   * Generate group URL to minScript Controller.
   * @param string $group The name of the group.
   * @return string URL to minScript Controller.
   */
  public function generateUrl($group) {
    $noFilemtime = 0;
    $filemtimes = array();
    $params = array();
    $groupMap = $this -> getGroupMap();
    if (isset($groupMap[$group])) {
      $params['g'] = $group;
      //Files
      foreach ($groupMap[$group] as $path) {
        $filemtime = @filemtime($path);
        if ($filemtime !== false) {
          $filemtimes[] = $filemtime;
        } else {
          Yii::log('Can\'t access ' . $path, 'error', 'ext.minScript');
          $noFilemtime += 1;
        }
      }
      if (!empty($filemtimes) && $noFilemtime < 2) {
        $params['lm'] = max($filemtimes);
      }
    }
    $minScriptUrl = Yii::app() -> createUrl($this -> controllerID . '/serve', $params);
    return $minScriptUrl;
  }

  /**
   * Compare given string with minify's groupsConfig.
   * @param string $str String to compare.
   * @return boolean True if given string differs.
   */
  protected function _compareGroupsConfig($str) {
    $groupsConfig = @file_get_contents($this -> _minifyDir . '/groupsConfig.php');
    if ($groupsConfig === false) {
      return false;
    }
    $str = str_replace('&lt;', '<', $str);
    if ($str != $groupsConfig) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Write string to minify's groupsConfig.
   * @param string $str String to write.
   */
  protected function _writeGroupsConfig($str) {
    $str = str_replace('&lt;', '<', $str);
    file_put_contents($this -> _minifyDir . '/groupsConfig.php', $str, LOCK_EX);
  }

}
