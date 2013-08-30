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

    class MarketingListsDefaultPortletController extends ZurmoPortletController
    {
        public function actionDelete($id)
        {
            $member = MarketingListMember::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($member->marketingList);
            $member->delete();
        }

        public function actionToggleUnsubscribed($id)
        {
            $member = MarketingListMember::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($member->marketingList);
            $member->unsubscribed = (bool)(!$member->unsubscribed);
            if (!$member->unrestrictedSave())
            {
                throw new FailedToSaveModelException();
            }
        }

        public function actionCountMembers($marketingListId)
        {
            $marketingList  = MarketingList::getById($marketingListId);
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($marketingList);
            $countArray = array(
                            'subscriberCount' => MarketingListMember::getCountByMarketingListIdAndUnsubscribed($marketingListId, false),
                            'unsubscriberCount' => MarketingListMember::getCountByMarketingListIdAndUnsubscribed($marketingListId, true)
                                );
            echo CJSON::encode($countArray);
        }

        public function actionSubscribeContacts($marketingListId, $id, $type, $page = 1, $subscribedCount = 0, $skippedCount = 0)
        {
            assert('is_int($id)');
            assert('$type === "contact" || $type === "report"');
            if (!in_array($type, array('contact', 'report')))
            {
                throw new NotSupportedException();
            }
            $contactIds = array($id);
            if  ($type === 'report')
            {
                $attributeName      = null;
                $pageSize           = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                      'reportResultsListPageSize', get_class($this->getModule()));
                $reportDataProvider = MarketingListMembersUtil::makeReportDataProviderAndResolveAttributeName($id, $pageSize, $attributeName);
                $contactIds         = MarketingListMembersUtil::getContactIdsByReportDataProviderAndAttributeName(
                                      $reportDataProvider, $attributeName);
                $pageCount = $reportDataProvider->getPagination()->getPageCount();
                $subscriberInformation = $this->addNewSubscribers($marketingListId, $contactIds);
                if ($pageCount == $page || $pageCount == 0)
                {
                    $subscriberInformation = array('subscribedCount' => $subscribedCount + $subscriberInformation['subscribedCount'],
                                                   'skippedCount'    => $skippedCount    + $subscriberInformation['skippedCount']);
                    $message = $this->renderCompleteMessageBySubscriberInformation($subscriberInformation);
                    echo CJSON::encode(array('message' => $message, 'type' => 'message'));
                }
                else
                {
                    $percentageComplete = (round($page / $pageCount, 2) * 100) . ' %';
                    $message            = Zurmo::t('MarketingListsModule', 'Processing: {percentageComplete} complete',
                                          array('{percentageComplete}' => $percentageComplete));
                    echo CJSON::encode(array('message'         => $message,
                                             'type'            => 'message',
                                             'nextPage'        => $page + 1,
                                             'subscribedCount' => $subscribedCount + $subscriberInformation['subscribedCount'],
                                             'skippedCount'    => $skippedCount    + $subscriberInformation['skippedCount']));
                }
            }
            else
            {
                $subscriberInformation = $this->addNewSubscribers($marketingListId, $contactIds);
                $message = $this->renderCompleteMessageBySubscriberInformation($subscriberInformation);
                echo CJSON::encode(array('message' => $message, 'type' => 'message'));
            }
        }

        protected function renderCompleteMessageBySubscriberInformation(array $subscriberInformation)
        {
            $message = Zurmo::t('MarketingListsModule', '{subscribedCount} subscribed.',
                array('{subscribedCount}' => $subscriberInformation['subscribedCount']));
            if (array_key_exists('skippedCount', $subscriberInformation) && $subscriberInformation['skippedCount'])
            {
                $message .= ' ' . Zurmo::t('MarketingListsModule', '{skippedCount} skipped, already in the list.',
                        array('{skippedCount}' => $subscriberInformation['skippedCount']));
            }
            return $message;
        }

        protected function addNewSubscribers($marketingListId, $contactIds)
        {
            $subscriberInformation = array('subscribedCount' => 0, 'skippedCount' => 0);
            $marketingList         = MarketingList::getById($marketingListId);
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($marketingList);
            foreach ($contactIds as $contactId)
            {
                if ($marketingList->addNewMember($contactId, false))
                {
                    $subscriberInformation['subscribedCount']++;
                }
                else
                {
                    $subscriberInformation['skippedCount']++;
                }
            }
            return $subscriberInformation;
        }

        /**
         * Override to support adding a contact to a marketing list.  This is currently the only type of select from related
         * model that is supported for adding a marketing list
         * @param string $modelId
         * @param string $portletId
         * @param string $uniqueLayoutId
         * @param string $relationAttributeName
         * @param string $relationModelId
         * @param string $relationModuleId
         * @param null|string $relationModelClassName
         * @throws NotSupportedException
         */
        public function actionSelectFromRelatedListSave($modelId, $portletId, $uniqueLayoutId,
                                                        $relationAttributeName, $relationModelId, $relationModuleId,
                                                        $relationModelClassName = null)
        {
            if ($relationModelClassName == null)
            {
                $relationModelClassName = Yii::app()->getModule($relationModuleId)->getPrimaryModelName();
            }
            if ($relationModelClassName != 'Contact' && $relationAttributeName != 'contact')
            {
                throw new NotSupportedException();
            }
            $relationModel          = $relationModelClassName::getById((int)$relationModelId);
            $modelClassName         = $this->getModule()->getPrimaryModelName();
            $model                  = $modelClassName::getById((int)$modelId);
            $redirectUrl            = $this->createUrl('/' . $relationModuleId . '/default/details',
                                      array('id' => $relationModelId));
            try
            {
                if (!$model->addNewMember($relationModel->id, false))
                {
                    $this->processSelectFromRelatedListSaveAlreadyConnected($model, $relationModel);
                }
            }
            catch (FailedToSaveModelException $e)
            {
                $this->processSelectFromRelatedListSaveFails($model);
            }
            $this->redirect(array('/' . $relationModuleId . '/defaultPortlet/modalRefresh',
                'id'                   => $relationModelId,
                'portletId'            => $portletId,
                'uniqueLayoutId'       => $uniqueLayoutId,
                'redirectUrl'          => $redirectUrl,
                'portletParams'        => array(  'relationModuleId' => $relationModuleId,
                    'relationModelId'  => $relationModelId),
                'portletsAreRemovable' => false));
        }

        protected function processSelectFromRelatedListSaveAlreadyConnected(RedBeanModel $model, Contact $contact = null)
        {
            if ($contact == null)
            {
                throw new NotSupportedException();
            }
            echo CJSON::encode(array('message' => Zurmo::t('MarketingListsModule', '{contactString} is already subscribed to {modelString}.',
                                                  array('{modelString}' => strval($model), '{contactString}' => strval($contact))),
                                                        'messageType'   => 'message'));
            Yii::app()->end(0, false);
        }
    }
?>
