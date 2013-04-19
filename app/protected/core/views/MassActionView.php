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
     * The base View for a module's mass action view.
     */
    abstract class MassActionView extends EditView
    {
        /**
         * Array of booleans indicating
         * which attributes are currently trying to
         * be mass updated
         */
        protected $activeAttributes;

        protected $alertMessage;

        protected $selectedRecordCount;

        protected $title;

        protected $moduleClassName;

        abstract protected function renderAlertMessage();

        abstract protected function renderPreActionElementBar($form);

        abstract protected function renderItemLabel();

        abstract protected function renderItemOperationType();

        abstract protected function renderOperationHighlight();

        /**
         * Constructs a detail view specifying the controller as
         * well as the model that will have its mass delete displayed.
         */
        public function __construct($controllerId, $moduleId, RedBeanModel $model, $activeAttributes, $selectedRecordCount, $title, $alertMessage = null, $moduleClassName = null)
        {
            assert('is_array($activeAttributes)');
            assert('is_string($title)');

            $this->controllerId                       = $controllerId;
            $this->moduleId                           = $moduleId;
            $this->model                              = $model;
            $this->modelClassName                     = get_class($model);
            $this->modelId                            = $model->id;
            $this->activeAttributes                   = $activeAttributes;
            $this->selectedRecordCount                = $selectedRecordCount;
            $this->title                              = $title;
            $this->alertMessage                       = $alertMessage;
            $this->moduleClassName                    = $moduleClassName;
        }

        protected function renderContent()
        {
            $content  = '<div class="wrapper">';
            $content .= $this->renderTitleContent();
            $content .= '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                                'ZurmoActiveForm',
                                                                array('id' => static::getFormId(), 'enableAjaxValidation' => false)
                                                            );
            $content .= $formStart;
            $content .= $this->renderAlertMessage();
            $content .= $this->renderOperationDescriptionContent();
            $content .= $this->renderPreActionElementBar($form);
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

        protected function renderItemCount()
        {
            return ZurmoHtml::tag('strong', array(), $this->selectedRecordCount) . '&#160;';
        }

        protected function renderItemOperationMessage()
        {
            $message    = 'selected for ' . $this->renderItemOperationType() . '.';
            $category   = $this->renderItemOperationMessageCategory();
            return Zurmo::t($category, $message);
        }

        protected function renderItemOperationMessageCategory()
        {
            return 'Core';
        }

        protected function renderOperationMessage()
        {
            $message  = $this->renderItemCount() .
                        $this->renderItemLabel() .
                        ' ' .
                        $this->renderItemOperationMessage();
            return $message;
        }

        protected function renderOperationDescriptionContent()
        {
            $highlight      = $this->renderOperationHighlight();
            $message        = $this->renderOperationMessage();
            $description    = $highlight . $message;
            return ZurmoHtml::wrapLabel($description, 'operation-description');
        }

        protected function getSelectedRecordCount()
        {
            return $this->selectedRecordCount;
        }
    }
?>