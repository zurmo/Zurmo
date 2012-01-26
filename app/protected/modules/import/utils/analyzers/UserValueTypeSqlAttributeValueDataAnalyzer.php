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
     * Data analysis for attributes that are user model types.
     */
    class UserValueTypeSqlAttributeValueDataAnalyzer extends SqlAttributeValueDataAnalyzer
                                                     implements LinkedToMappingRuleDataAnalyzerInterface
    {
        /**
         * @see LinkedToMappingRuleDataAnalyzerInterface::runAndMakeMessages()
         */
        public function runAndMakeMessages(AnalyzerSupportedDataProvider $dataProvider, $columnName,
                                         $mappingRuleType, $mappingRuleData)
        {
            assert('is_string($columnName)');
            assert('is_string($mappingRuleType)');
            assert('is_array($mappingRuleData)');
            assert('is_int($mappingRuleData["type"])');
            assert('$mappingRuleData["type"] == UserValueTypeModelAttributeMappingRuleForm::ZURMO_USER_ID ||
                    $mappingRuleData["type"] == UserValueTypeModelAttributeMappingRuleForm::EXTERNAL_SYSTEM_USER_ID ||
                    $mappingRuleData["type"] == UserValueTypeModelAttributeMappingRuleForm::ZURMO_USERNAME');
            if ($mappingRuleData['type'] == UserValueTypeModelAttributeMappingRuleForm::ZURMO_USER_ID)
            {
                $this->resolveForTypeZurmoUserId($dataProvider, $columnName);
            }
            elseif ($mappingRuleData['type'] == UserValueTypeModelAttributeMappingRuleForm::EXTERNAL_SYSTEM_USER_ID)
            {
                $this->resolveForTypeExternalSystemId($dataProvider, $columnName);
            }
            else
            {
                $this->resolveForTypeUsername($dataProvider, $columnName);
            }
        }

        /**
         * Check whether the value specified is a valid zurmo user model id.
         * @param object $dataProvider
         * @param string $columnName
         */
        protected function resolveForTypeZurmoUserId(AnalyzerSupportedDataProvider $dataProvider, $columnName)
        {
            assert('is_string($columnName)');
            $userIds = UserValueTypeSanitizerUtil::getUserIds();
            $data  = $dataProvider->getCountDataByGroupByColumnName($columnName);
            $count    = 0;
            $rowCount = 0;
            foreach ($data as $valueCountData)
            {
                if ($valueCountData[$columnName] == null)
                {
                    continue;
                }
                if (!in_array($valueCountData[$columnName], $userIds))
                {
                    $count++;
                    $rowCount = $rowCount + $valueCountData['count'];
                }
            }
            if ($count > 0)
            {
                $label   = '{count} zurmo user id(s) across {rowCount} row(s) were not found. ';
                $label  .= 'These values will not be used during the import.';
                $this->addMessage(Yii::t('Default', $label, array('{count}' => $count, '{rowCount}' => $rowCount)));
            }
        }

        /**
         * Check whether the value is a valid external system id.
         * @param object $dataProvider
         * @param string $columnName
         */
        protected function resolveForTypeExternalSystemId(AnalyzerSupportedDataProvider $dataProvider, $columnName)
        {
            assert('is_string($columnName)');
            $userIds = UserValueTypeSanitizerUtil::getUserExternalSystemIds();
            $data  = $dataProvider->getCountDataByGroupByColumnName($columnName);
            $count = 0;
            foreach ($data as $valueCountData)
            {
                if ($valueCountData[$columnName] == null)
                {
                    continue;
                }
                if (!in_array($valueCountData[$columnName], $userIds))
                {
                    $count++;
                }
            }
            if ($count > 0)
            {
                $label   = '{count} external system user id(s) specified were not found. ';
                $label  .= 'These values will not be used during the import.';
                $this->addMessage(Yii::t('Default', $label, array('{count}' => $count)));
            }
        }

        /**
         * Check whether the specified value corresponds to a valid username of a user model.
         * @param AnalyzerSupportedDataProvider $dataProvider
         * @param string $columnName
         */
        protected function resolveForTypeUsername(AnalyzerSupportedDataProvider $dataProvider, $columnName)
        {
            assert('is_string($columnName)');
            $usernameValues = UserValueTypeSanitizerUtil::getUsernames();
            $usernameValues = ArrayUtil::resolveArrayToLowerCase($usernameValues);
            $data  = $dataProvider->getCountDataByGroupByColumnName($columnName);
            $count = 0;
            foreach ($data as $valueCountData)
            {
                if ($valueCountData[$columnName] == null)
                {
                    continue;
                }
                if (!in_array(mb_strtolower($valueCountData[$columnName]), $usernameValues))
                {
                    $count++;
                }
            }
            if ($count > 0)
            {
                $label   = '{count} username(s) specified were not found. ';
                $label  .= 'These values will not be used during the import.';
                $this->addMessage(Yii::t('Default', $label, array('{count}' => $count)));
            }
        }
    }
?>