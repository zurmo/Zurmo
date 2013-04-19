<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class RedBeanSort extends CSort
    {
        private $sortAttribute;

        private $sortDescending;

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
                $modelClassName = $this->modelClass;
                $attributes     = $modelClassName::getAttributeNames();
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
                        $modelClassName = $this->modelClass;
                        if ($modelClassName::isAnAttribute($attribute))
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
                $modelClassName  = $this->modelClass;
                if ($modelClassName::getAbbreviatedAttributeLabel($attribute) != null)
                {
                    return $modelClassName::getAbbreviatedAttributeLabel($attribute);
                }
                else
                {
                    return $modelClassName::getAnAttributeLabel($attribute);
                }
            }
            else
            {
                return $attribute;
            }
        }

        /**
         * Override the default method to read from model in case attribute is not present in request
         * @return array sort directions indexed by attribute names.
         * Sort direction can be either CSort::SORT_ASC for ascending order or
         * CSort::SORT_DESC for descending order.
         */
        public function getDirections()
        {
            $directions = parent::getDirections();
            if (empty($directions))
            {
                $attributes = explode($this->separators[0], $this->sortAttribute . $this->sortDescending);
                foreach ($attributes as $attribute)
                {
                    if (($pos = strrpos($attribute, $this->separators[1])) !== false)
                    {
                        $descending = substr($attribute, $pos + 1) === $this->descTag;
                        if ($descending)
                        {
                            $attribute = substr($attribute, 0, $pos);
                        }
                    }
                    else
                    {
                        $descending = false;
                    }
                    if (($this->resolveAttribute($attribute)) !== false)
                    {
                        $directions[$attribute] = $descending;
                        if (!$this->multiSort)
                        {
                            return $directions;
                        }
                    }
                }
                if ($directions === array() && is_array($this->defaultOrder))
                {
                        $directions = $this->defaultOrder;
                }
            }

            return $directions;
        }

        public function getSortAttribute()
        {
            return $this->sortAttribute;
        }

        public function setSortAttribute($sortAttribute)
        {
            $this->sortAttribute = $sortAttribute;
        }

        public function getSortDescending()
        {
            return $this->sortDescending;
        }

        public function setSortDescending($sortDescending)
        {
            if ($sortDescending === true)
            {
                $this->sortDescending = ".desc";
            }
        }
    }
?>