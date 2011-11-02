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
     * This validator can be used by both a User model as well as a CFormModel like in User import for example.
     * This validator validates to see if a valid time zone is entered by validating it with DateTimeZone class.
     * See the yii documentation.
     */     
    class validateTimeZone extends CValidator
    {
        /**
         * See the yii documentation.
         */
        protected function validateAttribute($model, $attributeName)
        {
            if ($model->$attributeName != null)
            {
                try
                {
                    if (new DateTimeZone($model->$attributeName) === false)
                    {
                        $model->addError($attributeName, Yii::t('Default', 'The time zone is invalid.'));
                    }
                }
                catch (Exception $e)
                {
                    //Need to set UTC instead of checking validity of time zone to properly handle db auto build.
                    $model->$attributeName == 'UTC';
                }
            }
        }
    }
?>