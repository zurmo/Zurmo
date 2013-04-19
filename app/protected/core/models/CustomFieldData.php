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

    class CustomFieldData extends RedBeanModel
    {
        /**
         * Php caching for a single request
         * @var array
         */
        private static $cachedModelsByName = array();

        /**
         * Given a name, get the custom field data model.  Attempts to retrieve from cache, if it is not available,
         * will attempt to retrieve from persistent storage, cache the model, and return.
         * @param string $name
         * @return CustomFieldData model
         * @throws NotFoundException
         */
        public static function getByName($name)
        {
            if (isset(self::$cachedModelsByName[$name]))
            {
                return self::$cachedModelsByName[$name];
            }
            try
            {
                return GeneralCache::getEntry('CustomFieldData' . $name);
            }
            catch (NotFoundException $e)
            {
                assert('is_string($name)');
                assert('$name != ""');
                $bean = R::findOne('customfielddata', "name = :name ", array(':name' => $name));
                assert('$bean === false || $bean instanceof RedBean_OODBBean');
                if ($bean === false)
                {
                    $customFieldData = new CustomFieldData();
                    $customFieldData->name = $name;
                    $customFieldData->serializedData = serialize(array());
                    // An unused custom field data does not present as needing saving.
                    $customFieldData->setNotModified();
                }
                else
                {
                    $customFieldData = self::makeModel($bean);
                }
                self::$cachedModelsByName[$name] = $customFieldData;
                GeneralCache::cacheEntry('CustomFieldData' . $name, $customFieldData);
                return $customFieldData;
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
                    'serializedLabels',
                ),
                'rules' => array(
                    array('name',             'required'),
                    array('name',             'unique'),
                    array('name',             'type',   'type' => 'string'),
                    array('name',             'length', 'min'  => 3, 'max' => 64),
                    array('name',             'match',  'pattern' => '/[A-Z]([a-zA-Z]*[a-z]|[a-z]?)/',
                                                      'message' => 'Name must be PascalCase.'),
                    array('defaultValue',     'type',   'type' => 'string'),
                    array('serializedData',   'required'),
                    array('serializedData',   'type', 'type' => 'string'),
                    array('serializedLabels', 'type', 'type' => 'string'),
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
                self::$cachedModelsByName[$this->name] = $this;
                GeneralCache::cacheEntry('CustomFieldData' . $this->name, $this);
            }
            return $saved;
        }

        protected function unrestrictedDelete()
        {
            unset(self::$cachedModelsByName[$this->name]);
            GeneralCache::forgetEntry('CustomFieldData' . $this->name);
            return parent::unrestrictedDelete();
        }

        public static function forgetAllPhpCache()
        {
            self::$cachedModelsByName = array();
        }
    }
?>
