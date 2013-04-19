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
     * Override to accomodate a value type of 'ZURMO_MODEL_NAME'. This would represent a model 'name' attribute value
     * that can be used as a unique identifier to map to existing models. An example is when importing a contact, and
     * the account name is provided. If the name is found, then the contact will be connected to the existing account
     * otherwise a new account is created with the name provided.
     * @see IdValueTypeBatchAttributeValueDataAnalyzer
     */
    class RelatedModelNameOrIdValueTypeBatchAttributeValueDataAnalyzer extends IdValueTypeBatchAttributeValueDataAnalyzer
    {
        /**
         * The max allowed length of the name.
         * @var integer
         */
        protected $maxNameLength;

        /**
         * If the name provide is larger than the $maxNameLength
         * @var string
         */
        const NEW_NAME_TO0_LONG = 'New name too long';

        /**
         * Override to ensure the $attributeName is a single value and also to resolve the max name length.
         * @param string $modelClassName
         * @param string $attributeName
         */
        public function __construct($modelClassName, $attributeName)
        {
            parent:: __construct($modelClassName, $attributeName);
            assert('is_string($attributeName)');
            $attributeModelClassName                        = $this->attributeModelClassName;
            $model                                          = new $attributeModelClassName(false);
            assert('$model->isAttribute("name")');
            $this->maxNameLength                            = StringValidatorHelper::
                                                              getMaxLengthByModelAndAttributeName($model, 'name');
            $this->messageCountData[static::NEW_NAME_TO0_LONG] = 0;
        }

        /**
         * @see IdValueTypeBatchAttributeValueDataAnalyzer::ensureTypeValueIsValid()
         */
        protected function ensureTypeValueIsValid($type)
        {
            assert('$type == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID ||
                    $type == RelatedModelValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID ||
                    $type == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_NAME');
        }

        /**
         * @see IdValueTypeBatchAttributeValueDataAnalyzer::analyzeByValue()
         */
        protected function analyzeByValue($value)
        {
            $modelClassName = $this->attributeModelClassName;
            if ($this->type == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID)
            {
                $found = $this->resolveFoundIdByValue($value);
            }
            elseif ($this->type == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_NAME)
            {
                if ($value == null)
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
                    $modelsFound = $modelClassName::getByName($value);
                    if (count($modelsFound) == 0)
                    {
                        $found = false;
                        if (strlen($value) > $this->maxNameLength)
                        {
                            $this->messageCountData[static::NEW_NAME_TO0_LONG]++;
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
                $found = $this->resolveFoundExternalSystemIdByValue($value);
            }
            if ($found)
            {
                $this->messageCountData[static::FOUND]++;
            }
            else
            {
                $this->messageCountData[static::UNFOUND]++;
            }
            if ($this->type == IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)
            {
                if (strlen($value) > $this->externalSystemIdMaxLength)
                {
                    $this->messageCountData[static::EXTERNAL_SYSTEM_ID_TOO_LONG]++;
                }
            }
        }

        /**
         * @see IdValueTypeBatchAttributeValueDataAnalyzer::makeMessages()
         */
        protected function makeMessages()
        {
            if ($this->type == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID ||
               $this->type == RelatedModelValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)
            {
                $label   = Zurmo::t('ImportModule', '{found} record(s) will be updated and {unfound} record(s) will be skipped during import.',
                                             array('{found}'   => $this->messageCountData[static::FOUND],
                                                   '{unfound}' => $this->messageCountData[static::UNFOUND]));
            }
            else
            {
                $label   = Zurmo::t('ImportModule', '{found} record(s) will be updated and {unfound} record(s) will be created during the import.',
                                             array('{found}'   => $this->messageCountData[static::FOUND],
                                                   '{unfound}' => $this->messageCountData[static::UNFOUND]));
            }
            $this->addMessage($label);
            if ($this->messageCountData[static::NEW_NAME_TO0_LONG] > 0)
            {
                $label   = Zurmo::t('ImportModule', '{invalid} name value(s) is/are too long. These records will be skipped during import.',
                                             array('{invalid}' => $this->messageCountData[static::NEW_NAME_TO0_LONG]));
                $this->addMessage($label);
            }
            $this->resolveMakeExternalSystemIdTooLargeMessage();
        }
    }
?>