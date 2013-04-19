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

    /**
     * Validator for comparing date time strings against each other.
     */
    class RedBeanModelCompareDateTimeValidator extends CValidator
    {
        /**
         * 'after' or 'before'. Compare that a date time string occurs after or before another date time string.
         * @var string
         */
        public $type;

        /**
         * Date time string to compare against.
         * @var string
         */
        public $compareAttribute;

        /**
         * Validates a date time attribute on a model.  Compares either an attribute value is larger or smaller
         * than another date time attribute.
         * If there is any error, the error message is added to the model.
         * @param RedBeanModel $model the model being validated
         * @param string $attribute the attribute being validated
         */
        protected function validateAttribute($object, $attribute)
        {
            assert('$object instanceof RedBeanModel');
            assert('$this->type == "after" || $this->type == "before"');
            assert('is_string($this->compareAttribute)');
            if ($object->$attribute != null && $object->{$this->compareAttribute} != null)
            {
                $firstDateTime = DateTimeUtil::convertDbFormatDateTimeToTimestamp($object->$attribute);
                $secondDateTime = DateTimeUtil::convertDbFormatDateTimeToTimestamp($object->{$this->compareAttribute});
                if ($this->type === 'before')
                {
                    if ($firstDateTime > $secondDateTime)
                    {
                        $this->addError($object, $attribute,
                            Zurmo::t('Core', 'firstDateTime must occur before secondDateTime'),
                            array(  'firstDateTime'  => $object->getAttributeLabel($attribute),
                                    'secondDateTime' => $object->getAttributeLabel($this->compareAttribute)));
                    }
                }
                elseif ($this->type === 'after')
                {
                    if ($firstDateTime < $secondDateTime)
                    {
                        $this->addError($object, $attribute,
                            Zurmo::t('Core', 'firstDateTime must occur after secondDateTime'),
                            array(  'firstDateTime'  => $object->getAttributeLabel($attribute),
                                    'secondDateTime' => $object->getAttributeLabel($this->compareAttribute)));
                    }
                }
            }
        }
    }
?>
