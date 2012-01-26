<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
    * Handle API requests.
    */
    class ApiRequest
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
         * Store params from request.
         * @var array
         */
        protected $params = array();

        /**
         * To be redeclard in children classes.
         */
        public function getServiceType()
        {
        }

        /**
         * To be redeclard in children classes.
         */
        public static function getParamsFromRequest()
        {
        }

        /**
         * Init class.
         */
        public function init()
        {
            $this->parseResponseFormat();
        }

        public function getParams()
        {
            return $this->params;
        }

        public function setParams($params)
        {
            $this->params = $params;
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
         * Get requested response format (json or xml)
         */
        protected function parseResponseFormat()
        {
            @$this->paramsFormat = (strpos($_SERVER['HTTP_ACCEPT'], self::JSON_FORMAT)) ? self::JSON_FORMAT : self::XML_FORMAT;
        }

        /**
         * Get sessionId from HTTP headers
         */
        public function getSessionId()
        {
            if (isset($_SERVER['HTTP_ZURMO_SESSION_ID']))
            {
                return $_SERVER['HTTP_ZURMO_SESSION_ID'];
            }
            else
            {
                return false;
            }
        }

        /**
        * Get token from HTTP headers
        */
        public function getSessionToken()
        {
            if (isset($_SERVER['HTTP_ZURMO_TOKEN']))
            {
                return $_SERVER['HTTP_ZURMO_TOKEN'];
            }
            else
            {
                return false;
            }
        }

        /**
        * Get username from HTTP headers
        */
        public function getUsername()
        {
            if (isset($_SERVER['HTTP_ZURMO_AUTH_USERNAME']))
            {
                return $_SERVER['HTTP_ZURMO_AUTH_USERNAME'];
            }
            else
            {
                return false;
            }
        }

        /**
        * Get password from HTTP headers
        */
        public function getPassword()
        {
            if (isset($_SERVER['HTTP_ZURMO_AUTH_PASSWORD']))
            {
                return $_SERVER['HTTP_ZURMO_AUTH_PASSWORD'];
            }
            else
            {
                return false;
            }
        }

        /**
        * Get language from HTTP headers
        */
        public function getLanguage()
        {
            if (isset($_SERVER['HTTP_ZURMO_LANG']))
            {
                return $_SERVER['HTTP_ZURMO_LANG'];
            }
            else
            {
                return false;
            }
        }

        /**
        * Get request type from HTTP headers
        */
        public function getRequestType()
        {
            if (isset($_SERVER['HTTP_ZURMO_API_REQUEST_TYPE']))
            {
                if (strtolower($_SERVER['HTTP_ZURMO_API_REQUEST_TYPE']) == 'rest')
                {
                    return self::REST;
                }
                elseif (strtolower($_SERVER['HTTP_ZURMO_API_REQUEST_TYPE']) == 'soap')
                {
                    return self::SOAP;
                }
            }
            else
            {
                return false;
            }
        }

        /**
        * Parse params from request.
        */
        public function parseParams()
        {
            if ($this->getRequestType() == self::REST)
            {
                $params = ApiRestRequest::getParamsFromRequest();
            }
            elseif ($this->getRequestType() == self::SOAP)
            {
                $params = ApiSoapRequest::getParamsFromRequest();
            }
            else
            {
                echo Yii::t('Default', "Invalid request");
                Yii::app()->end();
            }
            $this->setParams($params);
        }

        /**
         * Check if request is api request.
         * @return boolean
         */
        public function isApiRequest()
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

            if (strpos($url, '/api/') !== false)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }
?>