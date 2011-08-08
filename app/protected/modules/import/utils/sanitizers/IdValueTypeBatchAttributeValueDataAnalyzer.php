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

    class IdValueTypeBatchAttributeValueDataAnalyzer extends BatchAttributeValueDataAnalyzer
                                                              implements LinkedToMappingRuleDataAnalyzerInterface
    {
        protected $type;

        protected $attributeModelClassName;

        public function __construct($modelClassName, $attributeNameOrNames)
        {
            parent:: __construct($modelClassName, $attributeNameOrNames);
            assert('count($this->attributeNameOrNames) == 1');
            $model                         = new $modelClassName(false);
            $this->maxLength               = StringValidatorHelper::
                                             getMaxLengthByModelAndAttributeName($model, $attributeNameOrNames[0]);
            $this->attributeModelClassName = $this->resolveAttributeModelClassName($model,$this->attributeNameOrNames[0]);
        }
        public function runAndGetMessage(AnalyzerSupportedDataProvider $dataProvider, $columnName,
                                         $mappingRuleType, $mappingRuleData)
        {
            assert('is_string($columnName)');
            assert('is_string($mappingRuleType)');
            assert('is_array($mappingRuleData)');
            assert('is_int($mappingRuleData["type"])');
            assert('count($this->attributeNameOrNames) == 1');
            $this->ensureTypeValueIsValid($mappingRuleData["type"]);
            $this->type = $mappingRuleData["type"];
            if($this->type == IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)
            {
                RedBean_Plugin_Optimizer_ExternalSystemId::
                ensureColumnIsVarchar100(User::getTableName($this->attributeModelClassName), 'externalSystemId');
            }
            return $this->processAndGetMessage($dataProvider, $columnName);
        }

        protected function ensureTypeValueIsValid($type)
        {
            assert('$type == IdValueTypeMappingRuleForm::ZURMO_MODEL_ID ||
                    $type == IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID');
        }

        protected function resolveAttributeModelClassName(RedBeanModel $model, $attributeName)
        {
            if ($attributeName == 'id')
            {
                return get_class($model);
            }
            return $model->getRelationModelClassName($attributeName);
        }

        protected function processAndGetMessage(AnalyzerSupportedDataProvider $dataProvider, $columnName)
        {
            assert('is_string($columnName)');

            $page    = 0;
            $unfound = 0;
            $found   = 0;
            $dataProvider->getPagination()->setCurrentPage($page);
            while(null != $data = $dataProvider->getData())
            {
                $data = $dataProvider->getData(true);
                foreach($data as $rowData)
                {
                    if($this->analyzeByValue($rowData->$columnName))
                    {
                        $found ++;
                    }
                    else
                    {
                        $unfound ++;
                    }
                }
                if(count($data) == $dataProvider->getPagination()->getPageSize())
                {
                    $page ++;
                    $dataProvider->getPagination()->setCurrentPage($page);
                }
                else
                {
                    break;
                }
            }
            return $this->getMessageByFoundAndUnfoundCount($found, $unfound);
        }

        protected function analyzeByValue($value)
        {
            $modelClassName = $this->attributeModelClassName;
            if($this->type == IdValueTypeMappingRuleForm::ZURMO_MODEL_ID)
            {
                return $this->resolveFoundIdByValue($value);
            }
            else
            {
                return $this->resolveFoundExternalSystemIdByValue($value);
            }
        }

        protected function resolveFoundIdByValue($value)
        {
            $modelClassName = $this->attributeModelClassName;
            $sql = 'select id from ' . $modelClassName::getTableName($modelClassName) .
            ' where id = ' . $value . ' limit 1';
            $ids =  R::getCol($sql);
            assert('count($ids) <= 1');
            if(count($ids) == 0)
            {
                return false;
            }
            return true;
        }

        protected function resolveFoundExternalSystemIdByValue($value)
        {
            $modelClassName = $this->attributeModelClassName;
            $sql = 'select id from ' . $modelClassName::getTableName($modelClassName) .
            ' where externalSystemId = \'' . $value . '\' limit 1';
            $ids =  R::getCol($sql);
            assert('count($ids) <= 1');
            if(count($ids) == 0)
            {
                return false;
            }
            return true;
        }

        protected function getMessageByFailedCount($failed)
        {
            throw new NotSupportedException();
        }

        protected function getMessageByFoundAndUnfoundCount($found, $unfound)
        {
            $label   = '{found} record(s) will be updated ';
            $label  .= 'and {unfound} record(s) will be skipped during import.';
            return Yii::t('Default', $label, array('{found}' => $found, '{unfound}' => $unfound));
        }
    }
?>