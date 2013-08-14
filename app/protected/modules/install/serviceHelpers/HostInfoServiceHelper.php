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

    /**
     * Checks if the host info and script url match the current installation
     */
    class HostInfoServiceHelper extends ServiceHelper
    {
        protected function checkService()
        {
            $hostInfoIsIncorrect = true;
            $ScriptUrlIsCorrect  = true;
            if(Yii::app()->request->getHostInfo() != Yii::app()->request->getRealHostInfo())
            {
                $hostInfoIsIncorrect = false;
            }
            if(Yii::app()->request->getScriptUrl() != Yii::app()->request->getRealScriptUrl())
            {
                $ScriptUrlIsCorrect = false;
            }
            if ($hostInfoIsIncorrect && $ScriptUrlIsCorrect)
            {
                $this->message  = Zurmo::t('InstallModule', 'Host Info and Script Url are configured correctly.');
                return true;
            }
            else
            {
                $this->message  = Zurmo::t('InstallModule', 'Host Info/Script Url is incorrectly configured.');
                if (!$hostInfoIsIncorrect)
                {
                    $this->message .= "\n" . Zurmo::t('InstallModule', 'Configured host:  {hostInfo}',
                                                      array('{hostInfo}' => Yii::app()->request->getHostInfo()));
                    $this->message .= "\n" . Zurmo::t('InstallModule', 'Real host:  {hostInfo}',
                                                      array('{hostInfo}' => Yii::app()->request->getRealHostInfo()));
                }
                if (!$ScriptUrlIsCorrect)
                {
                    $this->message .= "\n" . Zurmo::t('InstallModule', 'Configured script url:  {scriptUrl}',
                                                      array('{scriptUrl}' => Yii::app()->request->getScriptUrl()));
                    $this->message .= "\n" . Zurmo::t('InstallModule', 'Real script url:  {scriptUrl}',
                                                      array('{scriptUrl}' => Yii::app()->request->getRealScriptUrl()));
                }
                return false;
            }
        }
    }
?>