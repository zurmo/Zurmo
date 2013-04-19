<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Override to handle zurmo specific requirements for the console application. Certain methods are expected
     * to be available in the application that are added here. Additional the session component is defined as some
     * code running the console application will require access to the session component even though it is not utilized
     * in the console application.
     */
    class ConsoleApplication extends CConsoleApplication
    {
        /**
         * Some console applications can use the theme engine.  An example is sending an email notification using
         * an html email template.
         * @var string
         */
        private $_theme;

        /**
         * If the application has been installed or not.
         * @var boolean
         */
        protected $installed;

        /**
         * Is application in maintenance mode or not.
         * @var boolean
         */
        protected $maintenanceMode;

        public function isApplicationInstalled()
        {
            return $this->installed;
        }

        public function isApplicationInMaintenanceMode()
        {
            return $this->maintenanceMode;
        }

        protected function registerCoreComponents()
        {
            parent::registerCoreComponents();
            $components = array(
                'session' => array(
                    'class' => 'CHttpSession',
                ),
                'themeManager' => array(
                   'class' => 'CThemeManager',
                )
            );

            $this->setComponents($components);
        }

        /**
         * Adding expected WebApplication method.
         */
        public function getSession()
        {
            return $this->getComponent('session');
        }

        /**
         * Adding expected WebApplication method.
         */
        public function findModule($moduleID)
        {
            return WebApplication::findModuleInApplication($moduleID);
        }

        /**
         * @return CThemeManager the theme manager.
         */
        public function getThemeManager()
        {
            return $this->getComponent('themeManager');
        }

        /**
         * @return CTheme the theme used currently. Null if no theme is being used.
         */
        public function getTheme()
        {
            if (is_string($this->_theme))
            {
                $this->_theme = $this->getThemeManager()->getTheme($this->_theme);
            }
            return $this->_theme;
        }

        /**
         * @param string $value the theme name
         */
        public function setTheme($value)
        {
            $this->_theme = $value;
        }

        public function createAbsoluteUrl($route, $params = array(), $schema = '', $ampersand = '&')
        {
            $url = $this->createUrl($route, $params, $ampersand);
            if (strpos($url, 'http') === 0)
            {
                return $url;
            }
            else
            {
                return Yii::app()->getRequest()->getHostInfo($schema) . $url;
            }
        }
    }
?>