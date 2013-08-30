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

    class CampaignItem extends OwnedModel
    {
        public static function getModuleClassName()
        {
            return 'CampaignsModule';
        }

        /**
         * Returns the display name for the model class.
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('CampaignsModule', 'Campaign Item', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('CampaignsModule', 'Campaign Items', array(), null, $language);
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'processed',
                ),
                'relations' => array(
                    'contact'                       => array(RedBeanModel::HAS_ONE, 'Contact', RedBeanModel::NOT_OWNED),
                    'emailMessage'                  => array(RedBeanModel::HAS_ONE, 'EmailMessage'),
                    'campaignItemActivities'        => array(RedBeanModel::HAS_MANY, 'CampaignItemActivity'),
                    'campaign'                      => array(RedBeanModel::HAS_ONE, 'Campaign', RedBeanModel::NOT_OWNED),
                ),
                'rules' => array(
                    array('processed',              'boolean'),
                    array('processed',              'default', 'value' => false),
                ),
                'elements' => array(
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        /**
         * @param int $processed
         * @param null|int $pageSize
         */
        public static function getByProcessed($processed, $pageSize = null)
        {
            assert('is_int($processed)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'processed',
                    'operatorType'              => 'equals',
                    'value'                     => intval($processed),
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, $pageSize, $where, null);
        }

        /**
         * @param int $processed
         * @param null $timestamp
         * @param null|int $pageSize
         */
        public static function getByProcessedAndSendOnDateTime($processed, $timestamp = null, $pageSize = null)
        {
            if (empty($timestamp))
            {
                $timestamp = time();
            }
            $dateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($timestamp);
            assert('is_int($processed)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'processed',
                    'operatorType'              => 'equals',
                    'value'                     => intval($processed),
                ),
                2 => array(
                    'attributeName'             => 'campaign',
                    'relatedAttributeName'      => 'sendOnDateTime',
                    'operatorType'              => 'lessThan',
                    'value'                     => $dateTime,
                ),
            );
            $searchAttributeData['structure'] = '(1 and 2)';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, $pageSize, $where, null);
        }

        public static function getByProcessedAndStatusAndSendOnDateTime($processed, $status, $timestamp = null, $pageSize = null)
        {
            if (empty($timestamp))
            {
                $timestamp = time();
            }
            $dateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($timestamp);
            assert('is_int($processed)');
            assert('is_int($status)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'processed',
                    'operatorType'              => 'equals',
                    'value'                     => intval($processed),
                ),
                2 => array(
                    'attributeName'             => 'campaign',
                    'relatedAttributeName'      => 'status',
                    'operatorType'              => 'equals',
                    'value'                     => intval($status),
                ),
                3 => array(
                    'attributeName'             => 'campaign',
                    'relatedAttributeName'      => 'sendOnDateTime',
                    'operatorType'              => 'lessThan',
                    'value'                     => $dateTime,
                ),
            );
            $searchAttributeData['structure'] = '(1 and 2 and 3)';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, $pageSize, $where, null);
        }

        /**
         * @param int $processed
         * @param int $campaignId
         * @param null|int $pageSize
         */
        public static function getByProcessedAndCampaignId($processed, $campaignId, $pageSize = null)
        {
            assert('is_int($processed)');
            assert('is_int($campaignId) || is_string($campaignId)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'processed',
                    'operatorType'              => 'equals',
                    'value'                     => intval($processed),
                ),
                2 => array(
                    'attributeName'             => 'campaign',
                    'relatedAttributeName'      => 'id',
                    'operatorType'              => 'equals',
                    'value'                     => $campaignId,
                ),
            );
            $searchAttributeData['structure'] = '(1 and 2)';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, $pageSize, $where, null);
        }

        public static function registerCampaignItemsByCampaign($campaign, $contacts)
        {
            foreach ($contacts as $contact)
            {
                if (!static::addNewItem(0, $contact, $campaign))
                {
                    return false;
                }
            }
            return true;
        }

        public static function addNewItem($processed, $contact, $campaign)
        {
            $campaignItem                       = new self;
            $campaignItem->processed            = $processed;
            $campaignItem->contact              = $contact;
            $campaignItem->campaign             = $campaign;
            $saved                              = $campaignItem->unrestrictedSave();
            assert('$saved');
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            return $saved;
        }

        /**
         * Return true if the related email message in on the outbox folder
         * @return bool
         */
        public function isQueued()
        {
            if ($this->emailMessage->folder->type ==  EmailFolder::TYPE_OUTBOX)
            {
                return true;
            }
            return false;
        }

        /**
         * @return bool
         */
        public function isSkipped()
        {
            $count = CampaignItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(CampaignItemActivity::TYPE_SKIP,
                                                                                $this->id,
                                                                                $this->contact->getClassId('Person'),
                                                                                null,
                                                                                'latestDateTime',
                                                                                null,
                                                                                true);
            if ($count > 0)
            {
                return true;
            }
            return false;
        }

        /**
         * Return true if the email message has been sent
         * @return bool
         */
        public function isSent()
        {
            if ($this->emailMessage->id > 0 && $this->emailMessage->folder->type ==  EmailFolder::TYPE_SENT)
            {
                return true;
            }
            return false;
        }

        /**
         * @return bool
         */
        public function hasFailedToSend()
        {
            if ($this->emailMessage->id > 0 && $this->emailMessage->folder->type ==  EmailFolder::TYPE_OUTBOX_FAILURE)
            {
                return true;
            }
            return false;
        }

        /**
         * @return bool;
         */
        public function hasAtLeastOneOpenActivity()
        {
            $count = CampaignItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(CampaignItemActivity::TYPE_OPEN,
                                                                                $this->id,
                                                                                $this->contact->getClassId('Person'),
                                                                                null,
                                                                                'latestDateTime',
                                                                                null,
                                                                                true);
            if ($count > 0)
            {
                return true;
            }
            return false;
        }

        /**
         * @return bool;
         */
        public function hasAtLeastOneClickActivity()
        {
            $count = CampaignItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(CampaignItemActivity::TYPE_CLICK,
                                                                                $this->id,
                                                                                $this->contact->getClassId('Person'),
                                                                                null,
                                                                                'latestDateTime',
                                                                                null,
                                                                                true);
            if ($count > 0)
            {
                return true;
            }
            return false;
        }

        /**
         * @return bool;
         */
        public function hasAtLeastOneUnsubscribeActivity()
        {
             $count = CampaignItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(CampaignItemActivity::TYPE_UNSUBSCRIBE,
                                                                                 $this->id,
                                                                                 $this->contact->getClassId('Person'),
                                                                                 null,
                                                                                 'latestDateTime',
                                                                                 null,
                                                                                 true);
            if ($count > 0)
            {
                return true;
            }
            return false;
        }

        /**
         * @return bool;
         */
        public function hasAtLeastOneBounceActivity()
        {
            $count = CampaignItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(CampaignItemActivity::TYPE_BOUNCE,
                                                                                $this->id,
                                                                                $this->contact->getClassId('Person'),
                                                                                null,
                                                                                'latestDateTime',
                                                                                null,
                                                                                true);
            if ($count > 0)
            {
                return true;
            }
            return false;
        }
    }
?>