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
     * Model for game points.
     */
    class GamePoint extends Item
    {
        /**
         * @var String
         */
        const TYPE_USER_ADOPTION      = 'UserAdoption';

        /**
         * @var String
         */
        const TYPE_SALES              = 'Sales';

        /**
         * @var String
         */
        const TYPE_NEW_BUSINESS       = 'NewBusiness';

        /**
         * @var String
         */
        const TYPE_ACCOUNT_MANAGEMENT = 'AccountManagement';

        /**
         * @var String
         */
        const TYPE_COMMUNICATION      = 'Communication';

        /**
         * @var String
         */
        const TYPE_TIME_MANAGEMENT      = 'TimeManagement';

        public function __toString()
        {
            if (trim($this->type) == '')
            {
                return Yii::t('Default', '(Unnamed)');
            }
            return $this->type;
        }

        public function __set($attributeName, $value)
        {
            if ($attributeName == 'value')
            {
                $this->addValue($value);
            }
            else
            {
                parent::__set($attributeName, $value);
            }
        }

        /**
         * Given a point type and Item (Either User or Person),  try to find an existing model. If the model does
         * not exist, create it and populate the Item and type. @return The found or created model.
         * @param string $type
         * @param Item $person
         */
        public static function resolveToGetByTypeAndPerson($type, Item $person)
        {
            assert('is_string($type)');
            assert('$person->id > 0');
            assert('$person instanceof Contact || $person instanceof User');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'type',
                    'operatorType'         => 'equals',
                    'value'                => $type,
                ),
                2 => array(
                    'attributeName'        => 'person',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $person->getClassId('Item'),
                ),
            );
            $searchAttributeData['structure'] = '1 and 2';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('GamePoint');
            $where  = RedBeanModelDataProvider::makeWhere('GamePoint', $searchAttributeData, $joinTablesAdapter);
            $models = self::getSubset($joinTablesAdapter, null, null, $where, null);
            if (count($models) > 1)
            {
                throw new NotSupportedException();
            }
            if (count($models) == 0)
            {
                $gamePoint = new GamePoint();
                $gamePoint->type   = $type;
                $gamePoint->person = $person;
                return $gamePoint;
            }
            return $models[0];
        }

        /**
         * Given a Item (Either User or Person),  Try to find an existing models and index the returning array by
         * point type.
         * @param Item $person
         */
        public static function getAllByPersonIndexedByType(Item $person)
        {
            assert('$person->id > 0');
            assert('$person instanceof Contact || $person instanceof User');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'person',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $person->getClassId('Item'),
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('GamePoint');
            $where             = RedBeanModelDataProvider::makeWhere('GamePoint', $searchAttributeData, $joinTablesAdapter);
            $models            = self::getSubset($joinTablesAdapter, null, null, $where, null);
            $indexedModels     = array();
            foreach ($models as $gamePoint)
            {
                $indexedModels[$gamePoint->type] = $gamePoint;
            }
            return $indexedModels;
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
                    'type',
                    'value',
                ),
                'relations' => array(
                    'person'       => array(RedBeanModel::HAS_ONE, 'Item'),
                    'transactions' => array(RedBeanModel::HAS_MANY, 'GamePointTransaction', RedBeanModel::OWNED),
                ),
                'rules' => array(
                    array('type',          'required'),
                    array('type',          'type',    'type' => 'string'),
                    array('type',          'length',  'min'  => 3, 'max' => 64),
                    array('value',         'type',    'type' => 'integer'),
                    array('value',         'numerical', 'min' => 1),
                    array('value',         'required'),
                    array('person',        'required'),
                ),
                'elements' => array(
                    'person' => 'Person',
                ),
                'defaultSortAttribute' => 'type',
                'noAudit' => array(
                    'type',
                    'value',
                    'person',
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * Add specified value.
         */
        public function addValue($value)
        {
            assert('is_int($value)');
            $this->unrestrictedSet('value', $this->value + $value);
            $gamePointTransaction                   = new GamePointTransaction();
            $gamePointTransaction->value            = $value;
            $this->transactions->add($gamePointTransaction);
        }

        /**
         * Given a user and a number, determine if a user's existing total points exceeds the specified number.
         * If so, return true, otherwise return false.
         * @param User $user
         * @param Integer $points
         */
        public static function doesUserExceedPointsByLevelType(User $user, $points, $levelType)
        {
            assert('$user->id > 0');
            assert('is_int($points)');
            assert('is_string($levelType) && $levelType != null');
            $data = self::getSummationPointsDataByLevelTypeAndUser($user, $levelType);
            if ($data != null && $data['sum'] > $points)
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        public static function getSummationPointsDataByLevelTypeAndUser(User $user, $levelType)
        {
            assert('$user->id > 0');
            assert('is_string($levelType) && $levelType != null');
            $wherePart = static::getPointTypeWherePartByLevelType($levelType);
            $sql       = "select sum(value) sum from gamepoint where " . $wherePart . " person_item_id = " .
                         $user->getClassId('Item') . " group by person_item_id";
            return R::getRow($sql);
        }

        /**
         * Given a level type, get the corresponding where sql part to filter by point type if applicable.  Level types
         * match point types for now, so if the level type is not valid then it will thrown an exception.
         * @param string $levelType
         * @throws NotSupportedException
         */
        protected static function getPointTypeWherePartByLevelType($levelType)
        {
            assert('is_string($levelType) && $levelType != null');
            if (!in_array($levelType, array(GameLevel::TYPE_GENERAL,
                                     GameLevel::TYPE_SALES,
                                     GameLevel::TYPE_NEW_BUSINESS,
                                     GameLevel::TYPE_ACCOUNT_MANAGEMENT,
                                     GameLevel::TYPE_TIME_MANAGEMENT,
                                     GameLevel::TYPE_COMMUNICATION)))
            {
                throw new NotSupportedException();
            }
            if ($levelType == GameLevel::TYPE_GENERAL)
            {
                return null;
            }
            else
            {
                $pointType = $levelType;
                return ' type = "' . $pointType . '" and ';
            }
        }
    }
?>
