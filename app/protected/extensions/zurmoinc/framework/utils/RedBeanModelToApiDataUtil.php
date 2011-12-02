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
            $this->model = $model;
        }

        public function getData()
        {
            $data = array();
            //First do id manually
            //For Jason: Why when modelis empty models ids are negative, for example if there are no secondaryEmail for accounts, id is -7
            if (isset($this->model->id) && $this->model->id > 0){
                $data['id'] = $this->model->id;
            }

            //Second get attributes
            foreach($this->model->getAttributes() as $attributeName => $value)
            {

                if($this->model->isOwnedRelation($attributeName))
                {
                    //do something for relations ALSO MAKE SURE HAS_ONE IN the case of owned stuff?
                    //similer use of adapters. with relations you can use the getType on the attributeName as well, since DropDown for example is one type.  This would work well then.
                    //if something like address
                    $relationType = $this->model->getRelationType($attributeName);
                    if ($relationType == RedBeanModel::HAS_ONE)
                    {
                        $util = new RedBeanModelToApiDataUtil($this->model->{$attributeName});
                        $relatedData          = $util->getData();
                        $data[$attributeName] = $relatedData;
                    }
                 }
                 //We don't want to list properties from CustomFieldData objects
                 elseif ($this->model->isRelation($attributeName) && $attributeName != 'data')
                 {

                     $relationType = $this->model->getRelationType($attributeName);
                     //echo $this->model . " - " . $attributeName . ' - ' . $relationType . ' - ' . $value . '<br />';
                     //We need to avoid recursion when createdByUserId is not setup, for example
                     if (isset($value) && $value != '(Unnamed)' && $relationType == RedBeanModel::HAS_ONE)
                     {

                         $util = new RedBeanModelToApiDataUtil($this->model->{$attributeName});
                         $relatedData          = $util->getData();
                         $data[$attributeName] = $relatedData;
                     }

                     $relationType = $this->model->getRelationType($attributeName);
                 }
                 elseif(!$this->model->isRelation($attributeName))
                 {
                    if($attributeName == 'serializedData')
                    {
                        $value = unserialize($value);
                    }
                    $type = ModelAttributeToMixedTypeUtil::getType($this->model, $attributeName);
                    $adapterClassName = $type . 'RedBeanModelAttributeValueToApiValueAdapter';
                    $adapter = new $adapterClassName($this->model, $attributeName, $value);
                    // $data passed by reference
                    $adapter->resolveData($data);
                }
            }
            //Third get owner if owned
            //if($model instanceof OwnedSecurableItem)
            //{

            //}
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