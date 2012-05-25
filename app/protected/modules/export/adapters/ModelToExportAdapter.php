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

        /**
        *
        * Get model properties as array.
        * return array
        */
        public function getData()
        {
            $data       = array();
            $data[$this->model->getAttributeLabel('id')] = $this->model->id;
            $retrievableAttributes = static::resolveRetrievableAttributesByModel($this->model);
            foreach ($this->model->getAttributes($retrievableAttributes) as $attributeName => $notUsed)
            {
                $type             = ModelAttributeToMixedArrayTypeUtil::getType($this->model, $attributeName);
                $adapterClassName = $type . 'RedBeanModelAttributeValueToExportValueAdapter';
                if ($type != null && @class_exists($adapterClassName) &&
                    !($this->model->isRelation($attributeName) && $this->model->getRelationType($attributeName) !=
                      RedBeanModel::HAS_ONE))
                {
                    // Normal attribute
                    $adapter = new $adapterClassName($this->model, $attributeName);
                    $adapter->resolveData($data);
                }
                elseif ($this->model->isOwnedRelation($attributeName) &&
                    ($this->model->getRelationType($attributeName) == RedBeanModel::HAS_ONE ||
                      $this->model->getRelationType($attributeName) == RedBeanModel::HAS_MANY_BELONGS_TO))
                {
                    // Owned relationship
                    if ($this->model->{$attributeName}->id > 0)
                    {
                        $util = new ModelToExportAdapter($this->model->{$attributeName});
                        $relatedData          = $util->getData();
                        foreach ($relatedData as $relatedDataAttribute => $relatedDataValue)
                        {
                            if (strtolower($relatedDataAttribute) != 'id')
                            {
                                $exportAttributeName = $this->getDerivedAttributeNameFromTwoStrings(
                                    $this->model->getAttributeLabel($attributeName),
                                    $relatedDataAttribute);

                                $data[$exportAttributeName] = $relatedDataValue;
                            }
                        }
                    }
                    else
                    {
                        $data = array_merge($data, $this->getAtttributesForEmptyRelationships($attributeName));
                    }
                }
                //We don't want to list properties from CustomFieldData objects
                //This is also case fo related models, not only for custom fields
                elseif ($this->model->isRelation($attributeName) &&
                    $this->model->getRelationType($attributeName) == RedBeanModel::HAS_ONE)
                {
                    // Not owned relationship
                    if ($this->model->{$attributeName}->id > 0)
                    {
                        $exportAttributeName = $this->getDerivedAttributeNameFromTwoStrings(
                            $this->model->getAttributeLabel($attributeName),
                            Yii::t('Default', 'Name'));
                        $data[$exportAttributeName] = strval($this->model->{$attributeName});
                    }
                    else
                    {
                        $exportAttributeName = $this->getDerivedAttributeNameFromTwoStrings(
                            $this->model->getAttributeLabel($attributeName),
                            Yii::t('Default', 'Name'));
                        $data[$this->model->getAttributeLabel($attributeName)] = null;
                    }
                }
            }
            return $data;
        }

        /**
        * Return array of retrievable model attributes
        * @return array
        */
        protected static function resolveRetrievableAttributesByModel($model)
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

        public static function getDerivedAttributeNameFromTwoStrings($string1, $string2)
        {
            return $string1 . ModelToExportAdapter::DELIMITER . $string2;
        }

        public function getAtttributesForEmptyRelationships($attributeName)
        {
            $data = array();
            $metadata = $this->model->{$attributeName}->getMetadata();
            foreach ($metadata[get_class($this->model->{$attributeName})]['members'] as $memberName)
            {
                $exportAttributeName = $this->getDerivedAttributeNameFromTwoStrings(
                    $this->model->getAttributeLabel($attributeName),
                    $this->model->{$attributeName}->getAttributeLabel($memberName)
                );
                $data[$exportAttributeName] = null;
            }
            return $data;
        }
    }
?>