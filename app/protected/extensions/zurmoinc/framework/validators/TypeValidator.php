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
     *
     * See the yii documentation.
     * Validates datetime as integer, since it is a timestamp
     * Validates date as a db formatted date string.
     */
    class TypeValidator extends CTypeValidator
    {
        /**
         * Validates the attribute of the model.
         * If there is any error, the error message is added to the model.
         * @param RedBeanModel $model the model being validated
         * @param string $attribute the attribute being validated
         */
        protected function validateAttribute($object, $attribute)
        {
            $value = $object->$attribute;
            if ($this->allowEmpty && $this->isEmpty($value))
            {
                return;
            }
            switch ($this->type)
            {
                case 'blob':
                case 'longblob':
                    return;

                case 'integer':
                    $valid = preg_match('/^[-+]?[0-9]+$/', trim($value)); // Not Coding Standard
                    break;

                case 'float':
                    $valid = preg_match('/^[-+]?([0-9]*\.)?[0-9]+([eE][-+]?[0-9]+)?$/', trim($value)); // Not Coding Standard
                    break;

                case 'date':
                    $valid = DateTimeUtil::isValidDbFormattedDate($value);
                    break;

                case 'time':
                    $valid = CDateTimeParser::parse($value, $this->timeFormat) !== false;
                    break;

                case 'datetime':
                    $valid = DateTimeUtil::isValidDbFormattedDateTime($value);
                    break;

                case 'array';
                    throw new NotSupportedException();

                case 'string';
                default:
                    return;
            }
            if (!$valid)
            {
                if ($this->message !== null)
                {
                    $message = $this->message;
                }
                else
                {
                    $message = Yii::t('yii', '{attribute} must be {type}.');
                }
                $this->addError($object, $attribute, $message, array('{type}' => $this->type));
            }
        }
    }
?>