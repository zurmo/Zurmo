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
     * Helper class to adapt a drop down dependency mapping into a dependency collection that can be utilized by
     * the mapping layout to display in the user interface.
     */
    class DropDownDependencyToMappingLayoutAdapter
    {
        protected $modelClassName;

        protected $attributeName;

        protected $maxDepth;

        /**
         * @param string $modelClassName
         * @param string $attributeName
         * @param integer $maxDepth
         */
        public function __construct($modelClassName, $attributeName, $maxDepth)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName) || $this->attributeName == null');
            assert('is_int($maxDepth) && $maxDepth > 1 && $maxDepth < 5');
            $this->modelClassName = $modelClassName;
            $this->attributeName  = $attributeName;
            $this->maxDepth       = $maxDepth;
        }

        /**
         * Given an array of mapping data, create a collection of DropDownDependencyCustomFieldMapping objects.
         * Include the unmapped objects as well.  For example, if the mapping data only has mappings for 2 attributes,
         * based on the @see $this->maxDepth, it should make additional DropDownDependencyCustomFieldMapping if required.
         * @param array $mappingData
         * @return array of DropDownDependencyCustomFieldMapping objects.
         */
        public function makeDependencyCollectionByMappingData($mappingData)
        {
            assert('is_array($mappingData)');
            $collection                     = array();
            $depthCount                     = 0;
            $availableCustomFieldAttributes = self::getCustomFieldAttributesNotUsedInOtherDependencyAttributes();
            $parentAttributeName            = null;
            foreach ($mappingData as $dependencyData)
            {
                if ($dependencyData['attributeName'] == null)
                {
                     break;
                }
                self::resolveAvailableCustomFieldAttributes($availableCustomFieldAttributes, $parentAttributeName);
                $valuesToParentValues = self::resolveValuesToParentValues($dependencyData);
                $dependencyMapping   = new DropDownDependencyCustomFieldMapping(
                                             $depthCount,
                                             $dependencyData['attributeName'],
                                             $availableCustomFieldAttributes,
                                             self::getAttributeCustomFieldData($dependencyData['attributeName']),
                                             $valuesToParentValues);
                $collection[]        = $dependencyMapping;
                $parentAttributeName = $dependencyData['attributeName'];
                $depthCount++;
            }
            if ($this->maxDepth > $depthCount)
            {
                self::resolveAvailableCustomFieldAttributes($availableCustomFieldAttributes, $parentAttributeName);
                $allowSelection = true;
                for ($i = $depthCount; $i < $this->maxDepth; $i++)
                {
                    $dependencyMapping = new DropDownDependencyCustomFieldMapping(
                                                 $depthCount,
                                                 null,
                                                 $availableCustomFieldAttributes,
                                                 null,
                                                 null
                                             );
                    if (!$allowSelection)
                    {
                        $dependencyMapping->doNotAllowAttributeSelection();
                    }
                    $collection[]   = $dependencyMapping;
                    $allowSelection = false;
                    $depthCount++;
                }
            }
            return $collection;
        }

        /**
         * Public for testing only.
         */
        public function getCustomFieldAttributesNotUsedInOtherDependencyAttributes()
        {
            $modelClassName           = $this->modelClassName;
            $attributeNames           = CustomFieldUtil::getCustomFieldAttributeNames($modelClassName);
            $dropDownDependencyModels = DropDownDependencyDerivedAttributeMetadata::getAllByModelClassName($this->modelClassName);
            foreach ($dropDownDependencyModels as $dropDownDependency)
            {
                if ($dropDownDependency->name != $this->attributeName)
                {
                    $usedAttributeNames = $dropDownDependency->getUsedAttributeNames();
                    foreach ($usedAttributeNames as $usedAttributeName)
                    {
                        if (in_array($usedAttributeName, $attributeNames))
                        {
                            $key = array_search($usedAttributeName, $attributeNames);
                            unset($attributeNames[$key]);
                        }
                    }
                }
            }
            $attributeNamesAndLabels = array();
            foreach ($attributeNames as $attributeName)
            {
                $attributeNamesAndLabels[$attributeName] = $modelClassName::getAnAttributeLabel($attributeName);
            }
            return $attributeNamesAndLabels;
        }

        protected function resolveAvailableCustomFieldAttributes(& $availableCustomFieldAttributes, $parentAttributeName)
        {
            assert('is_array($availableCustomFieldAttributes)');
            assert('is_string($parentAttributeName) || $parentAttributeName == null');
            if ($parentAttributeName != null && isset($availableCustomFieldAttributes[$parentAttributeName]))
            {
                unset($availableCustomFieldAttributes[$parentAttributeName]);
            }
        }

        protected function getAttributeCustomFieldData($attributeName)
        {
            assert('is_string($attributeName) || $attributeName == null');
            if ($attributeName == null)
            {
                return null;
            }
            return CustomFieldDataModelUtil::
                   getDataByModelClassNameAndAttributeName($this->modelClassName, $attributeName);
        }

        protected function resolveValuesToParentValues($dependencyData)
        {
            assert('is_array($dependencyData)');
            if (isset($dependencyData['valuesToParentValues']))
            {
                return $dependencyData['valuesToParentValues'];
            }
            return null;
        }
    }
?>