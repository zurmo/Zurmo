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
     * Display a drop down of contact states.
     * This element is used by the designer for managing
     * the contact state attribute.
     */
    class AllContactStatesDropDownElement extends StaticDropDownFormElement implements DerivedElementInterface
    {
        /**
         * Override because in the user interface, the dynamic way in which this drop down is changed based on changes
         * in the user interface relies on the id of the drop down being a structured a certain way.
         * The example usage is in the designer tool -> Contacts -> fields -> status -> edit.
         * @see DropDownElement::getIdForSelectInput()
         */
        protected function getIdForSelectInput()
        {
            return $this->getEditableInputId($this->attribute);
        }

        protected function renderControlNonEditable()
        {
            $relatedAttributeName = $this->getRelatedAttributeName();
            assert('$relatedAttributeName != null');
            return parent::renderControlNonEditable();
        }

        protected function getRelatedAttributeName()
        {
            if (isset($this->params['relatedAttributeName']))
            {
                return $this->params['relatedAttributeName'];
            }
            return null;
        }

        protected function getDropDownArray()
        {
            $relatedAttributeName = $this->getRelatedAttributeName();
            assert('$relatedAttributeName != null');
            $dropDownArray = $this->model->{$relatedAttributeName};
            if ($dropDownArray == null)
            {
                return array();
            }
            return $dropDownArray;
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element.
         * @return array of model attributeNames used.
         */
        public static function getModelAttributeNames()
        {
            return array(
                'state',
            );
        }
    }
?>
