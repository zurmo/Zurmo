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

    class SanitizerUtil
    {
        public static function getType()
        {
            $type = get_called_class();
            $type = substr($type, 0, strlen($type) - strlen('SanitizerUtil'));
            return $type;
        }

        public static function supportsSqlAttributeValuesDataAnalysis()
        {
            return true;
        }

        public static function supportsBatchAttributeValuesDataAnalysis()
        {
            return true;
        }

        public static function supportsDataAnalysis()
        {
            return true;
        }

        public static function makeSqlAttributeValueDataAnalyzer($modelClassName, $attributeNameOrNames)
        {
            assert('is_string($modelClassName)');
            assert('is_array($attributeNameOrNames) || is_string($attributeNameOrNames)');
            $sqlAttributeValueDataAnalyzerType = static::getSqlAttributeValueDataAnalyzerType();
            if($sqlAttributeValueDataAnalyzerType == null)
            {
                throw new NotSupportedException();
            }
            $sqlAttributeValueDataAnalyzerClassName = $sqlAttributeValueDataAnalyzerType .
                                                      'SqlAttributeValueDataAnalyzer';
            return new $sqlAttributeValueDataAnalyzerClassName($modelClassName, $attributeNameOrNames);
        }

        public static function makeBatchAttributeValueDataAnalyzer($modelClassName, $attributeNameOrNames)
        {
            assert('is_string($modelClassName)');
            assert('is_array($attributeNameOrNames) || is_string($attributeNameOrNames)');
            $batchAttributeValueDataAnalyzerType = static::getBatchAttributeValueDataAnalyzerType();
            if($batchAttributeValueDataAnalyzerType == null)
            {
                throw new NotSupportedException();
            }
            $batchAttributeValueDataAnalyzerClassName = $batchAttributeValueDataAnalyzerType .
                                                        'BatchAttributeValueDataAnalyzer';
            return new $batchAttributeValueDataAnalyzerClassName($modelClassName, $attributeNameOrNames);
        }

        public static function getSqlAttributeValueDataAnalyzerType()
        {
            return null;
        }

        public static function getBatchAttributeValueDataAnalyzerType()
        {
            return null;
        }

        public static function getLinkedMappingRuleType()
        {
            return null;
        }
    }
?>