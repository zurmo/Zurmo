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
     * Sanitizer to handle attribute values that are possible a model name not just a model id.
     *
     * Override to accomodate a value type of 'ZURMO_MODEL_NAME'. This would represent a model 'name' attribute value
     * that can be used as a unique identifier to map to existing models. An example is when importing a contact, and
     * the account name is provided. If the name is found, then the contact will be connected to the existing account
     * otherwise a new account is created with the name provided.
     *
     */
    class RelatedModelNameOrIdValueTypeSanitizerUtil extends ExternalSystemIdSuppportedSanitizerUtil
    {
        protected $maxNameLength;

        public static function getLinkedMappingRuleType()
        {
            return 'RelatedModelValueType';
        }

        /**
         * @param RedBean_OODBBean $rowBean
         * @throws NotSupportedException
         */
        public function analyzeByRow(RedBean_OODBBean $rowBean)
        {
            if ($this->mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID)
            {
                $found = $this->resolveFoundIdByValue($rowBean->{$this->columnName});
            }
            elseif ($this->mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_NAME)
            {
                if ($rowBean->{$this->columnName} == null)
                {
                    $found = false;
                }
                else
                {
                    $modelClassName = $this->attributeModelClassName;
                    if (!method_exists($modelClassName, 'getByName'))
                    {
                        throw new NotSupportedException();
                    }
                    $modelsFound = $modelClassName::getByName($rowBean->{$this->columnName});
                    if (count($modelsFound) == 0)
                    {
                        $found = false;
                        if (strlen($rowBean->{$this->columnName}) > $this->maxNameLength)
                        {
                            $label   = Zurmo::t('ImportModule', 'Value is too long.');
                            $this->shouldSkipRow      = true;
                            $this->analysisMessages[] = $label;
                            return;
                        }
                    }
                    else
                    {
                        $found = true;
                    }
                }
            }
            else
            {
                $found = $this->resolveFoundExternalSystemIdByValue($rowBean->{$this->columnName});
            }
            if ($found)
            {
                $this->resolveForFoundModel();
            }
            else
            {
                $this->resolveForUnfoundModel($rowBean);
            }
            if ($this->mappingRuleData["type"] == IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)
            {
                if (strlen($rowBean->{$this->columnName}) > $this->externalSystemIdMaxLength)
                {
                    $label = Zurmo::t('ImportModule', 'Is too long.');
                    $this->shouldSkipRow      = true;
                    $this->analysisMessages[] = $label;
                }
            }
        }

        /**
         * Given a value that is either a zurmo id or an external system id, resolve that the
         * value is valid.  The value presented can also be a 'name' value.  If the name is not found as a model
         * in the system, then a new related model will be created using this name.
         * NOTE - If the related model has other required attributes that have no default values,
         * then there will be a problem saving this new model. This is too be resolved at some point.
         * If the value is not valid then an InvalidValueToSanitizeException is thrown.
         * @param mixed $value
         * @return sanitized value
         * @throws InvalidValueToSanitizeException
         * @throws NotFoundException
         * @throws NotSupportedException
         */
        public function sanitizeValue($value)
        {
            assert('is_string($this->attributeName) && $this->attributeName != "id"');
            if ($value == null)
            {
                return $value;
            }
            $modelClassName         = $this->modelClassName;
            $relationModelClassName = $modelClassName::getRelationModelClassName($this->attributeName);
            if ($this->mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID)
            {
                try
                {
                    if ((int)$value <= 0)
                    {
                        throw new NotFoundException();
                    }
                    return $relationModelClassName::getById((int)$value);
                }
                catch (NotFoundException $e)
                {
                    throw new InvalidValueToSanitizeException(Zurmo::t('ImportModule', 'Id specified did not match any existing records.'));
                }
            }
            elseif ($this->mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)
            {
                try
                {
                    return static::getModelByExternalSystemIdAndModelClassName($value, $relationModelClassName);
                }
                catch (NotFoundException $e)
                {
                    throw new InvalidValueToSanitizeException(Zurmo::t('ImportModule', 'Other Id specified did not match any existing records.'));
                }
            }
            else
            {
                if (!method_exists($relationModelClassName, 'getByName'))
                {
                    throw new NotSupportedException();
                }
                $modelsFound = $relationModelClassName::getByName($value);
                if (count($modelsFound) == 0)
                {
                    $newRelatedModel       = new $relationModelClassName();
                    $newRelatedModel->name = $value;
                    $saved = $newRelatedModel->save();
                    //Todo: need to handle this more gracefully. The use case where a related model is needed to be made
                    //but there are some required attributes that do not have defaults. As a result, since those extra
                    //defaults cannot be specified at this time, an error must be thrown.
                    if (!$saved)
                    {
                        throw new InvalidValueToSanitizeException(Zurmo::t('ImportModule',
                        'A new related model could not be created because there are unspecified required attributes on that related model.'));
                    }
                    return $newRelatedModel;
                }
                else
                {
                    return $modelsFound[0];
                }
            }
        }

        /**
         * Ensure the type is an accepted type.
         * @param unknown_type integer
         */
        protected function ensureTypeValueIsValid($type)
        {
            assert('$type == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID ||
                    $type == RelatedModelValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID ||
                    $type == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_NAME');
        }

        protected function assertMappingRuleDataIsValid()
        {
            assert('$this->mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID ||
                    $this->mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID ||
                    $this->mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_NAME');
        }

        protected function init()
        {
            parent::init();
            $modelClassName                = $this->modelClassName;
            $model                         = new $modelClassName(false);
            $this->attributeModelClassName = $this->resolveAttributeModelClassName($model, $this->attributeName);
            $attributeModelClassName       = $this->attributeModelClassName;
            $model                         = new $attributeModelClassName(false);
            if ($this->mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_NAME &&
               !$model->isAttribute("name"))
            {
                throw new NotSupportedException();
            }
            $this->maxNameLength = StringValidatorHelper::getMaxLengthByModelAndAttributeName($model, 'name');
        }

        protected function resolveForFoundModel()
        {
            if ($this->mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_NAME)
            {
                $label = Zurmo::t('ImportModule', 'Is an existing record and will be linked.');
                $this->analysisMessages[] = $label;
            }
        }

        protected function resolveForUnfoundModel(RedBean_OODBBean $rowBean)
        {
            if ($this->mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID ||
                $this->mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)
            {
                if ($rowBean->{$this->columnName} != null)
                {
                    $label = Zurmo::t('ImportModule', 'Was not found and this row will be skipped during import.');
                    $this->shouldSkipRow = true;
                    $this->analysisMessages[] = $label;
                }
            }
            else
            {
                if ($rowBean->{$this->columnName} != null)
                {
                    $label = Zurmo::t('ImportModule', 'Was not found and will create a new record during import.');
                    $this->analysisMessages[] = $label;
                }
            }
        }
    }
?>