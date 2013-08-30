<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class ZurmoHttpRequest extends CHttpRequest
    {
        public $tokenEnabledRoutes = array();

        const EXTERNAL_REQUEST_TOKEN = 'externalRequestToken';

        public function validateCsrfToken($event)
        {
            if (!$this->isTrustedRequest())
            {
                return parent::validateCsrfToken($event);
            }
            else
            {
                return true;
            }
        }

        protected function isTrustedRequest()
        {
            $safeUrls       = array();
            foreach ($this->tokenEnabledRoutes as $tokenEnabledRoute)
            {
                $safeUrls[] = Yii::app()->createUrl($tokenEnabledRoute);
            }
            $requestedUrl = Yii::app()->getRequest()->getUrl();
            foreach ($safeUrls as $url)
            {
                if (strpos($requestedUrl, $url) === 0)
                {
                    $externalRequestToken = Yii::app()->getRequest()->getPost(self::EXTERNAL_REQUEST_TOKEN);
                    if ($externalRequestToken === ZURMO_TOKEN)
                    {
                        return true;
                    }
                }
            }
            return false;
        }

        /**
         * @return bool, true if external controller is requested
         * Used for Web Forms and Google Apps Contextual Gadget
         */
        public function isExternalRequest()
        {
            try
            {
                $url = Yii::app()->getRequest()->getUrl();
            }
            catch (CException $e)
            {
                $url = '';
            }
            if (strpos($url, '/external/') !== false)
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        /**
         * @return bool, true if contextive external controller is requested
         * Used for Google Apps Contextual Gadget
         */
        public function isContextiveExternalRequest()
        {
            try
            {
                $url = Yii::app()->getRequest()->getUrl();
            }
            catch (CException $e)
            {
                $url = '';
            }
            if (strpos($url, '/contextiveExternal/') !== false)
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        /**
         * @return bool, true if external or contextive external controller is requested
         * Used for Web Forms and Google Apps Contextual Gadget
         */
        public function isAnExternalRequestVariant()
        {
            if ($this->isExternalRequest())
            {
                return true;
            }
            elseif ($this->isContextiveExternalRequest())
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        /**
         * Appends host info if contextive external controller is requested
         */
        public function resolveAndGetUrl()
        {
            if ($this->isContextiveExternalRequest())
            {
                return $this->getHostInfo() . $this->getUrl();
            }
            return $this->getUrl();
        }

        /**
         * Inspects server to return what the real host info is, regardless of the what the configuration file says it is
         */
        public function getRealHostInfo()
        {
            $secure = $this->getIsSecureConnection();
            if ($secure)
            {
                $http = 'https';
            }
            else
            {
                $http = 'http';
            }
            if (isset($_SERVER['HTTP_HOST']))
            {
                return $http . '://' . $_SERVER['HTTP_HOST'];
            }

            else
            {
                $hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];
                if ($secure)
                {
                    $port = $this->getSecurePort();
                }
                else
                {
                    $port = $this->getPort();
                }
                if (($port !== 80 && !$secure) || ($port !== 443 && $secure))
                {
                    $hostInfo .= ':' . $port;
                }
                return $hostInfo;
            }
        }

        /**
         * Inspects server to return what the real script url  is, regardless of the what the configuration file says it is
         */
        public function getRealScriptUrl()
        {
            $scriptName = basename($_SERVER['SCRIPT_FILENAME']);
            if (basename($_SERVER['SCRIPT_NAME']) === $scriptName)
            {
                return $_SERVER['SCRIPT_NAME'];
            }
            elseif (basename($_SERVER['PHP_SELF']) === $scriptName)
            {
                return $_SERVER['PHP_SELF'];
            }
            elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName)
            {
                return $_SERVER['ORIG_SCRIPT_NAME'];
            }
            elseif (($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName)) !== false)
            {
                return substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $scriptName;
            }
            elseif (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0)
            {
                return str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
            }
            else
            {
                throw new CException(Yii::t('yii', 'CHttpRequest is unable to determine the entry script URL.'));
            }
        }
    }
?>