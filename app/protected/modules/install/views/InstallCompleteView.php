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
     * View for installation. Final view after everything has been installed
     */
    class InstallCompleteView extends View
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
            $progressBarImageContent = CHtml::image($imagePath, 'Progress Bar');
            $cs = Yii::app()->getClientScript();
            $cs->registerScriptFile($cs->getCoreScriptUrl() . '/jquery.min.js', CClientScript::POS_END);
            $demoDataUrl = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/installDemoData/');
            $loginUrl = Yii::app()->createUrl('zurmo/default');

            $content  = '<div class="MetadataView">';
            $content .= '<table><tr><td>';
            $content .= '<div id="demo-data-table" style="display:none;">';
            $content .= '<table><tr><td>';
            $content .= Yii::t('Default', 'The next step is to install the demo data.');
            $content .= '<br/><br/>';
            $content .= CHtml::link(Yii::t('Default', 'Click Here to install the demo data'), $demoDataUrl);
            $content .= '</td></tr></table>';
            $content .= '</div>';
            $content .= '<div id="complete-table" style="display:none;">';
            $content .= '<table><tr><td>';
            $content .= Yii::t('Default', 'Congratulations! The installation of Zurmo is complete.');
            $content .= '<br/>';
            $content .= '<br/>';
            $content .= Yii::t('Default', 'Click below to go to the login page. The username is <b>super</b>');
            $content .= '<br/><br/>';
            $content .= CHtml::link(Yii::t('Default', 'Sign in'), $loginUrl);
            $content .= '</td></tr></table>';
            $content .= '</div>';
            $content .= '<div id="progress-table">';
            $content .= '<table><tr><td class="progress-bar">';
            $content .= Yii::t('Default', 'Installation in progress. Please wait.');
            $content .= '<br/>';
            $content .= $progressBarImageContent;
            $content .= '<br/>';
            $content .= '</td></tr></table>';
            $content .= '</div>';
            $content .= Yii::t('Default', 'Installation Output:');
            $content .= '<div id="logging-table">';
            $content .= '</div>';
            $content .= '</td></tr></table>';
            $content .= '</div>';
            return $content;
        }
    }
?>
