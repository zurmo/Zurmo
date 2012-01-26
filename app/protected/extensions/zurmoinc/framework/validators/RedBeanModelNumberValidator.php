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
     * RedBeanModel version of CNumberValidator.
     * Adds precision validation.
     * See the yii documentation.
     */
    class RedBeanModelNumberValidator extends CNumberValidator
    {
        /**
         * @var integer Precision as defined by the Php round() method. Defaults to null, meaning no precision.
         */
        public $precision = null;

        /**
         * @var string user-defined error message used when the value is too big.
         */
        public $tooPrecise = null;

        /**
         * See the yii documentation.
         */
        // The RedBeanModel is commented out here because the method
        // definition must match that of the base class.
        protected function validateAttribute(/*RedBeanModel*/ $model, $attributeName)
        {
            $value = $model->$attributeName;
            if ($this->allowEmpty && $this->isEmpty($value))
            {
                return;
            }
            parent::validateAttribute($model, $attributeName);
            if ($this->precision !== null)
            {
                assert('is_int($this->precision)');
                if (($value - round($value, $this->precision)) != 0)
                {
                    $message = $this->tooPrecise !== null ? $this->tooPrecise : Yii::t('Default', '{attribute} is too precise (maximum decimal places is {precision}).');
                    $this->addError($model, $attributeName, $message, array('{precision}' => $this->precision));
                }
            }
        }
    }
?>
