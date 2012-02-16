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
     * Adapter to set attributes from a multi-select drop down attribute form.
     */
    class MultiSelectDropDownModelAttributesAdapter extends DropDownModelAttributesAdapter
    {
        public function setAttributeMetadataFromForm(AttributeForm $attributeForm)
        {
            assert('$attributeForm instanceof MultiSelectDropDownAttributeForm');
            $modelClassName    = get_class($this->model);
            $attributeName     = $attributeForm->attributeName;
            $attributeLabels   = $attributeForm->attributeLabels;
            $defaultValueOrder = $attributeForm->defaultValueOrder;
            $elementType       = $attributeForm->getAttributeTypeName();
            $partialTypeRule   = $attributeForm->getModelAttributePartialRule();
            $isRequired        = (boolean)$attributeForm->isRequired;
            $isAudited         = (boolean)$attributeForm->isAudited;

            $customFieldDataName = $attributeForm->customFieldDataName;
            if ($customFieldDataName == null)
            {
                $customFieldDataName = ucfirst(strtolower($attributeForm->attributeName)); //should we do something else instead?
            }
            $customFieldDataData   = $attributeForm->customFieldDataData;
            $customFieldDataLabels = $attributeForm->customFieldDataLabels;
            $defaultValue          = DropDownDefaultValueOrderUtil::getDefaultValueFromDefaultValueOrder(
                                            $defaultValueOrder, $customFieldDataData);
            ModelMetadataUtil::addOrUpdateCustomFieldRelation($modelClassName,
                                                              $attributeName,
                                                              $attributeLabels,
                                                              $defaultValue,
                                                              $isRequired,
                                                              $isAudited,
                                                              $elementType,
                                                              $customFieldDataName,
                                                              $customFieldDataData,
                                                              $customFieldDataLabels,
                                                              'OwnedMultipleValuesCustomField');
            if ($attributeForm->getCustomFieldDataId() != null)
            {
                foreach ($attributeForm->customFieldDataData as $order => $newValue)
                {
                   if (isset($attributeForm->customFieldDataDataExistingValues[$order]) &&
                      $attributeForm->customFieldDataDataExistingValues[$order] != $newValue)
                   {
                       MultipleValuesCustomField::updateValueByDataIdAndOldValueAndNewValue(
                                        $attributeForm->getCustomFieldDataId(),
                                        $attributeForm->customFieldDataDataExistingValues[$order],
                                        $newValue);
                   }
                }
            }
            $this->resolveDatabaseSchemaForModel($modelClassName);
        }
    }
?>