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
     * Data analyzer for process minimum length.  Determines if a value is too shorte and adds messages about how the row
     * will be ignored on import.
     */
    class MinimumLengthSqlAttributeValueDataAnalyzer extends SqlAttributeValueDataAnalyzer
                                                implements DataAnalyzerInterface
    {
        /**
         * @see DataAnalyzerInterface::runAndMakeMessages()
         */
        public function runAndMakeMessages(AnalyzerSupportedDataProvider $dataProvider, $columnName)
        {
            assert('is_string($columnName)');
            assert('is_string($this->attributeName)');
            $minLength = $this->resolveMinLength($this->modelClassName, $this->attributeName);
            if ($minLength == null)
            {
                return;
            }
            $where = static::resolvColumnNameSqlLengthFunction($columnName) . ' < ' . $minLength;
            $count = $dataProvider->getCountByWhere($where);
            if ($count > 0)
            {
                $label   = '{count} value(s) are too short for this field. ';
                $label  .= 'These rows will be skipped during import.';
                $this->addMessage(Zurmo::t('ImportModule', $label, array('{count}' => $count, '{length}' => $minLength)));
            }
        }

        /**
         * @param string $columnName
         */
        protected static function resolvColumnNameSqlLengthFunction($columnName)
        {
            assert('is_string($columnName)');
            return DatabaseCompatibilityUtil::charLength($columnName);
        }

        /**
         * Provided a model class name and an attribute name, get the min length for that attribute.
         * @param string $modelClassName
         * @param string $attributeName
         */
        protected function resolveMinLength($modelClassName, $attributeName)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName)');
            $model = new $modelClassName(false);
            return StringValidatorHelper::getMinLengthByModelAndAttributeName($model, $attributeName);
        }
    }
?>