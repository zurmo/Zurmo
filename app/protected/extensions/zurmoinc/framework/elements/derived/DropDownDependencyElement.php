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
     * Element to render a collection of dropdown elements that are connected together. This element renders a
     * dropDown dependency derived attribute.
     */
    class DropDownDependencyElement extends Element
    {
        /**
         * Instance of metadata associated with the specified attribute
         * @var DropDownDependencyDerivedAttributeMetadata
         */
        protected $dropDownDependencyDerivedAttributeMetadata;

        protected function makeMetadata()
        {
            assert('$this->attribute != null');
            assert('$this->model instanceof RedBeanModel');
            $this->dropDownDependencyDerivedAttributeMetadata = DropDownDependencyDerivedAttributeMetadata::
                                                                getByNameAndModelClassName($this->attribute,
                                                                                           get_class($this->model));
        }

        /**
         * (non-PHPdoc)
         * @see Element::renderNonEditable()
         */
        protected function renderEditable()
        {
            $this->makeMetadata();
            return parent::renderEditable();
        }

        /**
         * (non-PHPdoc)
         * @see Element::renderControlEditable()
         */
        protected function renderControlEditable()
        {
            $attributes = $this->dropDownDependencyDerivedAttributeMetadata->getUsedAttributeNames();
            $content    = "<table> \n";
            foreach($attributes as $attribute)
            {
                $element                    = new DropDownElement($this->model,
                                                                  $attribute,
                                                                  $this->form,
                                                                  array('addBlank' => true));
                $element->editableTemplate  = $this->getEditableTemplate();
                $content                   .= $element->render();
            }
            $content   .= "</table> \n";
            return $content;
        }

        /**
         * (non-PHPdoc)
         * @see Element::renderNonEditable()
         */
        protected function renderNonEditable()
        {
            $this->makeMetadata();
            return parent::renderNonEditable();
        }

        /**
         * (non-PHPdoc)
         * @see Element::renderControlNonEditable()
         */
        protected function renderControlNonEditable()
        {
            $attributes = $this->dropDownDependencyDerivedAttributeMetadata->getUsedAttributeNames();
            $content    = "<table> \n";
            foreach($attributes as $attribute)
            {
                $element                        = new DropDownElement($this->model,
                                                                  $attribute,
                                                                  $this->form);
                $element->nonEditableTemplate   = $this->getNonEditableTemplate();
                $content                       .= $element->render();
            }
            $content   .= "</table> \n";
            return $content;
        }

        /**
         * (non-PHPdoc)
         * @see Element::renderLabel()
         */
        protected function renderLabel()
        {
            return $this->dropDownDependencyDerivedAttributeMetadata->getLabelByLanguage(Yii::app()->language);
        }

        /**
         * (non-PHPdoc)
         * @see Element::renderError()
         */
        protected function renderError()
        {
            return null;
        }

        protected function getEditableTemplate()
        {
            $template  = "<tr><td style='border:0px;' nowrap='nowrap'>\n";
            $template .= "{label}";
            $template .= "</td><td width='100%' style='border:0px;'>\n";
            $template .= '&#160;{content}{error}';
            $template .= "</td></tr>\n";
            return $template;
        }

        protected function getNonEditableTemplate()
        {
            $template  = "<tr><td width='100%' style='border:0px;'>\n";
            $template .= '{label}&#160;{content}';
            $template .= "</td></tr>\n";
            return $template;
        }
    }
?>