<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

    class CustomFieldData extends RedBeanModel
    {
        /**
         * Given a name, get the custom field data model.  Attempts to retrieve from cache, if it is not available,
         * will attempt to retrieve from persistent storage, cache the model, and return.
         * @param string $name
         */
        public static function getByName($name)
        {
            try
            {
                return ZurmoGeneralCache::getEntry('CustomFieldData' . $name);
            }
            catch (NotFoundException $e)
            {
                assert('is_string($name)');
                assert('$name != ""');
                $bean = R::findOne('customfielddata', "name = '$name'");
                assert('$bean === false || $bean instanceof RedBean_OODBBean');
                if ($bean === false)
                {
                    $customFieldData = new CustomFieldData();
                    $customFieldData->name = $name;
                    $customFieldData->serializedData = serialize(array());
                    // An unused custom field data does not present as needing saving.
                    $customFieldData->setNotModified();
                    return $customFieldData;
                }
                $model = self::makeModel($bean);
                ZurmoGeneralCache::cacheEntry('CustomFieldData' . $name, $model);
                return $model;
            }
        }

        public function __toString()
        {
            return $this->name;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                    'defaultValue',
                    'serializedData',
                ),
                'rules' => array(
                    array('name',           'required'),
                    array('name',           'unique'),
                    array('name',           'type',   'type' => 'string'),
                    array('name',           'length', 'min'  => 3, 'max' => 64),
                    array('name',           'match',  'pattern' => '/[A-Z]([a-zA-Z]*[a-z]|[a-z]?)/',
                                                      'message' => 'Name must be PascalCase.'),
                    array('defaultValue',   'type',   'type' => 'string'),
                    array('serializedData', 'required'),
                    array('serializedData', 'type', 'type' => 'string'),
                )
            );
            return $metadata;
        }

        /**
         * Any changes to the model must be re-cached.
         * @see RedBeanModel::save()
         */
        public function save($runValidation = true, array $attributeNames = null)
        {
            $saved = parent::save($runValidation, $attributeNames);
            if ($saved)
            {
                ZurmoGeneralCache::cacheEntry('CustomFieldData' . $this->name, $this);
            }
            return $saved;
        }
    }
?>
