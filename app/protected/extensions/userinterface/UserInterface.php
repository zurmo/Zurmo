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
     * Application component that helps to determine are visitors are using mobile, tablet or desktop computer.
     */
    class UserInterface extends CApplicationComponent
    {
        const MOBILE                              = 'Mobile';

        const TABLET                              = 'Tablet';

        const DESKTOP                             = 'Desktop';

        const DEFAULT_USER_INTERFACE_COOKIE_NAME  = "DefaultUserInterfaceType";

        const SELECTED_USER_INTERFACE_COOKIE_NAME = "UserInterfaceType";

        protected $defaultUserInterfaceType       = null;

        protected $selectedUserInterfaceType      = null;

        public function init()
        {
            $this->resolveDefaultUserInterfaceType();
            $this->resolveSelectedUserInterfaceType();
        }

        /**
         * Get selected user interface type
         */
        public function getSelectedUserInterfaceType()
        {
            return $this->selectedUserInterfaceType;
        }

        /**
         * Get default user interface type
         */
        public function getDefaultUserInterfaceType()
        {
            return $this->defaultUserInterfaceType;
        }

        /**
         * Set default interface type, based only on user device
         * User can't change this option.
         */
        public function resolveDefaultUserInterfaceType()
        {
            if (!isset(Yii::app()->request->cookies[self::DEFAULT_USER_INTERFACE_COOKIE_NAME]))
            {
                $userInterfaceType              = $this->detectUserInterfaceType();
                Yii::app()->request->cookies[self::DEFAULT_USER_INTERFACE_COOKIE_NAME] =
                                                  new CHttpCookie(self::DEFAULT_USER_INTERFACE_COOKIE_NAME, $userInterfaceType);
                $this->defaultUserInterfaceType = $userInterfaceType;
            }
            else
            {
                $this->defaultUserInterfaceType = Yii::app()->request->cookies[self::DEFAULT_USER_INTERFACE_COOKIE_NAME]->value;
            }
        }

        /**
         * Set interface type, selected by user
         * For example if user is using mobile device, he should be able to switch to desktop interface.
         * If user is using desktop interface, there are no sense to switch to mobile interface, except is user is using
         * mobile device and selected desktop interface.
         * Same ideas are implemented tor tablet devices.
         * @param $userInterfaceType
         */
        public function resolveSelectedUserInterfaceType($userInterfaceType = null)
        {
            if (!isset(Yii::app()->request->cookies[self::SELECTED_USER_INTERFACE_COOKIE_NAME]) || isset($userInterfaceType))
            {
                if (!isset($userInterfaceType))
                {
                    $userInterfaceType           = $this->detectUserInterfaceType();
                }
                Yii::app()->request->cookies[self::SELECTED_USER_INTERFACE_COOKIE_NAME] =
                                            new CHttpCookie(self::SELECTED_USER_INTERFACE_COOKIE_NAME, $userInterfaceType);
                $this->selectedUserInterfaceType = $userInterfaceType;
            }
            else
            {
                $this->selectedUserInterfaceType = Yii::app()->request->cookies[self::SELECTED_USER_INTERFACE_COOKIE_NAME]->value;
            }
        }

        /**
         * Is user interface a mobile interface. If there is no specifically selected interface then it will default
         * to the default detected interface.
         * @return bool
         */
        public function isMobile()
        {
            return $this->selectedUserInterfaceType == self::MOBILE;
        }

        /**
         * Is user interface a tablet interface. If there is no specifically selected interface then it will default
         * to the default detected interface.
         * @return bool
         */
        public function isTablet()
        {
            return $this->selectedUserInterfaceType == self::TABLET;
        }

        /**
         * Is user interface a desktop interface. If there is no specifically selected interface then it will default
         * to the default detected interface.
         * @return bool
         */
        public function isDesktop()
        {
            return ($this->selectedUserInterfaceType != self::MOBILE && $this->selectedUserInterfaceType != self::TABLET);
        }

        /**
         * Regardless of selected interface, is the real interface of the user device mobile
         * @return bool
         */
        public function isRealInterfaceMobile()
        {
            return $this->defaultUserInterfaceType == self::MOBILE;
        }

        /**
         * Regardless of selected interface, is the real interface of the user device a tablet
         * @return bool
         */
        public function isRealInterfaceTablet()
        {
            return $this->defaultUserInterfaceType == self::TABLET;
        }

        /**
         * Regardless of selected interface, is the real interface of the user device a desktop
         * @return bool
         */
        public function isRealInterfaceDesktop()
        {
            return ($this->defaultUserInterfaceType != self::MOBILE && $this->defaultUserInterfaceType != self::TABLET);
        }

        /**
         * Determine user interface type, based on device signature.
         * @return string
         */
        protected function detectUserInterfaceType()
        {
            require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib/Mobile_Detect.php';
            $detector = new Mobile_Detect;
            if ($detector->isMobile())
            {
                $userInterfaceType = self::MOBILE;
            }
            else if ($detector->isTablet())
            {
                $userInterfaceType = self::TABLET;
            }
            else
            {
                $userInterfaceType = self::DESKTOP;
            }
            return $userInterfaceType;
        }
    }
