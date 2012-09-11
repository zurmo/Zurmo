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
     * Element to render an input text box and scoping multiple-select checkbox interface.  A mixed attribute
     * search input allows a user to search multiple fields at the same time in a module.  This is similar to the
     * global search mechanism but only for a specific module.
     */
    class MixedModelsSearchElement extends AnyMixedAttributesSearchElement
    {
        private $value = null;

        /**
         * Override
         */
        protected function renderControlEditable()
        {
            $content  = $this->renderSearchScopingInputContent();
            //$content .= parent::renderControlEditable();
            $htmlOptions             = array();
            $htmlOptions['id']       = $this->getEditableInputId();
            $htmlOptions['name']     = $this->getEditableInputName();
            $htmlOptions['disabled'] = $this->getDisabledValue();
            $htmlOptions['value']    = $this->getValue();
            $htmlOptions             = array_merge($this->getHtmlOptions(), $htmlOptions);
            $content .= $this->form->textField($this->model, $this->attribute, $htmlOptions);
            $this->renderEditableScripts();
            return $content;
        }

        protected function getHtmlOptions()
        {
            $htmlOptions             = array('class'   => 'input-hint mixedModels-input',
                                             'onfocus' => '$(this).select();',
                                             'size'    => 20);
            return array_merge(parent::getHtmlOptions(), $htmlOptions);
        }

        /**
         * Override so On keyUp, the search should not be conducted.
         */
        protected function renderEditableScripts()
        {
            return null;
        }

        protected function getValue()
        {
            return $this->value;
        }

        public function setValue($value)
        {
            assert('is_string($value)');
            $this->value = $value;
        }
    }
?>