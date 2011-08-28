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
         * Override of parent function. Makes use of the ZurmoActiveForm
         * widget to provide an editable form.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $content = '<div class="wide form">';
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
            $content .= $this->renderViewToolBar();
            $content .= $this->renderFormLayout($form);
            $content .= $this->renderAfterFormLayout($form);
            $formEnd = $clipWidget->renderEndWidget();
            $content .= $formEnd;

            $content .= '</div>';
            return $content;
        }

        protected function renderAfterFormLayout($form)
        {
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

            protected function resolveFormHtmlOptions()
        {
            $data = array();
            if ($this->viewContainsFileUploadElement)
            {
                $data['enctype'] = 'multipart/form-data';
            }
            return $data;
        }
    }
?>
