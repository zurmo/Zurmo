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
     * Manages game scores.
     */
    class GameScore extends Item
    {
        public function __toString()
        {
            if (trim($this->type) == '')
            {
                return Yii::t('Default', '(Unnamed)');
            }
            return $this->type;
        }

        /**
         * Given a score type and Item (Either User or Person),  try to find an existing model. If the model does
         * not exist, create it and populate the Item and type. @return The found or created model.
         * @param string $type
         * @param Item $person
         */
        public static function resolveToGetByTypeAndPerson($type,  Item $person)
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
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('GameScore');
            $where  = RedBeanModelDataProvider::makeWhere('GameScore', $searchAttributeData, $joinTablesAdapter);
            $models = self::getSubset($joinTablesAdapter, null, null, $where, null);
            if (count($models) > 1)
            {
                throw new NotSupportedException();
            }
            if (count($models) == 0)
            {
                $gameScore         = new GameScore();
                $gameScore->type   = $type;
                $gameScore->person = $person;
                return $gameScore;
            }
            return $models[0];
        }

        /**
         * Given a Item (Either User or Person),  Try to find an existing models and index the returning array by
         * score type.
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
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('GameScore');
            $where             = RedBeanModelDataProvider::makeWhere('GameScore', $searchAttributeData, $joinTablesAdapter);
            $models            = self::getSubset($joinTablesAdapter, null, null, $where, null);
            $indexedModels     = array();
            foreach ($models as $gameScore)
            {
                $indexedModels[$gameScore->type] = $gameScore;
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
                    'person' => array(RedBeanModel::HAS_ONE, 'Item'),
                ),
                'rules' => array(
                    array('type',          'required'),
                    array('type',          'type',      'type' => 'string'),
                    array('type',          'length',    'min'  => 3, 'max' => 64),
                    array('value',         'type',      'type' => 'integer'),
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
         * Add a single point of value to the value count.
         */
        public function addValue()
        {
            $this->value = $this->value + 1;
        }
    }
?>
