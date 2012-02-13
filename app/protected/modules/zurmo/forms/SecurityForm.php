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
     * Form for displaying the policies, rights, and module permissions in the
     * administrative user interface.
     */
    abstract class SecurityForm extends ConfigurableMetadataModel
    {
        public $data;

        /**
         * Override to accomodate special attributes on this form.
         * Attribute data is stored in the data array. In order
         * to get an attribute if you use $name = UsersModule__POLICYA
         * then it will look for this attribute in: $data['UsersModule']['POLICYA']
         * and retrieve the 'explicit' value.
         *
         * Optionally you can specify UsersModule__POLICYA__inherited which will
         * retrieve the 'inherited' value instead of the explicit value.
         * @see FormModelUtil::DELIMITER
         * @return string
         */
        public function __get($name)
        {
            if (property_exists($this, $name))
            {
                return $this->$name;
            }
            $delimiter = FormModelUtil::DELIMITER;
            $name      = $this->resolveNameForDelimiterSplit($name, $delimiter);
            list($moduleName, $securityItem, $type) = explode($delimiter, $name);
            return $this->getPropertyFromData($moduleName, $securityItem, $type);
        }

        /**
         * Takes a name string and appends a second delimiter to the string
         * if it does not exist.
         * @return name string
         */
        public static function resolveNameForDelimiterSplit($name, $delimiter)
        {
            assert('substr_count($name, $delimiter) == 1 || substr_count($name, $delimiter) == 2');
            if (substr_count($name, $delimiter) == 1)
            {
                $name .= $delimiter;
            }
            return $name;
        }

        protected function getPropertyFromData($moduleName, $securityItem, $type)
        {
            assert('$type == null || $type == "inherited" || $type == "helper" ||
                $type == "effective" || $type == "actual"');
            if (isset($this->data[$moduleName]))
            {
                foreach ($this->data[$moduleName] as $tempSecurity => $securityInformation)
                {
                    if ($tempSecurity == $securityItem)
                    {
                        if     ($type == 'inherited')
                        {
                            return $securityInformation['inherited'];
                        }
                        elseif ($type == 'actual')
                        {
                            return $securityInformation['actual'];
                        }
                        elseif ($type == 'effective')
                        {
                            return $securityInformation['effective'];
                        }
                        elseif ($type == 'helper')
                        {
                            if (isset($securityInformation['helper']))
                            {
                                return $securityInformation['helper'];
                            }
                            return null;
                        }
                        return $securityInformation['explicit'];
                    }
                }
            }
            throw new CException(Yii::t('yii', 'Property "{class}.{property}" is not defined.',
                array('{class}'  => get_class($this),
                    '{property}' => $moduleName . FormModelUtil::DELIMITER . $securityItem))
            );
        }

        /**
         * Populate both the standard attribute in the data array
         * as well as the 'effective' and 'actual' attribute index which is expected
         * to be available in this data.
         * @return array;
         */
        public function attributeLabels()
        {
            $labels = array();
            foreach ($this->data as $moduleName => $items)
            {
                foreach ($items as $item => $information)
                {
                    $attributeName          = FormModelUtil::getDerivedAttributeNameFromTwoStrings(
                                                $moduleName,
                                                $item);
                    $labels[$attributeName] = $this->resolveLabelfromData($information);
                    $labels[$attributeName . FormModelUtil::DELIMITER . 'effective'] = $labels[$attributeName];
                    $labels[$attributeName . FormModelUtil::DELIMITER . 'actual']    = $labels[$attributeName];
                }
            }
            return $labels;
        }

        protected function resolveLabelfromData($information)
        {
            return Yii::t('Default', $information['displayName']);
        }

        /**
         * Validator used to compare a dropdown value against a text input box.
         * If the dropdown value is a certain value, then the input box shouldb
         * be made required.
         */
        public function validateIsRequiredByComparingHelper($attribute, $params)
        {
            if ($this->{$params['compareAttributeName']} != null && $this->{$attribute} == null)
            {
                $this->addError($attribute, Yii::t('Default', 'You must specify a value.'));
            }
        }
    }
?>