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
    class ModelToArrayAdapter
    {
        /**
         * @var RedBeanModel
         */
        protected $model;

        public function __construct($model)
        {
            assert('$model->id > 0');
            $this->model = $model;
        }

        /**
         *
         * Get model properties as array.
         * return array
         */
        public function getData()
        {
            $data       = array();
            $data['id'] = $this->model->id;
            $retrievableAttributes = static::resolveRetrievableAttributesByModel($this->model);
            foreach ($this->model->getAttributes($retrievableAttributes) as $attributeName => $notUsed)
            {
                $type             = ModelAttributeToMixedArrayTypeUtil::getType($this->model, $attributeName);
                $adapterClassName = $type . 'RedBeanModelAttributeValueToArrayValueAdapter';
                if ($type != null && @class_exists($adapterClassName) &&
                   !($this->model->isRelation($attributeName) && $this->model->getRelationType($attributeName) !=
                      RedBeanModel::HAS_ONE))
                {
                    $adapter = new $adapterClassName($this->model, $attributeName);
                    $adapter->resolveData($data);
                }
                elseif ($this->model->isOwnedRelation($attributeName) &&
                       ($this->model->getRelationType($attributeName) == RedBeanModel::HAS_ONE ||
                        $this->model->getRelationType($attributeName) == RedBeanModel::HAS_MANY_BELONGS_TO))
                {
                    if ($this->model->{$attributeName}->id > 0)
                    {
                        $util = new ModelToArrayAdapter($this->model->{$attributeName});
                        $relatedData          = $util->getData();
                        $data[$attributeName] = $relatedData;
                    }
                    else
                    {
                        $data[$attributeName] = null;
                    }
                 }
                 //We don't want to list properties from CustomFieldData objects
                 //This is also case fo related models, not only for custom fields
                 elseif ($this->model->isRelation($attributeName) &&
                         $this->model->getRelationType($attributeName) == RedBeanModel::HAS_ONE)
                 {
                    if ($this->model->{$attributeName}->id > 0)
                    {
                        $data[$attributeName] = array('id' => $this->model->{$attributeName}->id);
                    }
                    else
                    {
                        $data[$attributeName] = null;
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
            foreach ($model->attributeNames() as $name)
            {
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
    }
?>