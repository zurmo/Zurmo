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

    /**
     * Model for storing an email message item activity.
     */
    class EmailMessageActivity extends Item
    {
        const TYPE_OPEN         = 1;

        const TYPE_CLICK        = 2;

        const TYPE_UNSUBSCRIBE  = 3;

        const TYPE_BOUNCE       = 4;

        const TYPE_SKIP         = 5;

        public static function getTypesArray()
        {
            return array(
                static::TYPE_OPEN           => Zurmo::t('EmailMessagesModule', 'Open'),
                static::TYPE_CLICK          => Zurmo::t('EmailMessagesModule', 'Click'),
                static::TYPE_UNSUBSCRIBE    => Zurmo::t('EmailMessagesModule', 'Unsubscribe'),
                static::TYPE_BOUNCE         => Zurmo::t('EmailMessagesModule', 'Bounce'),
                static::TYPE_SKIP           => Zurmo::t('EmailMessagesModule', 'Skipped'),
            );
        }

        public static function getModuleClassName()
        {
            return 'EmailMessagesModule';
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
                    'latestDateTime',
                    'type',
                    'quantity',
                    'latestSourceIP',
                ),
                'rules' => array(
                    array('latestDateTime',         'type', 'type' => 'datetime'),
                    array('type',                   'required'),
                    array('type',                   'type', 'type' => 'integer'),
                    array('type',                   'numerical'),
                    array('quantity',               'required'),
                    array('quantity',               'type', 'type' => 'integer'),
                    array('quantity',               'numerical', 'integerOnly' => true),
                    array('latestSourceIP',         'type', 'type' => 'string'),
                ),
                'relations' => array(
                    'person'                        => array(RedBeanModel::HAS_ONE, 'Person', RedBeanModel::NOT_OWNED),
                    'emailMessageUrl'               => array(RedBeanModel::HAS_ONE, 'EmailMessageUrl'),
                ),
                'elements' => array(
                    'latestDateTime'                => 'DateTime',
                    // TODO: @Shoaibi: High: What other elements shall we define here?
                ),
                'defaultSortAttribute' => 'latestDateTime',
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getByType($type, $pageSize = null)
        {
            assert('is_int($type)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'type',
                    'operatorType'         => 'equals',
                    'value'                => $type,
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, $pageSize, $where, 'latestDateTime');
        }

        public static function getSearchAttributeDataByTypeAndPersonIdAndUrl($type, $personId, $url = null)
        {
            // TODO: @Shoaibi: Critical: Tests
            assert('is_int($type)');
            assert('is_int($personId)');
            assert('is_string($url) || $url === null');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'type',
                    'operatorType'              => 'equals',
                    'value'                     => $type,
                ),
                2 => array(
                    'attributeName'             => 'person',
                    'relatedAttributeName'      => 'id',
                    'operatorType'              => 'equals',
                    'value'                     => $personId,
                ),
            );
            $searchAttributeData['structure'] = '(1 and 2)';
            if ($url)
            {
                $searchAttributeData['clauses'][3] = array(
                    'attributeName'             => 'emailMessageUrl',
                    'relatedAttributeName'      => 'url',
                    'operatorType'              => 'equals',
                    'value'                     => $url,
                );
                $searchAttributeData['structure'] = '(1 and 2 and 3)';
            }
            return $searchAttributeData;
        }

        public static function getByTypeAndModelIdAndPersonIdAndUrl($type, $modelId, $personId, $url = null,
                                                                                            $sortBy = 'latestDateTime',
                                                                                            $pageSize = null,
                                                                                            $countOnly = false)
        {
            assert('is_int($type)');
            assert('is_int($personId) || is_string($personId)');
            assert('is_int($modelId) || is_string($modelId)');
            assert('is_string($url) || $url === null');
            $relationName   = static::getRelationName();
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'type',
                    'operatorType'              => 'equals',
                    'value'                     => $type,
                ),
                2 => array(
                    'attributeName'             => 'person',
                    'relatedAttributeName'      => 'id',
                    'operatorType'              => 'equals',
                    'value'                     => intval($personId),
                ),
                3 => array(
                    'attributeName'             => $relationName,
                    'relatedAttributeName'      => 'id',
                    'operatorType'              => 'equals',
                    'value'                     => intval($modelId),
                ),
            );
            $searchAttributeData['structure'] = '(1 and 2 and 3)';
            if ($url)
            {
                $searchAttributeData['clauses'][4] = array(
                    'attributeName'             => 'emailMessageUrl',
                    'relatedAttributeName'      => 'url',
                    'operatorType'              => 'equals',
                    'value'                     => $url,
                );
                $searchAttributeData['structure'] = '(1 and 2 and 3 and 4)';
            }
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            if ($countOnly)
            {
                return self::getCount($joinTablesAdapter, $where, get_called_class(), true);
            }
            return self::getSubset($joinTablesAdapter, null, $pageSize, $where, $sortBy);
        }

        public static function createNewActivity($type, $modelId, $personId, $url = null, $sourceIP = null,
                                                                                            $relatedModel = null)
        {
            $relationName               = static::getRelationName();
            if (!isset($relatedModel))
            {
                $relatedModelClassName      = static::getRelatedModelClassName();
                $relatedModel               = $relatedModelClassName::getById(intval($modelId));
            }
            $className                  = get_called_class();
            $activity                   = new $className();
            $activity->quantity         = 1;
            $activity->type             = $type;
            $activity->latestSourceIP   = $sourceIP;
            if ($url)
            {
                $emailMessageUrl            = new EmailMessageUrl();
                $emailMessageUrl->url       = $url;
                $activity->emailMessageUrl  = $emailMessageUrl;
            }
            $person                     = Person::getById(intval($personId));
            if (!$person)
            {
                throw new NotFoundException();
            }
            $activity->person           = $person;
            $activity->$relationName    = $relatedModel;
            if (!$activity->save())
            {
                throw new FailedToSaveModelException();
            }
            else
            {
                static::createNewOpenActivityForFirstClickTrackingActivity($type, $personId, $relatedModel, $sourceIP);
                return true;
            }
        }

        protected static function createNewOpenActivityForFirstClickTrackingActivity($type, $personId, $relatedModel,
                                                                                                            $sourceIP)
        {
            if (static::shouldCreateOpenActivityForTrackingActivity($type, $personId, $relatedModel->id))
            {
                return static::createNewActivity(static::TYPE_OPEN, $relatedModel->id, $personId, null,
                                                    $sourceIP, $relatedModel);
            }
        }

        protected static function shouldCreateOpenActivityForTrackingActivity($type, $personId, $modelId)
        {
            if ($type === static::TYPE_CLICK)
            {
                $existingActivitiesCount = static::getByTypeAndModelIdAndPersonIdAndUrl($type, $modelId, $personId, null,
                                                                                            null, null, true);
                return ($existingActivitiesCount == 1);
            }
            return false;
        }

        protected static function getLabel($language = null)
        {
            return Zurmo::t('EmailMessagesModule', 'Email Message Activity', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('EmailMessagesModule', 'Email Message Activities', array(), null, $language);
        }

        public function beforeSave()
        {
            if (parent::beforeSave())
            {
                $this->latestDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                return true;
            }
            return false;
        }

        public function __toString()
        {
            $type   = intval($this->type);
            if ($type)
            {
                $types  = static::getTypesArray();
                if (isset($types[intval($this->type)]))
                {
                    $type   = $types[intval($this->type)];
                }
            }
            return $this->latestDateTime . ': ' . strval($this->person) . '/' . $type;
        }

        protected static function getRelationName()
        {
            throw new NotImplementedException();
        }

        protected static function getRelatedModelClassName()
        {
            return ucfirst(static::getRelationName());
        }

        protected static function getIndexesDefinition()
        {
            $relatedModelClassName = static::getRelatedModelClassName();
            $relatedColumnName = static::getTableName($relatedModelClassName) . '_id';
            $baseColumnName = static::getTableName(get_class()) . '_id';
            return array($baseColumnName . '_' . $relatedColumnName => array(
                                'members' => array($baseColumnName, $relatedColumnName),
                                'unique' => true,
                            )
                        );
        }
    }
?>