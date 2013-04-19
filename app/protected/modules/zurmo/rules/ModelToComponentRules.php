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
     * Base class of rules that assist with reporting and workflow modules.  Consider extending this class when there is
     * a module that does operations across all or most other modules.  Both workflow and reporting share similarities
     * that make it logical to have this base class
     */
    abstract class ModelToComponentRules
    {
        /**
         * @var string
         */
        protected $modelClassName;

        /**
         * Implement in children classes
         * @throws NotImplementedException
         */
        public static function getRulesName()
        {
            throw new NotImplementedException();
        }

        /**
         * @param $moduleClassName
         * @return Rules based object
         */
        public static function makeByModuleClassName($moduleClassName)
        {
            assert('is_string($moduleClassName)');
            $rulesClassName = $moduleClassName::getPluralCamelCasedName() . static::getRulesName();
            return new $rulesClassName();
        }

        /**
         * @return array
         */
        public static function getMetadata()
        {
            $className = get_called_class();
            try
            {
                return GeneralCache::getEntry($className . 'Metadata');
            }
            catch (NotFoundException $e)
            {
            }
            $metadata = MetadataUtil::getMetadata($className);
            if (YII_DEBUG)
            {
                $className::assertMetadataIsValid($metadata);
            }
            GeneralCache::cacheEntry($className . 'Metadata', $metadata);
            return $metadata;
        }

        /**
         * @param array $metadata
         */
        public static function setMetadata(array $metadata)
        {
            $className = get_called_class();
            if (YII_DEBUG)
            {
                $className::assertMetadataIsValid($metadata);
            }
            MetadataUtil::setMetadata($className, $metadata);
            GeneralCache::cacheEntry($className . 'Metadata', $metadata);
        }

        /**
         * Returns default metadata for use in automatically generating the rules.
         */
        public static function getDefaultMetadata()
        {
            return array();
        }

        /**
         * @param RedBeanModel $model
         * @return array
         */
        public function getDerivedAttributeTypesData(RedBeanModel $model)
        {
            $derivedAttributeTypesData = array();
            $metadata = static::getMetadata();
            foreach (array_reverse(RuntimeUtil::getClassHierarchy(
                                   get_class($model), $model::getLastClassInBeanHeirarchy())) as $modelClassName)
            {
                if (isset($metadata[$modelClassName]) && isset($metadata[$modelClassName]['derivedAttributeTypes']))
                {
                    foreach ($metadata[$modelClassName]['derivedAttributeTypes'] as $derivedAttributeType)
                    {
                        $elementClassName          = $derivedAttributeType . 'Element';
                        $derivedAttributeTypesData
                        [$derivedAttributeType]    = array('label'                => $elementClassName::getDisplayName(),
                                                           'derivedAttributeType' => $derivedAttributeType);
                    }
                }
            }
            return $derivedAttributeTypesData;
        }

        /**
         * @param RedBeanModel $model
         * @param $attribute
         * @return null | string
         */
        public function getAvailableOperatorsTypes(RedBeanModel $model, $attribute)
        {
            assert('is_string($attribute)');
            $modelClassName = $model->getAttributeModelClassName($attribute);
            $metadata = static::getMetadata();
            if (isset($metadata[$modelClassName]) && isset($metadata[$modelClassName]['availableOperatorsTypes']) &&
               isset($attribute, $metadata[$modelClassName]['availableOperatorsTypes'][$attribute]))
            {
                return $metadata[$modelClassName]['availableOperatorsTypes'][$attribute];
            }
            return null;
        }

        /**
         * Override in children classes as necessary.  @see ContactReportRules for example
         * @param User $user
         * @throws NotImplementedException
         */
        public static function getVariableStateModuleLabel(User $user)
        {
            assert('$user->id > 0');
            throw new NotImplementedException();
        }

        /**
         * Override in children classes as necessary.  @see ContactReportRules for example
         * @param User $user
         * @throws NotImplementedException
         */
        public static function canUserAccessModuleInAVariableState(User $user)
        {
            assert('$user->id > 0');
            throw new NotImplementedException();
        }

        /**
         * Override in children classes as necessary.  @see ContactReportRules for example
         * @param User $user
         * @throws NotImplementedException
         */
        public static function resolveStateAdapterUserHasAccessTo(User $user)
        {
            assert('$user->id > 0');
            throw new NotImplementedException();
        }

        /**
         * Override in children classes as necessary.  @see ContactReportRules for example
         * @param $modelClassName
         * @param User $user
         */
        public static function getVariableStateValuesForUser($modelClassName, User $user)
        {
            assert('is_string($modelClassName)');
            assert('$user->id > 0');
        }

        /**
         * Override in children classes as necessary.
         * @param array $metadata
         */
        protected static function assertMetadataIsValid(array $metadata)
        {
        }
    }
?>