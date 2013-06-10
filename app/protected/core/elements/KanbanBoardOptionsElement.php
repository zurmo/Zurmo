<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Element to renders a series of checkboxes representing the values available for display on the Kanban Board.
     * Additionally, renders a radio selection of available background themes.
     */
    class KanbanBoardOptionsElement extends Element
    {
        protected function renderControlEditable()
        {
            assert('$this->model instanceof SearchForm');
            assert('$this->attribute == null');
            assert('$this->model->getKanbanBoard() != null');
            $content     = ZurmoHtml::tag('div', array('class' => 'kanban-board-options-panel'), $this->renderSelectionContent());
            $content    .= ZurmoHtml::tag('div', array('class' => 'kanban-board-options-panel'), $this->renderThemeContent());
            $content     = ZurmoHtml::tag('div', array('class' => 'attributesContainer clearfix'), $content);
            $linkContent = $this->renderApplyResetContent() . $this->renderApplyLinkContent();
            $linkContent = ZurmoHtml::tag('div', array('class' => 'form-toolbar clearfix'), $linkContent);
            $this->registerEditableValuesScripts();
            $this->registerThemeScript();
            return $content . ZurmoHtml::tag('div', array('class' => 'view-toolbar-container'), $linkContent);
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

        protected function renderSelectionContent()
        {
            $content = ZurmoHtml::tag('h3', array(), Zurmo::t('Core', 'Visible Columns'));
            $content .= ZurmoHtml::checkBoxList(
                $this->getEditableInputName(KanbanBoard::GROUP_BY_ATTRIBUTE_VISIBLE_VALUES),
                $this->model->getKanbanBoard()->getGroupByAttributeVisibleValues(),
                $this->model->getKanbanBoard()->getGroupByDataAndTranslatedLabels(),
                $this->getEditableValuesHtmlOptions()
            );
            return $content;
        }

        /**
         * Renders the setting as a radio list.
         * @return A string containing the element's content.
         */
        protected function renderThemeContent()
        {
            $content = ZurmoHtml::tag('h3', array(), Zurmo::t('Core', 'Theme'));
            $content .= ZurmoHtml::radioButtonList(
                $this->getEditableInputName(KanbanBoard::SELECTED_THEME),
                $this->model->getKanbanBoard()->getSelectedTheme(),
                $this->model->getKanbanBoard()->getThemeNamesAndLabels(),
                $this->getEditableThemeHtmlOptions()
            );
            return $content;
        }

        protected function getEditableValuesHtmlOptions()
        {
            return array(
                'template'  => '<div class="multi-select-checkbox-input">{input}{label}</div>',
                'separator' => '',
                'id'        => $this->getEditableInputId(KanbanBoard::GROUP_BY_ATTRIBUTE_VISIBLE_VALUES),
                'class'     => 'ignore-clearform'
            );
        }

        protected function getEditableThemeHtmlOptions()
        {
            $htmlOptions             = array();
            $htmlOptions['id']       = $this->getEditableInputId(KanbanBoard::SELECTED_THEME);
            $htmlOptions['template'] =  '<div class="radio-input texture-swatch {value}">{input}<span class="background-texture-1">' .
                                        '</span>{label}</div>';
            $htmlOptions['class']    = 'ignore-clearform';
            return $htmlOptions;
        }

        /**
         * On keyUp, the search should be conducted.
         */
        protected function registerEditableValuesScripts()
        {
            $defaultSelectedAttributes = $this->model->getListAttributesSelector()->getMetadataDefinedListAttributeNames();
            // Begin Not Coding Standard
            Yii::app()->clientScript->registerScript('kanbanBoardOptionsScripts', "
                $('#kanban-board-options-reset').unbind('click.reset');
                $('#kanban-board-options-reset').bind('click.reset', function()
                    {
                        $('.kanban-board-options-view').hide();
                        var inputName = '" .$this->getEditableInputName(KanbanBoard::GROUP_BY_ATTRIBUTE_VISIBLE_VALUES) . "[]';
                        $('input[name=\"' + inputName + '\"]').each(function()
                        {
                            $(this).attr('checked', true);
                            $(this).parent().addClass('c_on');
                        });
                        $('input[name=\"" . $this->getEditableInputName(KanbanBoard::SELECTED_THEME) . "\"]').each(function()
                        {
                            if ($(this).val() == '')
                            {
                                $(this).attr('checked', true);
                            }
                            else
                            {
                                $(this).attr('checked', false);
                            }
                        });
                    }
                );");
            // End Not Coding Standard
        }

        public function registerThemeScript()
        {
            //todo: implement
            //return;
            $removeScript = null;
            foreach ($this->model->getKanbanBoard()->getThemeNamesAndLabels() as $value => $notUsed)
            {
                $removeScript .= '$("#kanban-holder").removeClass("' . $value . '");' . "\n";
            }
            // Begin Not Coding Standard
            $script = "$('input[name=\"" . $this->getEditableInputName(KanbanBoard::SELECTED_THEME) . "\"]').live('change', function(){
                          $removeScript
                          $('#kanban-holder').addClass(this.value);
                          });
                      ";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('changeKanbanBoardTheme', $script);
        }

        protected function renderApplyLinkContent()
        {
            $params                = array();
            $params['label']       = Zurmo::t('Core', 'Apply');
            $params['htmlOptions'] = array('id'  => 'kanban-board-options-apply',
                                           'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $element               = new SaveButtonActionElement(null, null, null, $params);
            return $element->render();
        }

        protected function renderApplyResetContent()
        {
            $params                = array();
            $params['label']       = Zurmo::t('Core', 'Reset');
            $params['htmlOptions'] = array('id'  => 'kanban-board-options-reset',
                                           'class' => 'default-btn',
                                           'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $element               = new SaveButtonActionElement(null, null, null, $params);
            return $element->render();
        }
    }
?>