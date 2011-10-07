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
        }

        /**
         * Initialize groupsConfig.php file that is used to store group information.
         * Latter configured groupsConfig.php is used by minify script(extensions/minscript/vendors/minify)
         * @see ExtMinScript::init()
         * @throws CException
         */
        public function initializeGroups()
        {
            $minifyDir = dirname(dirname(__FILE__)) . '/../../minscript/vendors/minify/min';
            $this->_minifyDir = $minifyDir;
            if (!extension_loaded('apc'))
            {
                $cachePath = Yii::app() -> runtimePath . '/minScript/cache';
                if (!is_dir($cachePath))
                {
                    mkdir($cachePath, 0777, true);
                } elseif (!is_writable($cachePath))
                {
                    throw new FileNotWriteableException('ZurmoExtMinScript: ' . $cachePath . ' is not writable.');
                }
            }
            if (!is_writable($minifyDir . '/groupsConfig.php'))
            {
                throw new FileNotWriteableException('ZurmoExtMinScript: ' . $minifyDir . '/groupsConfig.php is not writable.');
            }
            $this->loadGroups();
            $this -> _processGroupMap();
            $this -> _readOnlyGroupMap = true;
        }

        /**
         * Define and load groups
         */
        public function loadGroups()
        {
            $themePath = $this->getThemePath();
            $groupMap = array(
                'css' => array(
                    $themePath . '/css/screen.css',
                    $themePath . '/css/theme.css',
                    $themePath . '/css/cgrid-view.css',
                    $themePath . '/css/designer.css',
                    $themePath . '/css/form.css',
                    $themePath . '/css/jquery-ui.css',
                    $themePath . '/css/main.css',
                    $themePath . '/css/mbmenu.css',
                    $themePath . '/css/widget-juiportlets.css',
                ),
                'js' => array(
                    INSTANCE_ROOT . DIRECTORY_SEPARATOR . '/../yii/framework/web/js/source/jquery.min.js',
                    INSTANCE_ROOT . DIRECTORY_SEPARATOR . '/../yii/framework/web/js/source/jquery.ba-bbq.js',
                    INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/qtip/assets/jquery.qtip-1.0.0-rc3.min.js',
                    INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/fusionChart/jquery.fusioncharts.js',

                    INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/rssReader/jquery.zrssfeed.min.js',
                    INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/juiportlets/JuiPortlets.js',
                    INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/jnotify/jquery.jnotify.js',
                    INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected/extensions/zurmoinc/framework/widgets/assets/designer/Designer.js',
                )
            );
            $this->setGroupMap($groupMap);
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
    }
?>