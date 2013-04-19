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
     * Data analysis to check if a value should not be null or empty based on if the attribute is required or not.
     * Also looks at whether a default value is specified since if the value is empty or null, but a default value
     * is specified, then that will pass.
     */
    class RequiredSqlAttributeValueDataAnalyzer extends SqlAttributeValueDataAnalyzer
                                                implements LinkedToMappingRuleDataAnalyzerInterface
    {
        /**
         * @see LinkedToMappingRuleDataAnalyzerInterface::runAndMakeMessages()
         */
        public function runAndMakeMessages(AnalyzerSupportedDataProvider $dataProvider, $columnName,
            $mappingRuleType, $mappingRuleData)
        {
            assert('is_string($columnName)');
            assert('is_array($mappingRuleData)');
            $modelClassName = $this->modelClassName;
            $model = new $modelClassName(false);
            if (!$model->isAttributeRequired($this->attributeName))
            {
                return false;
            }
            $mappingRuleFormClassName  = $mappingRuleType . 'MappingRuleForm';
            $defaultValueAttributeName = $mappingRuleFormClassName::getAttributeName();
            if ($mappingRuleData[$defaultValueAttributeName] != null)
            {
                return;
            }
            $where = $columnName . ' IS NULL OR ' . $columnName . "=''";  // Not Coding Standard
            $count = $dataProvider->getCountByWhere($where);
            if ($count > 0)
            {
                $label   = '{count} value(s) are missing and are required. ';
                $label  .= 'These rows will be skipped on import.';
                $this->addMessage(Zurmo::t('ImportModule', $label, array('{count}' => $count)));
            }
        }
    }
?>