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
     * Helper functionality for use in determining the correct label to display
     * based on a dynamic piece of information.
     */
    class LabelUtil
    {
        /**
         * Returns either record or records depending on input count
         */
        public static function getUncapitalizedRecordLabelByCount($count)
        {
            assert('is_int($count)');
            if ($count > 1 || $count == 0)
            {
                return Zurmo::t('Core', 'records');
            }
            return Zurmo::t('Core', 'record');
        }

        /**
         * Returns either a singluar or plural model label depending on input count
         */
        public static function getUncapitalizedModelLabelByCountAndModelClassName($count, $modelClassName)
        {
            assert('is_int($count)');
            if ($count > 1 || $count == 0)
            {
                return $modelClassName::getModelLabelByTypeAndLanguage('PluralLowerCase');
            }
            return $modelClassName::getModelLabelByTypeAndLanguage('SingularLowerCase');
        }

        /**
         * Module translation parameters are used by Zurmo::t as the third parameter to define the module labels.  These
         * parameter values resolve any custom module label names that have been specified in the module metadata.
         * @return array of key/value module label pairings.
         * TODO: cache results after first retrieval on each page load. Potentially across mulitple page loads
         */
        public static function getTranslationParamsForAllModules()
        {
            return Yii::app()->languageHelper->getAllModuleLabelsAsTranslationParameters();
        }

        /**
         * Given a model class name and an attribute name, make a label that combines the translated model
         * and attribute names.
         * @param string $modelClassName
         * @param string $attributeName
         */
        public static function makeModelAndAttributeNameCombinationLabel($modelClassName, $attributeName)
        {
            $model = new $modelClassName(false);
            return $modelClassName::getModelLabelByTypeAndLanguage('Singular') .
                   ' - ' . $model->getAttributeLabel($attributeName);
        }
    }
?>
