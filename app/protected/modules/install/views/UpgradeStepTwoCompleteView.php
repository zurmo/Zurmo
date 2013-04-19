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
     * View for upgrade.
     */
    class UpgradeStepTwoCompleteView extends View
    {
        private $controlerId;

        private $moduleId;

        public function __construct($controllerId, $moduleId)
        {
            assert('is_string($controllerId) && $controllerId != ""');
            assert('is_string($moduleId) && $moduleId != ""');
            $this->controllerId = $controllerId;
            $this->moduleId     = $moduleId;
        }

        protected function renderContent()
        {
            $imagePath = Yii::app()->baseUrl . '/themes/default/images/ajax-loader.gif';
            $progressBarImageContent = ZurmoHtml::image($imagePath, 'Progress Bar');
            $cs = Yii::app()->getClientScript();
            $cs->registerScriptFile($cs->getCoreScriptUrl() . '/jquery.min.js', CClientScript::POS_END);
            $loginUrl = Yii::app()->createUrl('zurmo/default');

            $content  = '<div class="MetadataView">';
            $content .= '<table><tr><td>';
            $content .= '<div id="upgrade-step-two" style="display:none;">';
            $content .= '<table><tr><td>';
            $content .= Zurmo::t('InstallModule', 'Upgrade process is completed. Please edit perInstance.php file, and disable maintenance mode.');
            $content .= '<br/><br/>';
            $content .= ZurmoHtml::link(Zurmo::t('InstallModule', 'Click here to access index page, after you disable maintenance mode.'), $loginUrl);
            $content .= '</td></tr></table>';
            $content .= '</div>';
            $content .= '<div id="progress-table">';
            $content .= '<table><tr><td class="progress-bar">';
            $content .= Zurmo::t('InstallModule', 'Upgrade in progress. Please wait.');
            $content .= '<br/>';
            $content .= $progressBarImageContent;
            $content .= '<br/>';
            $content .= '</td></tr></table>';
            $content .= '</div>';
            $content .= Zurmo::t('InstallModule', 'Upgrade Output:');
            $content .= '<div id="logging-table">';
            $content .= '</div>';
            $content .= '</td></tr></table>';
            $content .= '</div>';
            return $content;
        }
    }
?>
