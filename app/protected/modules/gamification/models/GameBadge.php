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
     * Model for game badges
     */
    class GameBadge extends Item
    {
        public function __toString()
        {
            if (trim($this->type) == '')
            {
                return Yii::t('Default', '(Unnamed)');
            }
            return $this->type . ' ' . $this->grade;
        }

        /**
         * Given a Item (Either User or Person),  Try to find an existing models and index the returning array by
         * badge type.
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
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('GameBadge');
            $where             = RedBeanModelDataProvider::makeWhere('GameBadge', $searchAttributeData, $joinTablesAdapter);
            $models            = self::getSubset($joinTablesAdapter, null, null, $where, null);
            $indexedModels     = array();
            foreach ($models as $gameBadge)
            {
                $indexedModels[$gameBadge->type] = $gameBadge;
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
                    'grade',
                ),
                'relations' => array(
                    'person'       => array(RedBeanModel::HAS_ONE, 'Item'),
                ),
                'rules' => array(
                    array('type',          'required'),
                    array('type',          'type',    'type' => 'string'),
                    array('type',          'length',  'min'  => 3, 'max' => 64),
                    array('grade',         'required'),
                    array('grade',         'type',    'type' => 'integer'),
                    array('grade',         'default', 'value' => 1),
                    array('grade',         'numerical', 'min' => 1),
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
         * Given a user and a gameBadge, process the bonus points, if applicable for the badge. This will also
         * process grade change points for the given badge.
         * @param GameBadge $gameBadge
         * @param User $user
         */
        public static function processBonusPoints(GameBadge $gameBadge, User $user, $gradeChangeOrNewBadge)
        {
            assert('$gameBadge->id > 0');
            assert('$gradeChangeOrNewBadge == "GradeChange" || $gradeChangeOrNewBadge == "NewBadge"');
            $gameBadgeRulesClassName = $gameBadge->type . 'GameBadgeRules';
            $gamePoint = null;
            if ($gradeChangeOrNewBadge == 'NewBadge' && $gameBadgeRulesClassName::hasBonusPointsOnCreation())
            {
                $type           = $gameBadgeRulesClassName::getNewBonusPointType();
                $gamePoint      = GamePoint::resolveToGetByTypeAndPerson($type, $user);
                $value          = $gameBadgeRulesClassName::getNewBonusPointValue();
            }
            elseif ($gradeChangeOrNewBadge == 'GradeChange' &&
            $gameBadgeRulesClassName::hasBonusPointsOnGradeChange())
            {
                $type           = $gameBadgeRulesClassName::getGradeBonusPointType();
                $gamePoint      = GamePoint::resolveToGetByTypeAndPerson($type, $user);
                $value          = $gameBadgeRulesClassName::getGradeBonusPointValue($gameBadge->grade);
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
