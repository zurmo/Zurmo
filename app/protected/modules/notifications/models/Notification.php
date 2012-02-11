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
     * A class for creating notification models.
     */
    class Notification extends Item
    {
        public function __toString()
        {
            if ($this->type == null)
            {
                return null;
            }
            $notificationRulesClassName = $this->type . 'NotificationRules';
            if (@class_exists($notificationRulesClassName))
            {
                return $notificationRulesClassName::getDisplayName();
            }
            else
            {
                return Yii::t('Default', '(Unnamed)');
            }
        }

        /**
         * Given a type and a user, find out how many existing unread notifications exist for that user
         * and that type.
         * @param string $type
         * @param User $user
         */
        public static function getUnreadCountByTypeAndUser($type, User $user)
        {
            assert('is_string($type) && $type != ""');
            assert('$user->id > 0');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'type',
                    'operatorType'         => 'equals',
                    'value'                => $type,
                ),
                2 => array(
                    'attributeName'        => 'owner',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $user->id,
                ),
                3 => array(
                    'attributeName'        => 'isRead',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => (bool)1,
                ),
            );
            $searchAttributeData['structure'] = '1 and 2 and 3';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Notification');
            $where = RedBeanModelDataProvider::makeWhere('Notification', $searchAttributeData, $joinTablesAdapter);
            $models = self::getSubset($joinTablesAdapter, null, null, $where, null);
            return count($models);
        }

        public static function getUnreadCountByUser(User $user)
        {
            assert('$user->id > 0');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'owner',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $user->id,
                ),
                2 => array(
                    'attributeName'        => 'isRead',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => (bool)1,
                ),
            );
            $searchAttributeData['structure'] = '1 and 2';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Notification');
            $where = RedBeanModelDataProvider::makeWhere('Notification', $searchAttributeData, $joinTablesAdapter);
            $models = self::getSubset($joinTablesAdapter, null, null, $where, null);
            return count($models);
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'type',
                    'isRead',
                ),
                'relations' => array(
                    'owner' => array(RedBeanModel::HAS_ONE, 'User'),
                    'notificationMessage' => array(RedBeanModel::HAS_ONE,  'NotificationMessage'),
                ),
                'rules' => array(
                    array('type',   'required'),
                    array('type',   'type',    'type' => 'string'),
                    array('type',   'length',  'min'  => 3, 'max' => 64),
                    array('isRead', 'boolean'),
                    array('isRead',   'validateIsReadIsSet'),
                    array('owner',  'required'),
                ),
                'elements' => array(
                    'owner' => 'User',
                ),
                'defaultSortAttribute' => null,
                'noAudit' => array(
                    'type',
                    'isRead',
                    'owner'
                )
            );
            return $metadata;
        }

        public function validateIsReadIsSet()
        {
            if ($this->isRead == null)
            {
                $this->addError('isRead', Yii::t('Default', 'Is Read must be set as true or false, not null.'));
            }
        }

        public static function isTypeDeletable()
        {
            return true;
        }
    }
?>
