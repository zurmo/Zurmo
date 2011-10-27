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
     * RedBeanModel version of CUniqueValidator.
     * See the yii documentation.
     */
    class RedBeanModelUniqueValidator extends CValidator
    {
        /**
         * See the yii documentation.
         */
        public $caseSensitive = true;

        /**
         * See the yii documentation.
         */
        public $allowEmpty = true;

        /**
         * See the yii documentation.
         */
        //public $className;

        /**
         * See the yii documentation.
         */
        public $attributeName;

        /**
         * See the yii documentation.
         */
        public $criteria = array();

        /**
         * See the yii documentation.
         */
        public $message;

        /**
         * See the yii documentation.
         */
        public $skipOnError = true;

        /**
         * See the yii documentation.
         */
        // The RedBeanModel is commented out here because the method
        // definition must match that of the base class.
        protected function validateAttribute(/*RedBeanModel*/ $model, $attributeName)
        {
            assert('$model instanceof RedBeanModel');
            assert('is_string($attributeName)');
            assert("\$model->isAttribute('$attributeName')");
            $value = $model->$attributeName;
            if ($this->allowEmpty && $this->isEmpty($value))
            {
                return;
            }
            if (!$model->isUniqueAttributeValue($attributeName, $value))
            {
                if ($this->message !== null)
                {
                    $message = $this->message;
                }
                else
                {
                    $message = Yii::t('Default', '{attribute} "{value}" is already in use.');
                }
                $this->addError($model, $attributeName, $message, array('{value}' => $value));
            }
        }
    }
?>
