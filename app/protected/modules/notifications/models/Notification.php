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
     * A class for creating notification models.
     */
    class Notification extends Item implements MashableInboxInterface
    {
        public static function getMashableInboxRulesType()
        {
            return 'Notification';
        }

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
                return Zurmo::t('NotificationsModule', '(Unnamed)');
            }
        }

        /**
         * Given a type and a user, find out how many existing notifications exist for that user
         * and that type.
         * @param string $type
         * @param User $user
         */
        public static function getCountByTypeAndUser($type, User $user)
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
            );
            $searchAttributeData['structure'] = '1 and 2';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Notification');
            $where = RedBeanModelDataProvider::makeWhere('Notification', $searchAttributeData, $joinTablesAdapter);
            $models = self::getSubset($joinTablesAdapter, null, null, $where, null);
            return count($models);
        }

        public static function getCountByUser(User $user)
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
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Notification');
            $where  = RedBeanModelDataProvider::makeWhere('Notification', $searchAttributeData, $joinTablesAdapter);
            return self::getCount($joinTablesAdapter, $where, null, true);
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'type',
                    'ownerHasReadLatest',
                ),
                'relations' => array(
                    'notificationMessage' => array(RedBeanModel::HAS_ONE,  'NotificationMessage', RedBeanModel::NOT_OWNED),
                    'owner' =>               array(RedBeanModel::HAS_ONE, 'User', RedBeanModel::NOT_OWNED,
                                                   RedBeanModel::LINK_TYPE_SPECIFIC, 'owner'),
                ),
                'rules' => array(
                    array('owner',                  'required'),
                    array('type',                   'required'),
                    array('type',                   'type',    'type' => 'string'),
                    array('type',                   'length',  'min'  => 3, 'max' => 64),
                    array('ownerHasReadLatest',     'boolean'),
                ),
                'elements' => array(
                    'owner' => 'User',
                ),
                'defaultSortAttribute' => null,
                'noAudit' => array(
                    'owner',
                    'type',
                    'ownerHasReadLatest',
                )
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getModuleClassName()
        {
            return 'NotificationsModule';
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'ownerHasReadLatest'  => Zurmo::t('NotificationsModule', 'Owner Has Read Latest',  array(), null, $language),
                    'notificationMessage' => Zurmo::t('NotificationsModule', 'Notification Message',  array(), null, $language),
                    'owner'               => Zurmo::t('ZurmoModule', 'Owner',  array(), null, $language),
                    'type'                => Zurmo::t('Core', 'Type',  array(), null, $language),
                )
            );
        }
    }
?>
