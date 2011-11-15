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

    class DropDownDependencyToMappingLayoutAdapter
    {
        protected $modelClassName;

        protected $attributeName;

        protected $maxDepth;

        public function __construct($modelClassName, $attributeName, $maxDepth)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName) || $this->attributeName == null');
            assert('is_int($maxDepth) && $maxDepth > 1 && $maxDepth < 5');
            $this->modelClassName = $modelClassName;
            $this->attributeName  = $attributeName;
            $this->maxDepth       = $maxDepth;
        }

        public function makeDependencyCollectionByMappingData($mappingData)
        {
            assert('is_array($mappingData)');
            $collection                     = array();
            $depthCount                     = 0;
            $availableCustomFieldAttributes = self::getCustomFieldAttributesNotUsedInOtherDependencyAttributes();
            $parentAttributeName            = null;
            foreach($mappingData as $dependencyData)
            {
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
                $depthCount ++;
            }
            if($this->maxDepth > $depthCount)
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
                    if(!$allowSelection)
                    {
                        $dependencyMapping->doNotAllowAttributeSelection();
                    }
                    $collection[]   = $dependencyMapping;
                    $allowSelection = false;
                    $depthCount ++;
                }
            }
            return $collection;
        }

        /**
         * Public for testing only.
         */
        protected function getCustomFieldAttributesNotUsedInOtherDependencyAttributes()
        {
            $modelClassName           = $this->modelClassName;
            $model                    = new $modelClassName(false);
            $attributeNames           = CustomFieldUtil::getCustomFieldAttributeNames($model);
            $dropDownDependencyModels = DropDownDependencyDerivedAttributeMetadata::getAllByModelClassName($this->modelClassName);
            foreach($dropDownDependencyModels as $dropDownDependency)
            {
                if($dropDownDependency->name != $this->attributeName)
                {
                    $usedAttributeNames = $dropDownDependency->getUsedModelAttributeNames();
                    foreach($usedAttributeNames as $usedAttributeName)
                    {
                        if(in_array($usedAttributeName, $attributeNames))
                        {
                            $key = array_search($usedAttributeName, $attributeNames);
                            unset($attributeNames[$key]);
                        }
                    }
                }
            }
            $attributeNamesAndLabels = array();
            foreach($attributeNames as $attributeName)
            {
                $attributeNamesAndLabels[$attributeName] = $model->getAttributeLabel($attributeName);
            }
            return $attributeNamesAndLabels;
        }

        protected function resolveAvailableCustomFieldAttributes(& $availableCustomFieldAttributes, $parentAttributeName)
        {
            assert('is_array($availableCustomFieldAttributes)');
            assert('is_string($parentAttributeName) || $parentAttributeName == null');
            if($parentAttributeName != null && isset($availableCustomFieldAttributes[$parentAttributeName]))
            {
                unset($availableCustomFieldAttributes[$parentAttributeName]);
            }
        }

        protected function getAttributeCustomFieldData($attributeName)
        {
            assert('is_string($attributeName) || $attributeName == null');
            if($attributeName == null)
            {
                return null;
            }
            return CustomFieldDataModelUtil::
                   getDataByModelClassNameAndAttributeName($this->modelClassName, $attributeName);
        }

        protected function resolveValuesToParentValues($dependencyData)
        {
            assert('is_array($dependencyData)');
            if(isset($dependencyData['valuesToParentValues']))
            {
                return $dependencyData['valuesToParentValues'];
            }
            return null;
        }
    }
?>