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

    class NotificationsDefaultController extends ZurmoBaseController
    {
        public function actionIndex()
        {
            $this->actionUserList();
        }

        public function actionUserList()
        {
            $pageSize     = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'listPageSize', get_class($this->getModule()));
            $notification = new Notification(false);
            $searchAttributes = array(
                'owner'    => array('id' => Yii::app()->user->userModel->id),
                'isRead'   => '0',
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                $notification,
                Yii::app()->user->userModel->id,
                $searchAttributes
            );
            $dataProvider = RedBeanModelDataProviderUtil::makeDataProvider(
                $metadataAdapter,
                'Notification',
                'RedBeanModelDataProvider',
                'createdDateTime',
                true,
                $pageSize
            );
            $titleBarAndListView = new TitleBarAndListView(
                                        $this->getId(),
                                        $this->getModule()->getId(),
                                        $notification,
                                        'Notifications',
                                        $dataProvider,
                                        'NotificationsForUserListView',
                                        NotificationsModule::getModuleLabelByTypeAndLanguage('Plural'),
                                        array(),
                                        false);
            $view = new NotificationsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $titleBarAndListView));
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $notification = Notification::getById(intval($id));
            if (!$notification->isRead)
            {
                $notification->isRead = true;
                $notification->save();
            }
            static::resolveCanCurrentUserAccessDetailsAction($notification->owner->id);
            $view = new NotificationsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this,
                                             $this->makeTitleBarAndDetailsView($notification)));
            echo $view->render();
        }

        protected function resolveCanCurrentUserAccessDetailsAction($userId)
        {
            if (Yii::app()->user->userModel->id == $userId)
            {
                return;
            }
            $messageView = new AccessFailureView();
            $view = new AccessFailurePageView($messageView);
            echo $view->render();
            Yii::app()->end(0, false);
        }

        /**
         * Method for testing creating a simple notification for the current user.
         */
        public function actionCreateTest()
        {
            $message                    = new NotificationMessage();
            $message->textContent       = 'text content';
            $message->htmlContent       = 'html content';
            $rules                      = new SimpleDuplicateNotificationRules();
            $rules->addUser(Yii::app()->user->userModel);
            NotificationsUtil::submit($message, $rules);
            echo 'Test notification created';
        }

        public function actionRecentNotifcations()
        {
            echo NotificationsUtil::getRecentAjaxContentByUser(Yii::app()->user->userModel, 10);
            $linkHtmlOptions = array('style' => 'text-decoration:underline;');
            echo CHtml::link(Yii::t('Default', 'View All Notifications'), array('/notifications/default'), $linkHtmlOptions);
        }
    }
?>
