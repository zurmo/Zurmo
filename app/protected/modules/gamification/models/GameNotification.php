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
     * Model for game notification data
     */
    class GameNotification extends RedBeanModel
    {
        const TYPE_LEVEL_CHANGE       = 'LevelChange';

        const TYPE_NEW_BADGE          = 'NewBadge';

        const TYPE_BADGE_GRADE_CHANGE = 'GradeChange';

        public function __toString()
        {
            return Zurmo::t('GamificationModule', '(Unnamed)');
        }

        /**
         * Given a user, retrieval all notifications for that user, sorted desc by Id
         * @param User $user
         */
        public static function getAllByUser(User $user)
        {
            assert('$user->id > 0');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'user',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $user->id,
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('GameNotification');
            $where  = RedBeanModelDataProvider::makeWhere('GameNotification', $searchAttributeData, $joinTablesAdapter);
            $models = self::getSubset($joinTablesAdapter, null, null, $where, 'gamenotification.id asc');
            return $models;
        }

        public static function getModuleClassName()
        {
            return 'GamificationModule';
        }

        public static function canSaveMetadata()
        {
            return false;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'serializedData',
                ),
                'relations' => array(
                    'user' => array(RedBeanModel::HAS_ONE, 'User'),
                ),
                'rules' => array(
                    array('user',           'required'),
                    array('serializedData', 'required'),
                    array('serializedData', 'type', 'type' => 'string'),
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * Sets the serializedData for a notification when a new level is reached
         * @param integer $nextLevel
         */
        public function setLevelChangeByNextLevelValue($nextLevelValue)
        {
            assert('is_int($nextLevelValue)');
            $this->serializedData = serialize(array('type' => self::TYPE_LEVEL_CHANGE, 'levelValue' => $nextLevelValue));
        }

        /**
         * Sets the serializedData for a notification when a new badge is received.
         * @param string $badgeType
         */
        public function setNewBadgeByType($badgeType)
        {
            assert('is_string($badgeType)');
            $this->serializedData = serialize(array('type' => self::TYPE_NEW_BADGE, 'badgeType' => $badgeType));
        }

        /**
         * Sets the serializedData for a notification when a badge grade changes.
         * @param string $badgeType
         * @param integer $newGrade
         */
        public function setBadgeGradeChangeByTypeAndNewGrade($badgeType, $newGrade)
        {
            assert('is_string($badgeType)');
            assert('is_int($newGrade)');
            $this->serializedData = serialize(array('type'      => self::TYPE_BADGE_GRADE_CHANGE,
                                                    'badgeType' => $badgeType,
                                                    'grade'     => $newGrade));
        }

        public function getUnserializedData()
        {
            return unserialize($this->serializedData);
        }
    }
?>