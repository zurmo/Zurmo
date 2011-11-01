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
     * Helper class for working with CustomFieldData
     */
    class CustomFieldDataUtil
    {
        /**
         * Given a CustomFieldData object, return an array of data and translated labels indexed by the data name.
         * @param CustomFieldData $customFieldData
         * $param string $language
         */
        public static function getDataIndexedByDataAndTranslatedLabelsByLanguage(CustomFieldData $customFieldData, $language)
        {
            assert('is_string($language)');
            $dropDownArray       = unserialize($customFieldData->serializedData);
            $customLabels        = unserialize($customFieldData->serializedLabels);
            if (empty($dropDownArray))
            {
                return array();
            }
            $labelsArray = self::getDataLabelsByLanguage($dropDownArray, $language, $customLabels);
            return array_combine($dropDownArray, $labelsArray);
        }

        /**
         * Given an array of data names, a language, and an array of custom labels make an array of data names paired
         * with their labels.  If a custom label is available then utilize that for each data name, otherwise fallback
         * to using the messages file to translate the label.  If the messages file does not have a translation, then
         * the data name will be used as the label.
         * @param $data
         * @param $language
         * @return array - labels for each data name.
         */
        protected static function getDataLabelsByLanguage($data, $language, $customLabels)
        {
            assert('is_array($data)');
            assert('is_string($language)');
            assert('$customLabels == null || is_array($customLabels)');
            $labels = array();
            foreach ($data as $order => $dataName)
            {
                if (isset($customLabels[$language]) &&
                   isset($customLabels[$language][$order]) &&
                   $customLabels[$language][$order] != null)
                {
                    $labels[] = $customLabels[$language][$order];
                }
                else
                {
                    $labels[] = Yii::t('Default', $dataName, array(), null, $language);
                }
            }
            return $labels;
        }
    }
?>