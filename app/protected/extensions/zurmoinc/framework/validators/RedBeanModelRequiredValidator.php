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
     * RedBeanModel version of CRequiredValidator.
     * Takes care of related models and leaves the
     * rest to CRequiredValidator.
     * See the yii documentation.
     */
    class RedBeanModelRequiredValidator extends CRequiredValidator
    {
        /**
         * See the yii documentation.
         */
        // The RedBeanModel is commented out here because the method
        // definition must match that of the base class.
        protected function validateAttribute(/*RedBeanModel*/ $model, $attributeName)
        {
            if ($model->isRelation($attributeName))
            {
                if ($this->requiredValue !== null)
                {
                    if ($this->message !== null)
                    {
                        throw new NotImplementedException(); // TODO
                        $message = $this->message;
                    }
                    else
                    {
                        $message = Yii::t('yii', '{attribute} must be {value}.',
                                          array('{value}' => $this->requiredValue));
                    }
                    $this->addError($model, $attributeName, $message);
                }
                if ($model->$attributeName instanceof CustomField)
                {
                    if ($model->$attributeName->value === null)
                    {
                        if ($this->message !== null)
                        {
                            $message = $this->message;
                        }
                        else
                        {
                            $message = Yii::t('yii', '{attribute} is a CustomField that cannot be blank, implying that {attribute}\'s Value cannot be blank.');
                        }
                        $this->addError($model, $attributeName, $message);
                    }
                }
                elseif ($model->$attributeName instanceof FileContent)
                {
                    if ($model->$attributeName->content === null)
                    {
                        if ($this->message !== null)
                        {
                            $message = $this->message;
                        }
                        else
                        {
                            $message = Yii::t('yii', '{attribute} cannot be blank.');
                        }
                        $this->addError($model, $attributeName, $message);
                    }
                }
                elseif ( $model->$attributeName->id <= 0 && !$model->$attributeName->isModified() ||
                        !$model->$attributeName->validate())
                {
                    if ($this->message !== null)
                    {
                        $message = $this->message;
                    }
                    else
                    {
                        $message = Yii::t('yii', '{attribute} cannot be blank.');
                    }
                    $this->addError($model, $attributeName, $message);
                }
            }
            else
            {
                parent::validateAttribute($model, $attributeName);
            }
        }
    }
?>
