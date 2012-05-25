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
     * The base View for a module's edit and detail combination view
     */
    abstract class EditAndDetailsView extends DetailsView
    {
        /**
         * Set as either Edit or Details
         */
        protected $renderType;

        /**
         * Property to decide if a form needs to change its enctype
         * to multipart/form-data
         * @var boolean
         */
        protected $viewContainsFileUploadElement = false;

        /**
         * Accepts $renderType as Edit or Details
         */
        public function __construct($renderType, $controllerId, $moduleId, $model)
        {
            assert('$model instanceof RedBeanModel || $model instanceof CFormModel');
            assert('$renderType == "Edit" || $renderType == "Details"');
            $this->renderType = $renderType;
            parent::__construct($controllerId, $moduleId, $model);
        }

        /**
         * Override of parent function. Makes use of the ZurmoActiveForm
         * widget to provide an editable form.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            if ($this->renderType == 'Details')
            {
                return parent::renderContent();
            }
            $content  = '<div>';
            $content .= $this->renderTitleContent();
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
                                                                    array('id' => 'edit-form',
                                                                    'htmlOptions' => $this->resolveFormHtmlOptions()),
                                                                    $this->resolveActiveFormAjaxValidationOptions()
                                                                )
                                                            );
            $content .= $formStart;
            $content .= '<div class="attributesContainer">';
            $content .= $this->renderFormLayout($form);
            $content .= $this->renderRightSideContent($form);
            $content .= '</div>';
            $content .= $this->renderAfterFormLayout($form);
            $actionElementContent = $this->renderActionElementBar(true);
            if ($actionElementContent != null)
            {
                $content .= '<div class="view-toolbar-container clearfix"><div class="form-toolbar">';
                $content .= $actionElementContent;
                $content .= '</div></div>';
            }
            $formEnd = $clipWidget->renderEndWidget();
            $content .= $formEnd;

            $content .= '</div></div>';
            return $content;
        }

        protected function renderTitleContent()
        {
            if ($this->model->id > 0)
            {
                return '<h1>' . strval($this->model) . '</h1>';
            }
            return '<h1>' . $this->getNewModelTitleLabel() . '</h1>';
        }

        protected function renderRightSideContent($form)
        {
            assert('$form == null || $form instanceof ZurmoActiveForm');
            if ($form != null)
            {
                $rightSideContent = $this->renderRightSideFormLayoutForEdit($form);
                if ($rightSideContent != null)
                {
                    $content  = '<div id="permissions-module"><div class="buffer"><div>';
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
                    Yii::getPathOfAlias('ext.zurmoinc.framework.views.assets')) . '/dropDownInteractions.js');
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.views.assets')) . '/jquery.dropkick-1.0.0.js');
        }

        protected function resolveActiveFormAjaxValidationOptions()
        {
            return array('enableAjaxValidation' => false);
        }

        public static function getDesignerRulesType()
        {
            return 'EditAndDetailsView';
        }

        protected function shouldDisplayCell($detailViewOnly)
        {
            if ($this->renderType == 'Details')
            {
                return true;
            }
            return !$detailViewOnly;
        }

        protected function shouldRenderToolBarElement($element, $elementInformation)
        {
            assert('$element instanceof ActionElement');
            assert('is_array($elementInformation)');
            if (!parent::shouldRenderToolBarElement($element, $elementInformation))
            {
                return false;
            }
            if (!isset($elementInformation['renderType']) ||
                (isset($elementInformation['renderType']) &&
                $elementInformation['renderType'] == $this->renderType
                )
            )
            {
                return true;
            }
            return false;
        }

        protected function resolveFormHtmlOptions()
        {
            $data = array();
            if ($this->viewContainsFileUploadElement)
            {
                $data['enctype'] = 'multipart/form-data';
            }
            return $data;
        }

        protected function getNewModelTitleLabel()
        {
            throw new NotImplementedException();
        }
    }
?>
