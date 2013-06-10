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

    class MarketingListsExternalController extends ZurmoModuleController
    {
        const TOGGLE_UNSUBSCRIBED_COOKIE_NAME = 'toggleUnsubscribed_Message';

        public function filters()
        {
            return array();
        }

        public function beforeAction($action)
        {
            Yii::app()->user->userModel = BaseActionControlUserConfigUtil::getUserToRunAs();
            return parent::beforeAction($action);
        }

        public function actionUnsubscribe($hash, $preview = 0)
        {
            $this->renderPreviewMessage($preview);
            $this->toggleUnsubscribed($hash, 0);
        }

        public function actionSubscribe($hash, $preview = 0)
        {
            $this->renderPreviewMessage($preview);
            $this->toggleUnsubscribed($hash, 1);
        }

        public function actionOptOut($hash, $preview = 0)
        {
            $this->renderPreviewMessage($preview);
            $this->toggleUnsubscribed($hash, 0, true);
        }

        public function actionManageSubscriptions($hash, $preview = 0)
        {
            /*
            $contact            = RandomDataUtil::getRandomValueFromArray(Contact::getAll());
            $personId           = $contact->getClassId('Person');
            $marketingList      = RandomDataUtil::getRandomValueFromArray(MarketingList::getAll());
            $marketingListId    = $marketingList->id;
            $model              = RandomDataUtil::getRandomValueFromArray(CampaignItem::getAll());
            $modelId            = $model->id;
            $modelType          = get_class($model);
            $hash               = EmailMessageActivityUtil::resolveHashForFooter($personId, $marketingListId, $modelId, $modelType, false);
            CVarDumper::dump($hash, 10, 1);
            exit; // Not Coding Standard
            */
            $this->renderPreviewMessage($preview);
            $contact                = null;
            $personId               = null;
            $marketingListId        = null;
            $modelId                = null;
            $modelType              = null;
            extract($this->resolveHashForMarketingListIdAndPersonIdAndContact($hash));
            $marketingLists         = MarketingList::getByUnsubscribedAndAnyoneCanSubscribe($contact->id);
            $listView               = new MarketingListsManageSubscriptionsListView($this->getId(),
                                                                                        $this->getModule()->getId(),
                                                                                        $marketingLists,
                                                                                        $personId,
                                                                                        $marketingListId,
                                                                                        $modelId,
                                                                                        $modelType);
            $view                   = new MarketingListsManageSubscriptionsPageView($this, $listView);
            echo $view->render();
        }

        protected function renderPreviewMessage($preview)
        {
            if (!$preview)
            {
                return;
            }
            $splashView         = new MarketingListsExternalActionsPreviewView();
            $view               = new MarketingListsExternalActionsPageView($this, $splashView);
            echo $view->render();
            Yii::app()->end(0, false);
        }

        protected function resolveAndValidateQueryStringHash($hash)
        {
            return EmailMessageActivityUtil::resolveQueryStringArrayForHash($hash, true, false);
        }

        protected function toggleUnsubscribed($hash, $currentUnsubscribedValue, $optOut = false)
        {
            $marketingListId        = null;
            $contact                = null;
            $personId               = null;
            $message                = null;
            $modelId                = null;
            $modelType              = null;
            $createNewActivity      = false;
            $newUnsubcribedValue   = (!$currentUnsubscribedValue);
            extract($this->resolveHashForMarketingListIdAndPersonIdAndContact($hash));
            $members                = $this->resolveMembers($currentUnsubscribedValue, $contact, $marketingListId, $optOut);
            if ($members)
            {
                $this->toggleUnsubscribedForMembers($members, $newUnsubcribedValue);
                $this->toggleOptOutForContact($contact, $optOut, $newUnsubcribedValue);
                $message = $this->resolveStatusMessage($newUnsubcribedValue, $optOut);
                $this->createActivityIfRequired($createNewActivity, $newUnsubcribedValue, $modelId, $modelType, $personId);
            }
            $this->setToggleUnsubscribedCookie($message);
            $url = Yii::app()->createUrl('/marketingLists/external/manageSubscriptions', array('hash' => $hash));
            $this->redirect($url);
        }

        protected function resolveMembers($unsubscribed, Contact $contact, $marketingListId, $optOut)
        {
            if ($optOut)
            {
                $members    = MarketingListMember::getByContactIdAndSubscribed($contact->id, $unsubscribed);
            }
            else
            {
                $members    = MarketingListMember::getByMarketingListIdContactIdAndSubscribed($marketingListId,
                                                                                                $contact->id,
                                                                                                $unsubscribed);
            }
            if (!is_array($members) && $members !== false)
            {
                $members = array($members);
            }
            if (empty($members))
            {
                $marketingList  = MarketingList::getById(intval($marketingListId));
                if ($unsubscribed === 1 && !empty($marketingList) && $marketingList->anyoneCanSubscribe &&
                                                            !$marketingList->memberAlreadyExists($contact->id))
                {
                    $members[0] = new MarketingListMember();
                    $members[0]->contact = $contact;
                    $members[0]->marketingList = $marketingList;
                }
                else
                {
                    return false;
                }
            }
            return $members;
        }

        protected function toggleUnsubscribedForMembers(& $members, $unsubscribed)
        {
            foreach ($members as $member)
            {
                $member->unsubscribed       = $unsubscribed;
                $member->unrestrictedSave();
            }
        }

        protected function toggleOptOutForContact(& $contact, $optOut, $unsubscribed)
        {
            if ($optOut && $unsubscribed)
            {
                $contact->primaryEmail->optOut = true;
            }
            elseif (!$optOut && !$unsubscribed)
            {
                $contact->primaryEmail->optOut = false;
            }
            return $contact->save();
        }

        protected function resolveStatusMessage($unsubscribed, $optOut)
        {
            $statusMessage = Zurmo::t('MarketingListsModule', 'You have been subscribed.');
            if ($unsubscribed)
            {
                if ($optOut)
                {
                    $statusMessage  = Zurmo::t('MarketingListsModule', 'You have been unsubscribed from all ' .
                        'marketing lists and opted out from all future emails.');
                }
                else
                {
                    $statusMessage = Zurmo::t('MarketingListsModule', 'You have been unsubscribed.');
                }
            }
            return $statusMessage;
        }

        protected function createActivityIfRequired($createNewActivity, $newUnsubcribedValue, $modelId, $modelType, $personId)
        {
            if (!$createNewActivity || $newUnsubcribedValue != 1)
            {
                return;
            }
            $activityClassName      = EmailMessageActivityUtil::resolveModelClassNameByModelType($modelType);
            $activityUtilClassName  = $activityClassName . 'Util';
            $type                   = $activityClassName::TYPE_UNSUBSCRIBE;
            $url                    = null;
            $sourceIP               = Yii::app()->request->userHostAddress;
            $activityData           = array('modelId'   => $modelId,
                                                'modelType' => $modelType,
                                                'personId'  => $personId,
                                                'url'       => null,
                                                'type'      => $type);
            return $activityUtilClassName::createOrUpdateActivity($activityData);
        }

        protected function setToggleUnsubscribedCookie($message)
        {
            if ($message)
            {
                $cookieName = static::TOGGLE_UNSUBSCRIBED_COOKIE_NAME;
                Yii::app()->request->cookies[$cookieName] = new CHttpCookie($cookieName, $message);
            }
        }

        protected function getContactByPersonId($personId)
        {
            $person                         = Person::getById(intval($personId));
            $contact                        = $person->castDown(array('Contact'));
            return $contact;
        }

        protected function resolveHashForMarketingListIdAndPersonIdAndContact($hash)
        {
            $queryStringArray               = $this->resolveAndValidateQueryStringHash($hash);
            $queryStringArray['contact']    = $this->getContactByPersonId($queryStringArray['personId']);
            return $queryStringArray;
        }
    }
?>