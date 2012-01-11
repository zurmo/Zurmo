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
class PhpUnitVersionHelper
{
    // Installed version must be equal or higher then phpUnitMinimumVersion
    public static $phpUnitMinimumVersion = '3.5';

    // Installed version must be less then phpUnitMaximumVersion
    public static $phpUnitMaximumVersion = '3.6';

    public static function checkVersion()
    {
        try
        {
            $actualVersion = PHPUnit_Runner_Version::id();

            if (version_compare($actualVersion, self::$phpUnitMinimumVersion) < 0)
            {
                echo "\n Zurmo tests are not working with PHPUnit {$actualVersion} \n";
                echo "PHPUnit version must be equal and higher then PHPUnit " . self::$phpUnitMinimumVersion . " and ";
                echo "lower then " .self::$phpUnitMaximumVersion . "\n";
                echo "Please upgrade your PHPUnit version \n\n";
                exit;
            }

            if (version_compare($actualVersion, self::$phpUnitMaximumVersion) >= 0)
            {
                echo "\n Zurmo tests are not working with PHPUnit {$actualVersion} \n";
                echo "PHPUnit version must be equal and higher then PHPUnit " . self::$phpUnitMinimumVersion . " and ";
                echo "lower then " .self::$phpUnitMaximumVersion . "\n";
                echo "Please downgrade your PHPUnit version \n\n";
                exit;
            }
            return;
        }
        catch (Exception $e)
        {
            echo "You must install PHPUnit, before running tests";
            exit;
        }

    }

    /**
     * Override to handle scenarios where the application can detect apache is installed, but is unable to resolve
     * the version.
     * @see ServiceHelper::checkServiceAndSetMessagesByMethodNameAndDisplayLabel()
     */
    protected function checkServiceAndSetMessagesByMethodNameAndDisplayLabel($methodName, $displayLabel)
    {
        assert('$this->minimumVersion != null &&
                    (is_array($this->minimumVersion) || is_string($this->minimumVersion))');
        assert('is_string($methodName)');
        assert('is_string($displayLabel)');
        $actualVersion           = null;
        $minimumVersionLabel     = $this->getMinimumVersionLabel();
        $passed                  = $this->callCheckServiceMethod($methodName, $actualVersion);
        if ($passed)
        {
            $this->message  = $displayLabel . ' ' . Yii::t('Default', 'version installed:') . ' ' . $actualVersion;
            $this->message .= ' ' .Yii::t('Default', 'Minimum version required:') . ' ' . $minimumVersionLabel;
            return true;
        }
        else
        {
            if ($actualVersion == null)
            {
                if ($_SERVER['SERVER_SOFTWARE'] == 'Apache')
                {
                    $this->checkResultedInWarning = true;
                    $this->message  = $displayLabel . ' ' .
                    Yii::t('Default', 'is installed, but the version is unknown.');
                }
                else
                {
                    $this->message  = $displayLabel . ' ' . Yii::t('Default', 'is not installed');
                }
            }
            else
            {
                $this->message  = $displayLabel . ' ' . Yii::t('Default', 'version installed:') . ' ' . $actualVersion;
            }
            $this->message .= "\n";
            $this->message .= Yii::t('Default', 'Minimum version required:') . ' ' . $minimumVersionLabel;
            return false;
        }
    }

    protected function getMinimumVersionLabel()
    {
        assert('is_array($this->minimumVersion)');
        $serverName = $_SERVER['SERVER_SOFTWARE'];
        if (strrpos($serverName, 'Microsoft-IIS') !== false && strrpos($serverName, 'Microsoft-IIS') >= 0)
        {
            return $this->minimumVersion['microsoft-iis'];
        }
        elseif (strrpos($serverName, 'Apache') !== false && strrpos($serverName, 'Apache') >= 0)
        {
            return $this->minimumVersion['apache'];
        }
    }
}
?>