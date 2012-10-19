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

    class Browser extends CApplicationComponent
    {
        private $name;
        private $version;
        private $platform;
        private $userAgent;

        public function init()
        {
            parent::init();
            $this->detect();
        }

        protected function detect()
        {
            $userAgent = null;
            if (isset($_SERVER['HTTP_USER_AGENT']))
            {
                $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
            }
            if (preg_match('/opera/', $userAgent))
            {
                $name = 'opera';
            }
            elseif (preg_match('/chrome/', $userAgent))
            {
                $name = 'chrome';
            }
            elseif (preg_match('/apple/', $userAgent))
            {
                $name = 'safari';
            }
            elseif (preg_match('/msie/', $userAgent))
            {
                $name = 'msie';
            }
            elseif (preg_match('/mozilla/', $userAgent) && !preg_match('/compatible/', $userAgent))
            {
                $name = 'mozilla';
            }
            else
            {
                $name = 'unrecognized';
            }
            if (preg_match('/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/', $userAgent, $matches)) // Not Coding Standard
            {
                $version = $matches[1];
            }
            else
            {
                $version = 'unknown';
            }
            if (preg_match('/linux/', $userAgent))
            {
                $platform = 'linux';
            }
            elseif (preg_match('/macintosh|mac os x/', $userAgent))
            {
                $platform = 'mac';
            }
            elseif (preg_match('/windows|win32/', $userAgent))
            {
                $platform = 'windows';
            }
            else
            {
                $platform = 'unrecognized';
            }
            $this->name         = $name;
            $this->version      = $version;
            $this->platform     = $platform;
            $this->userAgent    = $userAgent;
        }

        public function getName()
        {
            return $this->name;
        }

        public function getVersion()
        {
            return $this->version;
        }

        public function getPlatform()
        {
            return $this->platform;
        }

        public function getUserAgent()
        {
            return $this->userAgent;
        }
    }
?>
