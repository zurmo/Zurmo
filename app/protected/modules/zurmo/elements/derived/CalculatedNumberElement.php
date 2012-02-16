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
     * Helper class for displaying a calculated number in the user interface.
     */
    class CalculatedNumberElement extends Element
    {
        /**
         * Instance of metadata associated with the specified attribute
         * @var CalculatedDerivedAttributeMetadata
         */
        protected $calculatedDerivedAttributeMetadata;

        /**
         * Calculated Numbers are always read-only so the editable control is not supported.
         * (non-PHPdoc)
         * @see Element::renderControlEditable()
         */
        protected function renderControlEditable()
        {
            throw new NotSupportedException();
        }

        /**
         * (non-PHPdoc)
         * @see Element::renderNonEditable()
         */
        protected function renderNonEditable()
        {
            assert('$this->attribute != null');
            assert('$this->model instanceof RedBeanModel');
            $this->calculatedDerivedAttributeMetadata = CalculatedDerivedAttributeMetadata::
                                                        getByNameAndModelClassName($this->attribute,
                                                                                   get_class($this->model));
            return parent::renderNonEditable();
        }

        /**
         * (non-PHPdoc)
         * @see Element::renderControlNonEditable()
         */
        protected function renderControlNonEditable()
        {
            $formula = $this->calculatedDerivedAttributeMetadata->getFormula();
            $content = CalculatedNumberUtil::calculateByFormulaAndModel($formula, $this->model);
            return Yii::app()->format->text($content);
        }

        /**
         * (non-PHPdoc)
         * @see Element::renderLabel()
         */
        protected function renderLabel()
        {
            return $this->calculatedDerivedAttributeMetadata->getLabelByLanguage(Yii::app()->language);
        }

        /**
         * (non-PHPdoc)
         * @see Element::renderError()
         */
        protected function renderError()
        {
            throw new NotSupportedException();
        }

        /**
         * Calculated number is always read only.
         * @return true
         */
        public static function isReadOnly()
        {
            return true;
        }
    }
?>