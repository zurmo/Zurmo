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

    class LoginPageView extends ZurmoPageView
    {
        public function __construct(CController $controller, CFormModel $formModel, $extraHeaderContent = null)
        {
            assert('is_string($extraHeaderContent) || $extraHeaderContent == null');

            $loginview = new LoginView($controller, $formModel, $extraHeaderContent);
            $loginview->setCssClasses(array('clearfix', 'background-' . mt_rand(1, 6)));
            $flashMessageView = new FlashMessageView($controller);
            $gridView = new GridView(3, 1);
            $gridView->setView($flashMessageView, 0, 0);
            $gridView->setView($loginview, 1, 0);
            $gridView->setView(new FooterView(), 2, 0);
            $this->registerScripts();
            parent::__construct($gridView);
        }

        protected function getSubtitle()
        {
            return Zurmo::t('ZurmoModule', 'Sign in');
        }

        protected function registerScripts()
        {
            $this->registerUpdateFlashBarScript();
            $this->registerSessionTimeoutFlashBarScript();
        }

        protected function registerUpdateFlashBarScript()
        {
            Yii::app()->clientScript->registerScript('handleUpdateFlashBar', '
                function updateFlashBar(data, flashBarId)
                {
                    $("#" + flashBarId).jnotifyAddMessage(
                    {
                        text: data.message,
                        permanent: false,
                        showIcon: true,
                        type: data.type
                    });
                }
            ');
        }

        protected function registerSessionTimeoutFlashBarScript()
        {
            Yii::app()->clientScript->registerCoreScript('cookie');
            Yii::app()->clientScript->registerScript('handleSessionTimeoutFlashBar', '
                var notificationBarId         = "FlashMessageBar";
                var sessionTimeoutCookieName  = "' . Yii::app()->user->loginRequiredAjaxResponse . 'Cookie";
                var sessionTimeoutCookieValue = $.cookie(sessionTimeoutCookieName);
                if (sessionTimeoutCookieValue == 1)
                {
                    $.cookie(sessionTimeoutCookieName, null,
                                        {
                                            expires : -1,
                                            path:  "/"
                                        });
                    var data = {' . // Not Coding Standard
                                '   "message" : "' . Zurmo::t('ZurmoModule', 'You have been logged out due to inactivity.'). '",
                                    "type"    : "error"
                                };
                    updateFlashBar(data, notificationBarId);
                }
            ');
        }
    }
?>
