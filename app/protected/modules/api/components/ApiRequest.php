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

    class ApiRequest
    {
        const REST           = 'REST';
        const SOAP           = 'SOAP';
        const JSON_FORMAT    = 'json';
        const XML_FORMAT     = 'xml';

        protected $requestType;
        protected $paramsFormat;

        /**
         * Store params from request
         * @var array
         */
        protected $params = array();

        //To be redeclard in children classes
        public function getServiceType()
        {
        }

        //To be redeclard in children classes
        public static function getParamsFromRequest()
        {
        }

        public function init()
        {
            $this->parseRequestType();
            $this->parseParamsFormat();
        }

        public function getParams()
        {
            return $this->params;
        }

        public function setParams($params)
        {
            $this->params = $params;
        }

        public function getRequestType()
        {
            return $this->requestType;
        }

        public function setRequestType($requestType)
        {
            $this->requestType = $requestType;
        }

        public function getParamsFormat()
        {
            return $this->paramsFormat;
        }

        public function setParamsFormat($paramsFormat)
        {
            $this->paramsFormat = $paramsFormat;
        }

        protected function parseRequestType()
        {
            $reqestedUrl = Yii::app()->getRequest()->getUrl();
            if (ZurmoUrlManager::getPositionOfPathInUrl('api/rest') === 0)
            {
                $this->requestType = self::REST;
            }
            elseif (ZurmoUrlManager::getPositionOfPathInUrl('api/soap') === 0)
            {
                $this->requestType = self::SOAP;
            }
            else
            {
                $this->requestType = false;
            }
        }

        protected function parseParamsFormat()
        {
            //ToDo:This produce warnings, when running unit tests, because $_SERVER['HTTP_ACCEPT'] is not defined in cli environment
            @$this->paramsFormat = (strpos($_SERVER['HTTP_ACCEPT'], self::JSON_FORMAT)) ? self::JSON_FORMAT : self::XML_FORMAT;
        }

        public function getSessionId()
        {
            if(isset($_SERVER['HTTP_ZURMO_SESSION_ID']))
            {
                return $_SERVER['HTTP_ZURMO_SESSION_ID'];
            }
            else
            {
                return false;
            }
        }

        public function getSessionToken()
        {
            if(isset($_SERVER['HTTP_ZURMO_TOKEN']))
            {
                return $_SERVER['HTTP_ZURMO_TOKEN'];
            }
            else
            {
                return false;
            }
        }

        public function getUsername()
        {
            if(isset($_SERVER['HTTP_ZURMO_AUTH_USERNAME']))
            {
                return $_SERVER['HTTP_ZURMO_AUTH_USERNAME'];
            }
            else
            {
                return false;
            }
        }

        public function getPassword()
        {
            if(isset($_SERVER['HTTP_ZURMO_AUTH_PASSWORD']))
            {
                return $_SERVER['HTTP_ZURMO_AUTH_PASSWORD'];
            }
            else
            {
                return false;
            }
        }

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
            else {
                echo "Invalid request";
                Yii::app()->end();
            }
            $this->setParams($params);
        }

        public function isApiRequest()
        {
            if (ZurmoUrlManager::getPositionOfPathInUrl('api/') === 0)
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