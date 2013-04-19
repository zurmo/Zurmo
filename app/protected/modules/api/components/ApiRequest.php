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
    * Handle API requests.
    */
    class ApiRequest extends CApplicationComponent
    {
        const REST           = 'REST';
        const SOAP           = 'SOAP';
        const JSON_FORMAT    = 'json';
        const XML_FORMAT     = 'xml';

        /**
         * Params format for response.
         * @var string
         */
        protected $paramsFormat;

        /**
         * Store real request class
         * @var ApiRequest
         */
        protected $requestClass;

        protected $resultClassName;

        /**
         * To be re-declared in children classes.
         */
        public static function getParamsFromRequest()
        {
        }

        /**
         * Init class.
         */
        public function init()
        {
            //$moduleId = static::callingSomeMethod();
            $moduleId = $this->getModuleId();
            $rulesClassName = ApiRulesFactory::getRulesClassNameByModuleId($moduleId);
            $requestClassName   = $rulesClassName::getRequestClassName();

            // Set request class
            $this->requestClass = new $requestClassName;
            $this->setResponseFormat($this->requestClass->getResponseFormat());
            $this->resultClassName = $rulesClassName::getResultClassName();
        }

        public function getResultClassName()
        {
            return $this->resultClassName;
        }

        public function getResponseClassName()
        {
            return $this->requestClass->getResponseClassName();
        }

        public function getParams()
        {
            $params = $this->requestClass->getParamsFromRequest();
            return $params;
        }

        public function getResponseFormat()
        {
            return $this->paramsFormat;
        }

        public function setResponseFormat($paramsFormat)
        {
            $this->paramsFormat = $paramsFormat;
        }

        /**
         * Get sessionId from HTTP headers
         */
        public function getSessionId()
        {
            return $this->requestClass->getSessionId();
        }

        /**
        * Get token from HTTP headers
        */
        public function getSessionToken()
        {
            return $this->requestClass->getSessionToken();
        }

        /**
        * Get username from HTTP headers
        */
        public function getUsername()
        {
            return $this->requestClass->getUsername();
        }

        /**
        * Get password from HTTP headers
        */
        public function getPassword()
        {
            return $this->requestClass->getPassword();
        }

        /**
        * Get language from HTTP headers
        */
        public function getLanguage()
        {
            return $this->requestClass->getLanguage();
        }

        public function isSessionTokenRequired()
        {
            return $this->requestClass->isSessionTokenRequired();
        }

        /**
         * Check if request is api request.
         * @return boolean
         */
        public static function isApiRequest()
        {
            // We need to catch exception and return false in case that this method is called via ConsoleApplication.
            try
            {
                $url = Yii::app()->getRequest()->getUrl();
            }
            catch (CException $e)
            {
                $url = '';
            }

            //if (strpos($url, '/api/') !== false || strpos($url, '/riva/') !== false)
            if (strpos($url, '/api/') !== false)
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        protected function getModuleId()
        {
            $url = Yii::app()->getRequest()->getUrl();
            if (strpos($url, '/api/') !== false)
            {
                return 'api';
            }
            elseif (strpos($url, '/riva/') !== false)
            {
                return 'riva';
            }
            else
            {
                return false;
            }
        }
    }
?>