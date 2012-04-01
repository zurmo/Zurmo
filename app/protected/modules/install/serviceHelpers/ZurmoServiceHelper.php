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
     * Checks Zurmo installed version.
     */
    class ZurmoServiceHelper extends ServiceHelper
    {
        protected $required = false;

        protected $minimumVersion = '0';

        protected function checkService()
        {
            return $this->checkServiceAndSetMessagesByMethodNameAndDisplayLabel('checkZurmo',
                                                                                Yii::t('Default', 'Zurmo Version'));
        }

        /**
        * Override to handle scenarios where the application can detect apache is installed, but is unable to resolve
        * the version.
        * @see ServiceHelper::checkServiceAndSetMessagesByMethodNameAndDisplayLabel()
        */
        protected function checkServiceMethod(& $latestStableVersion, & $actualVersion)
        {
            assert('$actualVersion != null');

            $actualVersion = $this->getVersion($actualVersion);
            $latestStableVersion = $this->getVersion($latestStableVersion);

            if (preg_match('/^\d+\.\d+$/', $actualVersion) == 1) // Not Coding Standard
            {
                $actualVersion .= '.0';
            }
            return version_compare($actualVersion, $latestStableVersion) >= 0;
        }

        protected function checkServiceAndSetMessagesByMethodNameAndDisplayLabel($methodName, $displayLabel)
        {
            assert('$this->minimumVersion != null &&
                            (is_array($this->minimumVersion) || is_string($this->minimumVersion))');
            assert('is_string($methodName)');
            assert('is_string($displayLabel)');
            $actualVersion           = VERSION;
            $latestStableVersion = ZurmoModule::getLastZurmoStableVersion();
            $this->minimumVersion = $latestStableVersion;

            $minimumVersionLabel     = $this->getMinimumVersionLabel();
            $passed                  = $this->checkServiceMethod($latestStableVersion, $actualVersion);
            if ($passed)
            {
                return true;
            }
            else
            {
                $this->message .= Yii::t('Default', 'Your ZurmoCRM software is outdated, new stable release available:') . ' ' . $latestStableVersion;
                return false;
            }
        }

        protected function getVersion($string)
        {
            $pos = strpos($string, '(');
            $version = substr ( $string , 0,  $pos - 1);
            return $version;
        }
    }
?>