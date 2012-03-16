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
     * RedBeanModel version of CDefaultValueValidator.
     * See the yii documentation.
     */
    class RedBeanModelDefaultValueValidator extends CDefaultValueValidator
    {
        /**
         * See the yii documentation.
         */
        // The RedBeanModel is commented out here because the method
        // definition must match that of the base class.
        protected function validateAttribute(/*RedBeanModel*/ $model, $attributeName)
        {
            if (!$this->setOnEmpty)
            {
                $model->$attributeName = $this->value;
            }
            else
            {
                $value = $model->$attributeName;

                $isEmptyAttribute            = $model->isAttribute($attributeName) &&
                                               ($value === null || $value === '');

                $isRelation = $model->isRelation($attributeName);

                $isNullRelatedModel               = $isRelation &&
                                                    $value === null;
                assert('!$isRelation || $value === null || $value->id != 0');
                $isNewUnmodifiedRelatedModel      = $isRelation                    &&
                                                    !$value instanceof CustomField &&
                                                    !$value instanceof MultipleValuesCustomField &&
                                                    $value !== null                &&
                                                    $value->id < 0                 &&
                                                    !$value->isModified();
                $isEmptyCustomField               = $isRelation                   &&
                                                    $value instanceof CustomField &&
                                                    ($value->value === null ||
                                                    $value->value === '');
                $isEmptyMultipleValuesCustomField = $isRelation                   &&
                                                    $value instanceof MultipleValuesCustomField &&
                                                    (count($value->values) == 0);

                // None or only one of these is true.
                assert('!($isEmptyAttribute            ||
                          $isNullRelatedModel          ||
                          $isNewUnmodifiedRelatedModel ||
                          $isEmptyMultipleValuesCustomField ||
                          $isEmptyCustomField) ||
                         ($isEmptyAttribute            ^
                          $isNullRelatedModel          ^
                          $isNewUnmodifiedRelatedModel ^
                          $isEmptyMultipleValuesCustomField ^
                          $isEmptyCustomField)');

                $thisValue = $this->calculate();

                if ($isEmptyAttribute   ||
                    $isNullRelatedModel ||
                    $isNewUnmodifiedRelatedModel)
                {
                    $model->$attributeName = $thisValue;
                }
                if ($isEmptyMultipleValuesCustomField)
                {
                    $customFieldValue        = new CustomFieldValue();
                    $customFieldValue->value = $thisValue;
                    $model->$attributeName->values->add($customFieldValue);
                }
                elseif ($isEmptyCustomField)
                {
                    if ($this->value instanceof CustomField)
                    {
                        $model->$attributeName = $thisValue;
                    }
                    else
                    {
                        $model->$attributeName->value = $this->value;
                    }
                }
            }
        }

        /**
         * To be overriden in deriving class. Must return
         * $this->value or calculate a something to return in
         * its place.
         */
        protected function calculate()
        {
            return $this->value;
        }
    }
?>