<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Provides an interface that is the same as a RedBeanModel
     * interface, but allows developer to extend RedBeanModel
     * with additional attributes that are not part of the model.
     */
    abstract class ModelForm extends CModel
    {
        private static $_names = array();

        protected $model;

        /**
         * Override in child to implement.
         * @throws NotImplementedException
         */
        protected static function getRedBeanModelClassName()
        {
            throw new NotImplementedException();
        }

        public function __construct(RedBeanModel $model)
        {
            $this->model = $model;
        }

        public function getModel()
        {
            return $this->model;
        }

        public function __toString()
        {
            return strval($this->model);
        }

        public function __set($name, $value)
        {
            if (property_exists($this, $name))
            {
                $this->$name        = $value;
            }
            else
            {
                $this->model->$name = $value;
            }
        }

        public function __get($name)
        {
            if (property_exists($this, $name))
            {
                return $this->$name;
            }
            return $this->model->$name;
        }

        /**
         * Interface to mimic model getMetadata method.
         * Can only be called non-statically since the model is required.
         */
        public function getMetadata()
        {
            return $this->model->getMetadata();
        }

        /**
         * If the attribute exists on the form, then assume it is not a relation since the form
         * does not support relational attributes.
         */
        public static function isOwnedRelation($attributeName)
        {
            if (property_exists(get_called_class(), $attributeName))
            {
                return false;
            }
            $modelClassName = static::getRedBeanModelClassName();
            return $modelClassName::isOwnedRelation($attributeName);
        }

        /**
         * If the attribute exists on the form, then assume it is not a relation since the form
         * does not support relational attributes.
         */
        public static function isRelation($attributeName)
        {
            if (property_exists(get_called_class(), $attributeName))
            {
                return false;
            }
            $modelClassName = static::getRedBeanModelClassName();
            return $modelClassName::isRelation($attributeName);
        }

        /**
         * If the attribute exists on the form, then assume it is not a relation since the form
         * does not support relational attributes.
         */
        public static function getRelationModelClassName($relationName)
        {
            if (property_exists(get_called_class(), $relationName))
            {
                return false;
            }
            $modelClassName = static::getRedBeanModelClassName();
            return $modelClassName::getRelationModelClassName($relationName);
        }

        /**
         * Returns true if the named attribute is a property on this
         * model.
         */
        public function isAttribute($attributeName)
        {
            assert('is_string($attributeName)');
            assert('$attributeName != ""');
            if (property_exists($this, $attributeName))
            {
                return true;
            }
            return $this->model->isAttribute($attributeName);
        }

        /**
         * Override to properly check if the attribute is required or not.
         * (non-PHPdoc)
         * @see CModel::isAttributeRequired()
         */
        public function isAttributeRequired($attribute)
        {
            if (property_exists($this, $attribute))
            {
                return parent::isAttributeRequired($attribute);
            }
            return $this->model->isAttributeRequired($attribute);
        }

        /**
         * Returns the list of attribute names.
         * By default, this method returns all public properties of the class.
         * You may override this method to change the default.
         * @return array list of attribute names. Defaults to all public properties of the class.
         */
        public function attributeNames()
        {
            $className = get_class($this);
            if (!isset(self::$_names[$className]))
            {
                $class = new ReflectionClass(get_class($this));
                $names = array();
                foreach ($class->getProperties() as $property)
                {
                    $name = $property->getName();
                    if ($property->isPublic() && !$property->isStatic())
                    {
                        $names[] = $name;
                    }
                }
                return self::$_names[$className] = $names;
            }
            else
            {
                return self::$_names[$className];
            }
        }

        public function getAttributeLabel($attribute)
        {
            assert('is_string($attribute)');
            assert('$attribute != ""');
            $attributeLabels = $this->attributeLabels();
            if (isset($attributeLabels[$attribute]))
            {
                return $attributeLabels[$attribute];
            }
            return $this->model->getAttributeLabel($attribute);
        }

        /**
         * Override of setAttributes in CModel to support setting attributes into this form as well
         * as the related model.  Splits $values into two arrays. First array is name/value pairs of attributes
         * on this form, whereas the second array is name/value pairs on the model.
         */
        public function setAttributes($values, $safeOnly = true)
        {
            $formValues  = array();
            $modelValues = array();
            foreach ($values as $name => $value)
            {
                if (property_exists($this, $name))
                {
                    $formValues[$name] = $value;
                }
                else
                {
                    $modelValues[$name] = $value;
                }
            }
            parent::setAttributes($formValues, $safeOnly);
            $this->model->setAttributes($modelValues, $safeOnly);
        }

        /**
         * Performs validation on this form and the model.
         * clearErrors is not supported, because the model does not support this
         * parameter in the RedBeanModel->validate() function.
         * Currently this method does not support specifying the $attributes parameter.
         */
        public function validate($attributes = null, $clearErrors = true)
        {
            assert('$clearErrors == true');
            assert('$attributes == null');
            $formValidatedSuccessfully  = parent::validate($attributes);
            $modelValidatedSuccessfully = $this->model->validate($attributes, static::shouldIgnoreRequiredValidator());
            if (!$modelValidatedSuccessfully || !$formValidatedSuccessfully)
            {
                return false;
            }
            return true;
        }

        /**
         * Override to properly get validators for an attribute when they are on the model.
         * Todo: Factor in scenario for model attributes.
         * (non-PHPdoc)
         * @see CModel::getValidators()
         */
        public function getValidators($attribute = null)
        {
            if ($attribute != null && !property_exists($this, $attribute))
            {
                return $this->model->getValidators($attribute);
            }
            return parent::getValidators($attribute);
        }

        /**
         * Override and set to true if you need to ignore the required validator.
         */
        protected static function shouldIgnoreRequiredValidator()
        {
            return false;
        }

        /**
         * Return array of errors on form and model.
         * Currently this method does not support specifying the $attributeNameOrNames parameter.
         */
        public function getErrors($attributeNameOrNames = null)
        {
            $formErrors  = parent::getErrors($attributeNameOrNames);
            $modelErrors = $this->model->getErrors($attributeNameOrNames);
            return array_merge($formErrors, $modelErrors);
        }

        /**
         * @return true/false. If the form and/or the model has any errors.
         * Currently this method does not support specifying the $attributeNameOrNames parameter.
         */
        public function hasErrors($attributeNameOrNames = null)
        {
            $hasFormErrors  = parent::hasErrors($attributeNameOrNames);
            if ($hasFormErrors)
            {
                return true;
            }
            return false;
        }

        /**
         * Saves the model.
         */
        public function save($runValidation = true, array $attributeNames = null)
        {
            assert('$attributeNames == null');
            if (!$runValidation || $this->validate())
            {
                return $this->model->save($runValidation);
            }
            return false;
        }
    }
?>