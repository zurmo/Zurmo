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
     * Helper class used to convert models into arrays
     */
    class ModelToExportAdapter extends ModelToArrayAdapter
    {
        /**
        * Use when multiple attribute names
        * need to be combined together into one string that can easily
        * be parsed later.
        */
        const DELIMITER = ' - ';

        public static function getLabelFromTwoAttributeStrings($stringOne, $stringTwo)
        {
            return $stringOne . ModelToExportAdapter::DELIMITER . $stringTwo;
        }

        /**
        *
        * Get model properties as array.
        * return array
        */
        public function getData()
        {
            $data                  = array();
            $data[]                = $this->model->id;
            $retrievableAttributes = static::resolveExportableAttributesByModel($this->model);
            foreach ($this->model->getAttributes($retrievableAttributes) as $attributeName => $notUsed)
            {
                if ((null !== $adapterClassName = $this->getRedBeanModelAttributeValueToExportValueAdapterClassName($attributeName)) &&
                    !($this->model->isRelation($attributeName) && $this->model->getRelationType($attributeName) !=
                      RedBeanModel::HAS_ONE))
                {
                    //Non-relation attribute
                    $adapter = new $adapterClassName($this->model, $attributeName);
                    $adapter->resolveData($data);
                }
                elseif ($this->isHasOneVariationOwnedRelation($attributeName))
                {
                    //Owned relation attribute
                    if ($this->model->{$attributeName}->id > 0)
                    {
                        $util           = new ModelToExportAdapter($this->model->{$attributeName});
                        $relatedData    = $util->getData();
                        foreach ($relatedData as $relatedDataAttribute => $relatedDataValue)
                        {
                            if (strtolower($relatedDataAttribute) != 'id')
                            {
                                $data[] = $relatedDataValue;
                            }
                        }
                    }
                    else
                    {
                        $data = array_merge($data, $this->getAllAttributesDataAsNull($attributeName));
                    }
                }
                //We do not want to list properties from CustomFieldData objects
                //This is also the case for related models, not only for custom fields
                elseif ($this->model->isRelation($attributeName) &&
                        $this->model->getRelationType($attributeName) == RedBeanModel::HAS_ONE)
                {
                    //Non-owned relation
                    if ($this->model->{$attributeName}->id > 0)
                    {
                        $data[] = strval($this->model->{$attributeName});
                    }
                    else
                    {
                        $data[] = null;
                    }
                }
            }
            return $data;
        }

        /**
         * Get the header row data which includes a label for each column
         * @return array $data
         */
        public function getHeaderData()
        {
            $data                  = array();
            $data[]                = $this->resolveIdLabelToTitleCaseForExport($this->model->getAttributeLabel('id'));
            $retrievableAttributes = static::resolveExportableAttributesByModel($this->model);
            foreach ($this->model->getAttributes($retrievableAttributes) as $attributeName => $notUsed)
            {
                if ((null !== $adapterClassName = $this->getRedBeanModelAttributeValueToExportValueAdapterClassName($attributeName)) &&
                    !($this->model->isRelation($attributeName) && $this->model->getRelationType($attributeName) !=
                        RedBeanModel::HAS_ONE))
                {
                    //Non-relation attribute
                    $adapter = new $adapterClassName($this->model, $attributeName);
                    $adapter->resolveHeaderData($data);
                }
                elseif ($this->isHasOneVariationOwnedRelation($attributeName))
                {
                    //Owned relation attribute
                    if ($this->model->{$attributeName}->id > 0)
                    {
                        $util           = new ModelToExportAdapter($this->model->{$attributeName});
                        $relatedData    = $util->getData();
                        foreach ($relatedData as $relatedDataAttribute => $notUsed)
                        {
                            if (strtolower($relatedDataAttribute) != 'id')
                            {
                                $exportAttributeName = static::getLabelFromTwoAttributeStrings(
                                    $this->model->getAttributeLabel($attributeName), $relatedDataAttribute);
                                $data[] = $exportAttributeName;
                            }
                        }
                    }
                    else
                    {
                        $data = array_merge($data, $this->getAllAtttributesDataAsLabels($attributeName));
                    }
                }
                //We do not want to list properties from CustomFieldData objects
                //This is also the case for related models, not only for custom fields
                elseif ($this->model->isRelation($attributeName) &&
                    $this->model->getRelationType($attributeName) == RedBeanModel::HAS_ONE)
                {
                    //Non-owned relation
                    if ($this->model->{$attributeName}->id > 0)
                    {
                        $label  = static::getLabelFromTwoAttributeStrings(
                                  $this->model->getAttributeLabel($attributeName), Zurmo::t('ExportModule', 'Name'));
                        $data[] = $label;
                    }
                    else
                    {
                        $data[] = $this->model->getAttributeLabel($attributeName);
                    }
                }
            }
            return $data;
        }

        /**
         * Return array of retrievable model attributes
         * @param $model
         * @return array
         */
        protected static function resolveExportableAttributesByModel($model)
        {
            $retrievableAttributeNames = array();
            $metadata = $model->getMetadata();
            foreach ($model->attributeNames() as $name)
            {
                if (isset($metadata[get_class($model)]['noExport']) && in_array($name, $metadata[get_class($model)]['noExport']))
                {
                    continue;
                }
                try
                {
                    $value = $model->{$name};
                    $retrievableAttributeNames[] = $name;
                }
                catch (Exception $e)
                {
                }
            }
            return $retrievableAttributeNames;
        }

        protected function getRedBeanModelAttributeValueToExportValueAdapterClassName($attributeName)
        {
            assert('is_string($attributeName)');
            $type = ModelAttributeToMixedArrayTypeUtil::getType($this->model, $attributeName);
            $adapterClassName = $type . 'RedBeanModelAttributeValueToExportValueAdapter';
            if ($type != null && @class_exists($adapterClassName))
            {
                return $adapterClassName;
            }
        }

        protected function isHasOneVariationOwnedRelation($attributeName)
        {
            assert('is_string($attributeName)');
            if ($this->model->isOwnedRelation($attributeName) &&
                ($this->model->getRelationType($attributeName) == RedBeanModel::HAS_ONE ||
                 $this->model->getRelationType($attributeName) == RedBeanModel::HAS_MANY_BELONGS_TO))
            {
                return true;
            }
            return false;
        }

        protected function resolveIdLabelToTitleCaseForExport($id)
        {
            return mb_convert_case($id, MB_CASE_TITLE, "UTF-8");
        }

        protected function getAllAttributesDataAsNull($attributeName)
        {
            $data = array();
            $metadata = $this->model->{$attributeName}->getMetadata();
            foreach ($metadata[get_class($this->model->{$attributeName})]['members'] as $memberName)
            {
                $data[] = null;
            }
            return $data;
        }

        protected function getAllAtttributesDataAsLabels($attributeName)
        {
            $data = array();
            $metadata = $this->model->{$attributeName}->getMetadata();
            foreach ($metadata[get_class($this->model->{$attributeName})]['members'] as $memberName)
            {
                $label  = static::getLabelFromTwoAttributeStrings(
                          $this->model->getAttributeLabel($attributeName),
                          $this->model->{$attributeName}->getAttributeLabel($memberName));
                $data[] = $label;
            }
            return $data;
        }
    }
?>