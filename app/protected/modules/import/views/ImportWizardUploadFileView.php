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
     * First view in the import wizard. Allows user to select a module to import data into.
     */
    class ImportWizardUploadFileView extends ImportWizardView
    {
        /**
         * Override to handle the form layout for this view.
         * @param $form If the layout is editable, then pass a $form otherwise it can
         * be null.
         * @return A string containing the element's content.
          */
        protected function renderFormLayout($form = null)
        {
            assert('$form instanceof ZurmoActiveForm');
            $fileUploadElement                         = new ImportFileUploadElement($this->model, 'fileUploadData',
                                                         $form);
            $fileUploadElement->editableTemplate       = '{label}<br/>{content}';
            $firstRowIsHeaderElement                   = new CheckBoxElement($this->model, 'firstRowIsHeaderRow', $form);
            $firstRowIsHeaderElement->editableTemplate = '{content}{label}';
            $content  = $form->errorSummary($this->model);
            $content .= '<table>'     . "\n";
            $content .= '<tbody>'     . "\n";
            $content .= '<tr><td>'    . "\n";
            $content .= $fileUploadElement->render();
            $content .= '</td></tr>'  . "\n";
            $content .= '<tr><td>'    . "\n";
            $content .= $firstRowIsHeaderElement->render();
            $content .= '</td></tr>'  . "\n";
            $content .= '</tbody>'    . "\n";
            $content .= '</table>'    . "\n";
            $content .= $this->renderActionLinksContent($form);
            return $content;
        }

        protected function renderPreviousPageLinkContent($form)
        {
            $route = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/step1/',
                                           array('id' => $this->model->id));
            return CHtml::link(Yii::t('Default', 'Previous'), $route);
        }

        protected function renderNextPageLinkContent($form)
        {
            return CHtml::linkButton(Yii::t('Default', 'Next'));
        }
    }
?>