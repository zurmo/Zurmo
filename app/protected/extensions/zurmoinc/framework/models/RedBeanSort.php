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

    class RedBeanSort extends CSort
    {
        /**
         * Override so that the model can be retrieved using
         * redBean model and not CActiveRecord. Also
         * made 'id' attribute automatically return itself
         * since this is always an attribute on the model
         */
        public function resolveAttribute($attribute)
        {
            if ($this->attributes !== array())
            {
                $attributes = $this->attributes;
            }
            elseif ($attribute == 'id')
            {
                return $attribute;
            }
            elseif ($this->modelClass !== null)
            {
                $modelClass = $this->modelClass;
                $model = new $modelClass();
                $attributes = $model->attributeNames();
            }
            else
            {
                return false;
            }
            foreach ($attributes as $name => $definition)
            {
                if (is_string($name))
                {
                    if ($name === $attribute)
                    {
                        return $definition;
                    }
                }
                elseif ($definition === '*')
                {
                    if ($this->modelClass !== null)
                    {
                        $modelClass = $this->modelClass;
                        $model = new $modelClass();
                        if ($model->hasAttribute($attribute))
                        {
                            return $attribute;
                        }
                    }
                }
                elseif ($definition === $attribute)
                {
                    return $attribute;
                }
            }
            return false;
        }

        /**
         * Override so that the model can be retrieved using
         * redBean model and not CActiveRecord
         */
        public function resolveLabel($attribute)
        {
            $definition = $this->resolveAttribute($attribute);
            if (is_array($definition))
            {
                if (isset($definition['label']))
                {
                    return $definition['label'];
                }
            }
            elseif (is_string($definition))
            {
                $attribute = $definition;
            }
            if ($this->modelClass !== null)
            {
                $modelClass = $this->modelClass;
                $model = new $modelClass();
                return $model->getAttributeLabel($attribute);
            }
            else
            {
                return $attribute;
            }
        }
    }
?>