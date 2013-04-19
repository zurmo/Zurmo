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
            $content .= Zurmo::t('InstallModule', 'Welcome to Zurmo. Before getting started, we need some information ' .
                                          'on the database. You will need to know the following items before proceeding:');
            $content .= '<br/>';
            $content .= '<br/>';
            $content .= '<ul>';
            $content .= '<li>' . Zurmo::t('InstallModule', 'Database host') . '</li>';
            $content .= '<li>' . Zurmo::t('InstallModule', 'Database admin username') . '</li>';
            $content .= '<li>' . Zurmo::t('InstallModule', 'Database admin password') . '</li>';
            $content .= '<li>' . Zurmo::t('InstallModule', 'Database name') . '</li>';
            $content .= '<li>' . Zurmo::t('InstallModule', 'Database username') . '</li>';
            $content .= '<li>' . Zurmo::t('InstallModule', 'Database password') . '</li>';
            $content .= '<li>' . Zurmo::t('InstallModule', 'Memcache host') . '</li>';
            $content .= '<li>' . Zurmo::t('InstallModule', 'Memcache port number') . '</li>';
            $content .= '</ul>';
            $content .= Zurmo::t('InstallModule', 'In all likelihood, these items were supplied to you by your Web Host. ' .
                                           'If you do not have this information, then you will need to contact ' .
                                           'them before you can continue. If you\'re all ready...');
            $content .= '<br/><br/>';
            $content .= ZurmoHtml::link(ZurmoHtml::wrapLabel(Zurmo::t('InstallModule', 'Click to start')),
                                        $nextPageUrl, array('class' => 'z-button'));
            $content .= '</td></tr></table>';
            $content .= '</div>';
            return $content;
        }
    }
?>
