<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    /**
     * ZurmoExtMinScript minify, combine, minify, compress, cache javascript and css files.
     */
    Yii::import('application.extensions.minscript.components.ExtMinScript');
    class ZurmoExtMinScript extends ExtMinScript
    {
        protected $themePath;

        /**
         * Used to avoid call to ExtMinScript::init() function
         * @see ExtMinScript::init()
         */
        public function init()
        {
            CApplicationComponent::init();
            $minifyDir = dirname(dirname(__FILE__)) . '/vendors/minify/min';
            $this -> _minifyDir = $minifyDir;
            if (!extension_loaded('apc'))
            {
              $cachePath = Yii::app() -> runtimePath . '/minScript/cache';
              if (!is_dir($cachePath))
              {
                  mkdir($cachePath, 0777, true);
                  chmod(Yii::app() -> runtimePath . '/minScript' , 0777);
                  chmod(Yii::app() -> runtimePath . '/minScript/cache' , 0777);
              }
              elseif (!is_writable($cachePath))
              {
                  throw new CException('ext.minScript: ' . $cachePath . ' is not writable.');
              }
            }
            $this -> _processGroupMap();
            $this -> _readOnlyGroupMap = true;
        }

        /**
         * Set path to theme directory.
         * @param string $themePath
         */
        public function setThemePath($themePath)
        {
            $this->themePath = $themePath;
        }

        /**
         * Get theme path
         */
        public function getThemePath()
        {
            return $this->themePath;
        }

        /**
         * We don't need to load data into groupsConfig.php
         * @see ExtMinScript::_processGroupMap()
         */
        protected function _processGroupMap()
        {
            $groupMap = $this->getGroupMap();
            $this -> setGroupMap($groupMap);
        }
    }
?>