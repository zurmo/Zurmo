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
     * The base View for a module's edit view.
     */
    abstract class EditView extends DetailsView
    {
        /**
         * Property to decide if a form needs to change its enctype
         * to multipart/form-data
         * @var boolean
         */
        protected $viewContainsFileUploadElement = false;

        /**
         * When rendering the content, should it be wrapped in a div that has the class 'wrapper' or not.
         * @var boolean
         */
        protected $wrapContentInWrapperDiv = true;

        /**
         * Override of parent function. Makes use of the ZurmoActiveForm
         * widget to provide an editable form.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $content = $this->renderTitleContent();
            $maxCellsPresentInAnyRow = $this->resolveMaxCellsPresentInAnyRow($this->getFormLayoutMetadata());
            if ($maxCellsPresentInAnyRow > 1)
            {
                $class = "wide double-column form";
            }
            else
            {
                $class = "wide form";
            }
            $content .= '<div class="' . $class . '">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                                'ZurmoActiveForm',
                                                                array_merge(
                                                                    array('id' => static::getFormId(),
                                                                    'htmlOptions' => $this->resolveFormHtmlOptions()),
                                                                    $this->resolveActiveFormAjaxValidationOptions()
                                                                )
                                                            );
            $content .= $formStart;
            $content .= $this->renderOperationDescriptionContent();
            $content .= '<div class="attributesContainer">';
            $content .= $this->renderFormLayout($form);
            $content .= $this->renderRightSideContent($form);
            $content .= '</div>';
            $content .= $this->renderAfterFormLayout($form);
            $actionToolBarContent = $this->renderActionElementBar(true);
            if ($actionToolBarContent != null)
            {
                $content .= '<div id="float-bar"><div class="view-toolbar-container clearfix dock"><div class="form-toolbar">';
                $content .= $actionToolBarContent;
                $content .= '</div></div></div>';
            }
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $content .= '</div>';
            if ($this->wrapContentInWrapperDiv)
            {
                return ZurmoHtml::tag('div', array('class' => 'wrapper'), $content);
            }
            return $content;
        }

        /**
         * Override as needed
         */
        protected function renderOperationDescriptionContent()
        {
        }

        protected function renderRightSideContent($form = null)
        {
            assert('$form == null || $form instanceof ZurmoActiveForm');
            if ($form != null)
            {
                $rightSideContent = $this->renderRightSideFormLayoutForEdit($form);
                if ($rightSideContent != null)
                {
                    $content  = '<div id="right-side-edit-view-panel"><div class="buffer"><div>';
                    $content .= $rightSideContent;
                    $content .= '</div></div></div>';
                    return $content;
                }
            }
        }

        protected function renderRightSideFormLayoutForEdit($form)
        {
        }

        protected function renderAfterFormLayout($form)
        {
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.views.assets')) . '/dropDownInteractions.js');
        }

        protected function resolveActiveFormAjaxValidationOptions()
        {
            return array('enableAjaxValidation' => false);
        }

        public static function getDesignerRulesType()
        {
            return 'EditView';
        }

        protected function shouldDisplayCell($detailViewOnly)
        {
            return !$detailViewOnly;
        }

        protected static function getFormId()
        {
            return 'edit-form';
        }

        protected function resolveFormHtmlOptions()
        {
            $data = array('onSubmit' => 'js:return attachLoadingOnSubmit("' . static::getFormId() . '")');
            if ($this->viewContainsFileUploadElement)
            {
                $data['enctype'] = 'multipart/form-data';
            }
            return $data;
        }
    }
?>
