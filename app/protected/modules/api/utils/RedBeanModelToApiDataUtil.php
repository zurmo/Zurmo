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

    /**
     * Helper class used to convert models into arrays
     */
    class RedBeanModelToApiDataUtil
    {
        protected $model;

        public function __construct($model)
        {
            assert('$model->id > 0');
            $this->model = $model;
        }

        public function getData()
        {
            $data       = array();
            $data['id'] = $this->model->id;
            foreach($this->model->getAttributes() as $attributeName => $notUsed)
            {
                $type             = ModelAttributeToMixedTypeUtil::getType($this->model, $attributeName);
                $adapterClassName = $type . 'RedBeanModelAttributeValueToApiValueAdapter';
                if($type != null && @class_exists($adapterClassName) &&
                   !($this->model->isRelation($attributeName) && $this->model->getRelationType($attributeName) !=
                      RedBeanModel::HAS_ONE))
                {
                    $adapter = new $adapterClassName($this->model, $attributeName);
                    $adapter->resolveData($data);
                }
                elseif($this->model->isOwnedRelation($attributeName) &&
                       $this->model->getRelationType($attributeName) == RedBeanModel::HAS_ONE)
                {
                    if($this->model->{$attributeName}->id > 0)
                    {
                        $util = new RedBeanModelToApiDataUtil($this->model->{$attributeName});
                        $relatedData          = $util->getData();
                        $data[$attributeName] = $relatedData;
                        $data[$attributeName] = null;
                    }
                    else
                    {
                        $data[$attributeName] = null;
                    }
                 }
                 //We don't want to list properties from CustomFieldData objects
                 elseif ($this->model->isRelation($attributeName) &&
                         $this->model->getRelationType($attributeName) == RedBeanModel::HAS_ONE)
                 {
                    if($this->model->{$attributeName}->id > 0)
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

        public function getCustomFields()
        {
            $data = array();
            $metadata = $this->model->getMetadata();
            foreach ($metadata as $key => $classMetadata)
            {
                if (isset($classMetadata['customFields']))
                {
                    foreach ($classMetadata['customFields'] as $customFieldName => $customFieldDataName)
                    {
                        //Extract custom field data here
                    }
                }
            }
        }
    }
?>