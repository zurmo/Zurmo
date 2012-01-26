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
     * View for installation. First step during the installation process.
     */
    class InstallWelcomeView extends View
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
            $nextPageUrl = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/checkSystem/');
            $content  = '<div class="MetadataView">';
            $content .= '<table><tr><td>';
            $content .= Yii::t('Default', 'Welcome to Zurmo. Before getting started, we need some information ' .
                                          'on the database. You will need to know the following items before proceeding:');
            $content .= '<br/>';
            $content .= '<br/>';
            $content .= '<ul>';
            $content .= '<li>' . Yii::t('Default', 'Database host') . '</li>';
            $content .= '<li>' . Yii::t('Default', 'Database admin username') . '</li>';
            $content .= '<li>' . Yii::t('Default', 'Database admin password') . '</li>';
            $content .= '<li>' . Yii::t('Default', 'Database name') . '</li>';
            $content .= '<li>' . Yii::t('Default', 'Database username') . '</li>';
            $content .= '<li>' . Yii::t('Default', 'Database password') . '</li>';
            $content .= '<li>' . Yii::t('Default', 'Memcache host') . '</li>';
            $content .= '<li>' . Yii::t('Default', 'Memcache port number') . '</li>';
            $content .= '</ul>';
            $content .= Yii::t('Default', 'In all likelihood, these items were supplied to you by your Web Host. ' .
                                           'If you do not have this information, then you will need to contact ' .
                                           'them before you can continue. If you\'re all ready...');
            $content .= '<br/><br/>';
            $content .= CHtml::link(Yii::t('Default', 'Click to start'), $nextPageUrl);
            $content .= '</td></tr></table>';
            $content .= '</div>';
            return $content;
        }
    }
?>
