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
     * Sanitizer for resolving if an attribute is required or not and whether the value is present.
     */
    class RequiredSanitizerUtil extends SanitizerUtil
    {
        public static function getLinkedMappingRuleType()
        {
            return 'DefaultValueModelAttribute';
        }

        /**
         * If a required value is missing or invalid, then skip the entire row during import.
         */
        public static function shouldNotSaveModelOnSanitizingValueFailure()
        {
            return true;
        }

        /**
         * @param RedBean_OODBBean $rowBean
         */
        public function analyzeByRow(RedBean_OODBBean $rowBean)
        {
            if (isset($this->mappingRuleData['defaultValue']) && $this->mappingRuleData['defaultValue'] != null)
            {
                return;
            }
            $modelClassName  = $this->modelClassName;
            $model = new $modelClassName(false);
            if (!$model->isAttributeRequired($this->attributeName))
            {
                return;
            }
            if ($rowBean->{$this->columnName} == null)
            {
                $label = Zurmo::t('ImportModule', 'Is  required.');
                $this->shouldSkipRow      = true;
                $this->analysisMessages[] = $label;
            }
        }

        /**
         * Resolves that the value is not null or the value is null and a valid default value is available for
         * the model id. If not, then an InvalidValueToSanitizeException is thrown.
         * @param mixed $value
         * @return sanitized value
         * @throws InvalidValueToSanitizeException
         */
        public function sanitizeValue($value)
        {
            if ($value != null)
            {
                return $value;
            }
            if (isset($this->mappingRuleData['defaultValue']) && $this->mappingRuleData['defaultValue'] != null)
            {
                return $this->mappingRuleData['defaultValue'];
            }
            else
            {
                $modelClassName  = $this->modelClassName;
                $model = new $modelClassName(false);
                if (!$model->isAttributeRequired($this->attributeName))
                {
                    return $value;
                }
                throw new InvalidValueToSanitizeException(Zurmo::t('ImportModule',
                          'This field is required and neither a value nor a default value was specified.'));
            }
        }

        public function shouldSanitizeValue()
        {
            return true;
        }
    }
?>