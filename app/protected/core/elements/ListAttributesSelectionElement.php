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
     * Element to renders two multi-select inputs representing available list view attributes that can be selected when
     * running a search and viewing a list.
     */
    class ListAttributesSelectionElement extends Element
    {
        protected function renderControlEditable()
        {
            assert('$this->model instanceof SearchForm');
            assert('$this->attribute == null');
            assert('$this->model->getListAttributesSelector() != null');
            $content      = $this->renderSelectionContent();
            $content      = ZurmoHtml::tag('div', array('class' => 'attributesContainer'), $content);
            $linkContent  = $this->renderApplyResetContent() . $this->renderApplyLinkContent();
            $linkContent  = ZurmoHtml::tag('div', array('class' => 'form-toolbar clearfix'), $linkContent);
            $this->renderEditableScripts();
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
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("SortableListAttributes");
            $cClipWidget->widget('application.core.widgets.SortableCompareLists', array(
                'leftSideId'             => $this->getEditableInputId(SearchForm::SELECTED_LIST_ATTRIBUTES) . '_hidden',
                'leftSideName'           => $this->getEditableInputName(SearchForm::SELECTED_LIST_ATTRIBUTES) . '_hidden',
                'leftSideValue'          => array(),
                'leftSideData'           => $this->model->getListAttributesSelector()->getUnselectedListAttributesNamesAndLabelsAndAll(),
                'leftSideDisplayLabel'   => Yii::t('Default', 'Hidden Columns'),
                'rightSideId'            => $this->getEditableInputId(SearchForm::SELECTED_LIST_ATTRIBUTES),
                'rightSideName'          => $this->getEditableInputName(SearchForm::SELECTED_LIST_ATTRIBUTES),
                'rightSideValue'         => $this->model->getListAttributesSelector()->getSelected(),
                'rightSideData'          => $this->model->getListAttributesSelector()->getSelectedListAttributesNamesAndLabelsAndAll(),
                'rightSideDisplayLabel'  => Yii::t('Default', 'Visible Columns'),
                'formId'                 => $this->form->getId(),
                'allowSorting'           => true,
                'multiselectNavigationClasses' => 'multiselect-nav-updown',
            ));
            $cClipWidget->endClip();
            $cellsContent  = $cClipWidget->getController()->clips['SortableListAttributes'];
            $content       = '<table>';
            $content      .= '<tbody>';
            $content      .= '<tr>';
            $content      .= $cellsContent;
            $content      .= '</tr>';
            $content      .= '</tbody>';
            $content      .= '</table>';
            return $content;
        }

        /**
         * On keyUp, the search should be conducted.
         */
        protected function renderEditableScripts()
        {
            $defaultSelectedAttributes = $this->model->getListAttributesSelector()->getMetadataDefinedListAttributeNames();
            Yii::app()->clientScript->registerScript('selectedListAttributesScripts', "
                $('#list-attributes-reset').unbind('click.reset');
                $('#list-attributes-reset').bind('click.reset', function()
                    {
                        $('.select-list-attributes-view').hide();
                        resetSelectedListAttributes('" .
                            $this->getEditableInputId(SearchForm::SELECTED_LIST_ATTRIBUTES) . "', '" .
                            $this->getEditableInputId(SearchForm::SELECTED_LIST_ATTRIBUTES) . "_hidden', " .
                            CJSON::encode($defaultSelectedAttributes) . ");
                    }
                );");
        }

        protected function renderApplyLinkContent()
        {
            $params = array();
            $params['label']       = Yii::t('Default', 'Apply');
            $params['htmlOptions'] = array('id'  => 'list-attributes-apply',
                                           'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $searchElement = new SaveButtonActionElement(null, null, null, $params);
            return $searchElement->render();
        }

        protected function renderApplyResetContent()
        {
            $params = array();
            $params['label']       = Yii::t('Default', 'Reset');
            $params['htmlOptions'] = array('id'  => 'list-attributes-reset',
                                           'class' => 'default-btn',
                                           'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $searchElement = new SaveButtonActionElement(null, null, null, $params);
            return $searchElement->render();
        }
    }
?>