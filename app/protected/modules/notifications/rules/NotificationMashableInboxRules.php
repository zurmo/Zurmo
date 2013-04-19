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

    class NotificationMashableInboxRules extends MashableInboxRules
    {
        public function getUnreadCountForCurrentUser()
        {
            $searchAttributeData = $this->getMetadataFilteredByFilteredBy(MashableInboxForm::FILTERED_BY_UNREAD);
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('Notification');
            $where               = RedBeanModelDataProvider::makeWhere('Notification', $searchAttributeData, $joinTablesAdapter);
            return Notification::getCount($joinTablesAdapter, $where, null, true);
        }

        public function getModelClassName()
        {
            return 'Notification';
        }

        public function getListViewClassName()
        {
            return 'NotificationsForUserListView';
        }

        public function getZeroModelViewClassName()
        {
            return null;
        }

        public function getMachableInboxOrderByAttributeName()
        {
            return 'createdDateTime';
        }

        public function getActionViewOptions()
        {
            return array();
        }

        public function getMetadataFilteredByOption($option)
        {
            return self::getSearchAttributeData();
        }

        public function getMetadataFilteredByFilteredBy($filteredBy)
        {
            if ($filteredBy == MashableInboxForm::FILTERED_BY_UNREAD)
            {
                $searchAttributeData['clauses'] = array(
                    1 => array(
                        'attributeName'        => 'ownerHasReadLatest',
                        'operatorType'         => 'doesNotEqual',
                        'value'                => (bool)1
                    ),
                    2 => array(
                        'attributeName'        => 'ownerHasReadLatest',
                        'operatorType'         => 'isNull',
                        'value'                => null
                    ),
                    3 => array(
                        'attributeName' => 'owner',
                        'operatorType'  => 'equals',
                        'value'         => Yii::app()->user->userModel->id
                    ),
                );
                $searchAttributeData['structure'] = '1 or (2 and 3)';
                return $searchAttributeData;
            }
            else
            {
                $metadata = null;
            }
            return $metadata;
        }

        public function getModelStringContent(RedBeanModel $model)
        {
            $content = strval($model);
            if ($content != null)
            {
                $content = $content . ' ';
            }
            if ($model->notificationMessage->id > 0)
            {
                if ($model->notificationMessage->htmlContent != null)
                {
                    $contentForSpan = Yii::app()->format->raw($model->notificationMessage->htmlContent);
                }
                elseif ($model->notificationMessage->textContent != null)
                {
                    $contentForSpan = Yii::app()->format->text($model->notificationMessage->textContent);
                }
                $content .= ZurmoHtml::tag(
                                    'span',
                                    array("class" => "last-comment"),
                                    $contentForSpan
                                );
            }
            return $content;
        }

        public function getModelCreationTimeContent(RedBeanModel $model)
        {
            return MashableUtil::getTimeSinceLatestUpdate($model->createdDateTime);
        }

        public function getSearchAttributeData($searchTerm = null)
        {
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'owner',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => Yii::app()->user->userModel->id,
                ),
            );
            $searchAttributeData['structure'] = '1';
            if ($searchTerm !== null)
            {
                $searchAttributeData['clauses'][2] = array(
                        'attributeName'        => 'notificationMessage',
                        'relatedAttributeName' => 'htmlContent',
                        'operatorType'         => 'contains',
                        'value'                => $searchTerm,
                );
                $searchAttributeData['clauses'][3] = array(
                        'attributeName'        => 'notificationMessage',
                        'relatedAttributeName' => 'textContent',
                        'operatorType'         => 'contains',
                        'value'                => $searchTerm,
                );
                $searchAttributeData['structure'] .= ' and (2 or 3)';
            }
            return $searchAttributeData;
        }

        public function resolveMarkRead($modelId)
        {
            assert('$modelId > 0');
            $this->resolveChangeHasReadLatestStatus($modelId, true);
        }

        public function resolveMarkUnread($modelId)
        {
            assert('$modelId > 0');
            $this->resolveChangeHasReadLatestStatus($modelId, false);
        }

        private function resolveChangeHasReadLatestStatus($modelId, $newStatus)
        {
            $modelClassName            = $this->getModelClassName();
            $model                     = $modelClassName::getById($modelId);
            if (Yii::app()->user->userModel == $model->owner)
            {
                $model->ownerHasReadLatest = $newStatus;
            }
            $model->ownerHasReadLatest = $newStatus;
            $model->save();
        }

        public function getMassOptions()
        {
            return array(
                          'deleteSelected' => array('label' => Zurmo::t('NotificationsModule', 'Delete selected'), 'isActionForAll' => false),
                          'deleteAll'      => array('label' => Zurmo::t('NotificationsModule', 'Delete all'), 'isActionForAll' => true),
                    );
        }

        public function resolveDeleteSelected($modelId)
        {
            assert('$modelId > 0');
            $notification = Notification::GetById(intval($modelId));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($notification);
            $notification->delete();
        }

        public function resolveDeleteAll()
        {
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'owner',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => Yii::app()->user->userModel->id,
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Notification');
            $where  = RedBeanModelDataProvider::makeWhere('Notification', $searchAttributeData, $joinTablesAdapter);
            $models = Notification::getSubset($joinTablesAdapter, null, null, $where, null);
            foreach ($models as $model)
            {
                ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($model);
                $model->delete();
            }
        }

        public function hasCurrentUserReadLatest($modelId)
        {
            $modelClassName = $this->getModelClassName();
            $model          = $modelClassName::getById($modelId);
            return $model->ownerHasReadLatest;
        }
    }
?>
