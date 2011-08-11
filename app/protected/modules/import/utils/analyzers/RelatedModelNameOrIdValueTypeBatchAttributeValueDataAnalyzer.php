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
         * Override to ensure the $attributeNameOrNames is a single value and also to resolve the max name length.
         * @param string $modelClassName
         * @param string $attributeNameOrNames
         */
        public function __construct($modelClassName, $attributeNameOrNames)
        {
            parent:: __construct($modelClassName, $attributeNameOrNames);
            assert('count($this->attributeNameOrNames) == 1');
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
            if($this->type == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID)
            {
                $found = $this->resolveFoundIdByValue($value);
            }
            elseif($this->type == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_NAME)
            {
                $modelClassName = $this->attributeModelClassName;
                $sql = 'select name from ' . $modelClassName::getTableName($modelClassName) .
                " where name = " . DatabaseCompatibilityUtil::lower("'" . $value . "'") . " limit 1";
                $ids =  R::getCol($sql);
                if(count($ids) == 0)
                {
                    $found = false;
                    if(strlen($value) > $this->maxNameLength)
                    {
                        $this->messageCountData[static::NEW_NAME_TO0_LONG] ++;
                    }
                }
                else
                {
                    $found = true;
                }

            }
            else
            {
                $found = $this->resolveFoundExternalSystemIdByValue($value);
            }
            if($found)
            {
                $this->messageCountData[static::FOUND] ++;
            }
            else
            {
                $this->messageCountData[static::UNFOUND] ++;
            }
            if($this->type == IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)
            {
                if(strlen($value) > $this->externalSystemIdMaxLength)
                {
                    $this->messageCountData[static::EXTERNAL_SYSTEM_ID_TOO_LONG] ++;
                }
            }
        }

        /**
         * @see IdValueTypeBatchAttributeValueDataAnalyzer::makeMessages()
         */
        protected function makeMessages()
        {
            if($this->type == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID ||
               $this->type == RelatedModelValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)
            {
                $label   = '{found} record(s) will be updated ';
                $label  .= 'and {unfound} record(s) will be skipped during import.';
            }
            else
            {
                $label   = '{found} record(s) will be updated and ';
                $label  .= '{unfound} record(s) will be created during the import.';
            }
            $this->addMessage(Yii::t('Default', $label,
                              array('{found}' => $this->messageCountData[static::FOUND],
                                    '{unfound}' => $this->messageCountData[static::UNFOUND])));
            if($this->messageCountData[static::NEW_NAME_TO0_LONG] > 0)
            {
                $label   = '{invalid} name value(s) is/are too long.';
                $label  .= 'These records will be skipped during import.';
                $this->addMessage(Yii::t('Default', $label,
                              array('{invalid}' => $this->messageCountData[static::NEW_NAME_TO0_LONG])));
            }
            $this->resolveMakeExternalSystemIdTooLargeMessage();
        }
    }
?>