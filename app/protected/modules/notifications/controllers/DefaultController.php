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

    class NotificationsDefaultController extends ZurmoBaseController
    {
        public function actionUserList()
        {
            $message              = new NotificationMessage();
            $message->textContent = 'sam yang (test many chars chopped)' . mt_rand(5,1000);
            $message->htmlContent = 'html sam yang' . mt_rand(5,1000);
            $message->save();

            $notification = new Notification();
            $notification->type                = 'Simple';
            $notification->owner               = Yii::app()->user->userModel;
            $notification->isRead              = false;
            $notification->notificationMessage = $message;
            $notification->save();


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
                                        NotificationsModule::getModuleLabelByTypeAndLanguage('Plural'));
            $view = new NotificationsPageView($this, $titleBarAndListView);
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $account = Account::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($account);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, strval($account), $account);
            $detailsAndRelationsView = $this->makeDetailsAndRelationsView($account, 'AccountsModule',
                                                                          'AccountDetailsAndRelationsView',
                                                                          Yii::app()->request->getRequestUri());
            $view = new AccountsPageView($this, $detailsAndRelationsView);
            echo $view->render();
        }
    }
?>
