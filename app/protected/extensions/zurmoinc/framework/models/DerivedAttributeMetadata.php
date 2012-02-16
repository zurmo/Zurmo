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
     * Holds metadata for derived attribute if required. Some derived attributes such as calculated attributes and
     * dropdown dependencies require metadata to be stored.
     */
    abstract class DerivedAttributeMetadata extends RedBeanModel
    {
        /**
         * Get by specifying a name and model class name. This combination is unique
         * and so one object will be returned.
         */
        public static function getByNameAndModelClassName($name, $modelClassName)
        {
            assert('is_string($name)');
            assert('$name != ""');
            assert('is_string($modelClassName)');
            assert('$modelClassName != ""');
            assert('get_called_class() != "DerivedAttributeMetadata"');
            $derivedAttirbuteMetadataTableName   = RedBeanModel::getTableName('DerivedAttributeMetadata');
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $joinTablesAdapter->addFromTableAndGetAliasName($derivedAttirbuteMetadataTableName,
                                                            "{$derivedAttirbuteMetadataTableName}_id");
            $where  = "$derivedAttirbuteMetadataTableName.name = '$name' and ";
            $where .= "$derivedAttirbuteMetadataTableName.modelclassname = '$modelClassName'";
            $models = static::getSubset($joinTablesAdapter, null, null, $where);
            if (count($models) == 0 || count($models) > 1)
            {
                throw new NotFoundException();
            }
            return $models[0];
        }

        /**
         * Given a model class name, return all the derived attributes based on the called class.
         * @param string $modelClassName
         */
        public static function getAllByModelClassName($modelClassName)
        {
            assert('$modelClassName != ""');
            $derivedAttirbuteMetadataTableName   = RedBeanModel::getTableName('DerivedAttributeMetadata');
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $joinTablesAdapter->addFromTableAndGetAliasName($derivedAttirbuteMetadataTableName,
                                                            "{$derivedAttirbuteMetadataTableName}_id");
            $where = "$derivedAttirbuteMetadataTableName.modelclassname = '$modelClassName'";
            $models = static::getSubset($joinTablesAdapter, null, null, $where);
            if (count($models) == 0)
            {
                return array();
            }
            return $models;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                    'modelClassName',
                    'serializedMetadata',
                ),
                'rules' => array(
                    array('name',   'required'),
                    array('name',   'validateUniqueNameByModelClassName'),
                    array('name',   'type', 'type' => 'string'),
                    array('name',   'length', 'max'   => 64),
                    array('name', 	'match', 'pattern' => '/^[A-Za-z0-9_]+$/', // Not Coding Standard
                                    'message' =>  Yii::t('Default', 'Name must not contain spaces or special characters'),
                    ),
                    array('name',   'match', 'pattern' => '/^[a-z]/', // Not Coding Standard
                                    'message' =>  Yii::t('Default', 'First character must be a lower case letter'),
                    ),
                    array('modelClassName',      'required'),
                    array('modelClassName',      'match', 'pattern' => '/[A-Z]([a-zA-Z]*[a-z]|[a-z]?)/',
                                                 'message' => 'Model Class Name must be PascalCase.'),
                    array('modelClassName',      'type', 'type' => 'string'),
                    array('modelClassName',      'length', 'max'   => 64),
                    array('serializedMetadata',  'required'),
                    array('serializedMetadata',  'type', 'type' => 'string'),
                    array('serializedMetadata',  'validateSerializedMetadata', 'on'   => 'nonAutoBuild'),
                )
            );
            return $metadata;
        }

        public function validateSerializedMetadata($attribute, $params)
        {
            if ($this->$attribute != null)
            {
                $unserializedData = unserialize($this->serializedMetadata);
                if(!isset($unserializedData['attributeLabels']))
                {
                    $message = Yii::t('Default', 'Missing the attribute labels.');
                    $this->addError('name', $message);
                }
            }
        }

        public function validateUniqueNameByModelClassName($attribute, $params)
        {
            assert('$attribute == "name"');
            if ($this->$attribute != null)
            {
                $tableName = self::getTableName('DerivedAttributeMetadata');
                $sql       = 'select id from ' . $tableName . " where name = '{$this->$attribute}' and ";
                $sql      .= "modelclassname = '" . $this->modelClassName . "'";
                $rows      = R::getAll($sql);
                if(count($rows) == 0 || count($rows) == 1 && $rows[0]['id'] == $this->getClassId('DerivedAttributeMetadata'))
                {
                    return;
                }
                $message = Yii::t('Default', '{attribute} "{value}" is already in use.',
                                  array('{attribute}' => $attribute, '{name}' => $this->$attribute));
                $this->addError('name', $message);
            }
        }

        public function getLabelByLanguage($language)
        {
            assert('is_string($language)');
            $unserializedData = unserialize($this->serializedMetadata);
            if(isset($unserializedData['attributeLabels']) && isset($unserializedData['attributeLabels'][$language]))
            {
                return $unserializedData['attributeLabels'][$language];
            }
            return $this->name;
        }
    }
?>
