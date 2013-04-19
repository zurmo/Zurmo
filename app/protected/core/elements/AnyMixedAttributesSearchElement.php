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
     * Element to render an input text box and scoping multiple-select checkbox interface.  A mixed attribute
     * search input allows a user to search multiple fields at the same time in a module.  This is similar to the
     * global search mechanism but only for a specific module.
     */
    class AnyMixedAttributesSearchElement extends TextElement
    {
        private $selectedValue = array('All');

        /**
         * Override to ensure the attributeName is anyMixedAttributes
         */
        protected function renderControlEditable()
        {
            assert('$this->model instanceof SearchForm');
            assert('$this->attribute = "anyMixedAttributes"');
            $content  = $this->renderSearchScopingInputContent();
            $content .= parent::renderControlEditable();
            $this->renderEditableScripts();
            return $content;
        }

        /**
         * (non-PHPdoc)
         * @see Element::getHtmlOptions()
         */
        protected function getHtmlOptions()
        {
            $htmlOptions             = array('class'   => 'input-hint anyMixedAttributes-input',
                                             'onfocus' => '$(this).select();',
                                             'size'    => 80);
            return array_merge(parent::getHtmlOptions(), $htmlOptions);
        }

        /**
         * (non-PHPdoc)
         * @see TextElement::renderControlNonEditable()
         */
        protected function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

        /**
         * (non-PHPdoc)
         * @see Element::renderLabel()
         */
        protected function renderLabel()
        {
            return null;
        }

        protected function renderSearchScopingInputContent()
        {
            $cClipWidget   = new CClipWidget();
            $cClipWidget->beginClip("ScopedJuiMultiSelect");
            $cClipWidget->widget('application.core.widgets.ScopedSearchJuiMultiSelect', array(
                'dataAndLabels'  => $this->model->getGlobalSearchAttributeNamesAndLabelsAndAll(),
                'selectedValue'  => $this->selectedValue,
                'inputId'        => $this->getEditableInputId(SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME),
                'inputName'      => $this->getEditableInputName(SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME),
                'options'        => array(
                                          'selectedText' => '',
                                          'noneSelectedText' => '', 'header' => false,
                                          ),
                'htmlOptions'    => array('class' => 'ignore-style ignore-clearform')
            ));
            $cClipWidget->endClip();
            $content = $cClipWidget->getController()->clips['ScopedJuiMultiSelect'];
            return $content;
        }

        /**
         * On keyUp, the search should be conducted.
         */
        protected function renderEditableScripts()
        {
            $inputId = $this->getEditableInputId();
            $script   = " basicSearchQueued = 0;";
            $script  .= " basicSearchOldValue = '';";
            $script  .= "   var basicSearchHandler = function(event)
                            {
                                if ($(this).val() != '')
                                {
                                    if (basicSearchOldValue != $(this).val())
                                    {
                                        basicSearchOldValue = $(this).val();
                                        basicSearchQueued = basicSearchQueued  + 1;
                                        setTimeout('basicSearchQueued = basicSearchQueued - 1', 900);
                                        setTimeout('searchByQueuedSearch(\"" . $inputId . "\")', 1000);
                                    }
                                }
                            }
                            $('#" . $inputId . "').unbind('input.ajax propertychange.ajax keyup.ajax');
                            $('#" . $inputId . "').bind('input.ajax propertychange.ajax keyup.ajax', basicSearchHandler);
                            ";
            Yii::app()->clientScript->registerScript('basicSearchAjaxSubmit', $script);
        }

        protected function getSelectedValue()
        {
            return $this->selectedValue;
        }

        public function setSelectedValue(Array $selectedValue)
        {
            assert('is_array($selectedValue)');
            $this->selectedValue = $selectedValue;
        }
    }
?>