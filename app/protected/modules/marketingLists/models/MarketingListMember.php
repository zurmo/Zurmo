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

    class MarketingListMember extends OwnedModel
    {
        public static function getModuleClassName()
        {
            return 'MarketingListsModule';
        }

        /**
         * Returns the display name for plural of the model class.
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('MarketingListsModule', 'Marketing List Members', array(), null, $language);
        }

        protected static function getLabel($language = null)
        {
            return Zurmo::t('MarketingListsModule', 'Marketing List Member', array(), null, $language);
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'createdDateTime',
                    'modifiedDateTime',
                    'unsubscribed',
                ),
                'relations' => array(
                    'contact'       => array(RedBeanModel::HAS_ONE,                 'Contact'),
                    'marketingList' => array(RedBeanModel::HAS_ONE,                 'MarketingList'),
                ),
                'rules' => array(
                    array('createdDateTime',       'required'),
                    array('createdDateTime',       'type', 'type' => 'datetime'),
                    array('modifiedDateTime',      'type', 'type' => 'datetime'),
                    array('unsubscribed',          'boolean'),
                    array('unsubscribed',          'default', 'value' => false),
                ),
                'elements' => array(
                    'createdDateTime'  => 'DateTime',
                    'modifiedDateTime' => 'DateTime',
                    'unsubscribed'     => 'CheckBox',
                ),
                'defaultSortAttribute' => 'createdDateTime',
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

        public static function getCountByMarketingListIdAndUnsubscribed($marketingListId, $unsubscribed)
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'unsubscribed',
                    'operatorType'              => 'equals',
                    'value'                     => intval($unsubscribed)
                ),
                2 => array(
                    'attributeName'             => 'marketingList',
                    'relatedAttributeName'      => 'id',
                    'operatorType'              => 'equals',
                    'value'                     => $marketingListId,
                ),
            );
            $searchAttributeData['structure'] = '(1 and 2)';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where             = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getCount($joinTablesAdapter, $where, get_called_class(), true);
        }

        public function onCreated()
        {
            $this->unrestrictedSet('createdDateTime',  DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
            $this->unrestrictedSet('modifiedDateTime', DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
        }

        public function beforeSave()
        {
            if ($this->id < 0 || (isset($this->originalAttributeValues['unsubscribed']) &&
                                            $this->originalAttributeValues['unsubscribed'] != $this->unsubscribed))
            {
                $operation = Autoresponder::OPERATION_SUBSCRIBE;
                if ($this->unsubscribed)
                {
                    $operation = Autoresponder::OPERATION_UNSUBSCRIBE;
                }
                AutoresponderItem::registerAutoresponderItemsByAutoresponderOperation($operation, $this->marketingList->id, $this->contact);
            }
            return true;
        }

        public function beforeDelete()
        {
            $operation = Autoresponder::OPERATION_REMOVE;
            AutoresponderItem::registerAutoresponderItemsByAutoresponderOperation($operation, $this->marketingList->id, $this->contact);
            return true;
        }
    }
?>
