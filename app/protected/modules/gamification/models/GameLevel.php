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
     * Model for game levels.
     */
    class GameLevel extends Item
    {
        /**
         * Used to define the level type as being general, which means it is a total of all point groups
         * @var String
         */
        const TYPE_GENERAL            = 'General';

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

        /**
         * Given a point type and Item (Either User or Person),  try to find an existing model. If the model does
         * not exist, create it and populate the Item and type. @return The found or created model.
         * @param string $type
         * @param Item $person
         */
        public static function resolveByTypeAndPerson($type, Item $person)
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
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('GameLevel');
            $where  = RedBeanModelDataProvider::makeWhere('GameLevel', $searchAttributeData, $joinTablesAdapter);
            $models = self::getSubset($joinTablesAdapter, null, null, $where, null);
            if (count($models) > 1)
            {
                throw new NotSupportedException();
            }
            if (count($models) == 0)
            {
                $gameLevel = new GameLevel();
                $gameLevel->type   = $type;
                $gameLevel->person = $person;
                $gameLevel->value  = 1;
                return $gameLevel;
            }
            return $models[0];
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
                    'person' => array(RedBeanModel::HAS_ONE, 'Item'),
                ),
                'rules' => array(
                    array('type',          'required'),
                    array('type',          'type',    'type' => 'string'),
                    array('type',          'length',  'min'  => 3, 'max' => 64),
                    array('value',         'type',    'type' => 'integer'),
                    array('value',         'default', 'value' => 1),
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
            $this->value = $this->value + $value;
        }

        /**
         * Given a user and a gameLevel, process the bonus points, if applicable for the badge. This will also
         * process grade change points for the given badge.
         * @param GameBadge $gameBadge
         * @param User $user
         */
        public static function processBonusPointsOnLevelChange(GameLevel $gameLevel, User $user)
        {
            assert('$gameLevel->id > 0');
            $gameLevelRulesClassName = $gameLevel->type . 'GameLevelRules';
            $gamePoint = null;
            if ($gameLevelRulesClassName::hasBonusPointsOnLevelChange())
            {
                $type           = $gameLevelRulesClassName::getLevelBonusPointType();
                $gamePoint      = GamePoint::resolveToGetByTypeAndPerson($type, $user);
                $value          = $gameLevelRulesClassName::getLevelBonusPointValue($gameLevel->value);
            }
            if ($gamePoint != null && $value > 0)
            {
                $gamePoint->addValue($value);
                $saved          = $gamePoint->save();
                if (!$saved)
                {
                    throw new NotSupportedException();
                }
            }
        }
    }
?>
