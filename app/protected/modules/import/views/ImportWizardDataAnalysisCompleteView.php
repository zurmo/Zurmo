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
     * View for displaying the data analysis results after the data analysis runs in a sequential process.
     * This step occurs after the import mapping view is completed.
     * @see ImportDataAnalysisSequentialProcess
     */
    class ImportWizardDataAnalysisCompleteView extends ImportWizardView
    {
        protected $columnNamesAndAttributeIndexOrDerivedTypeLabels;

        public function __construct($controllerId, $moduleId, ImportWizardForm $model,
                                    $columnNamesAndAttributeIndexOrDerivedTypeLabels)
        {
            assert('is_array($columnNamesAndAttributeIndexOrDerivedTypeLabels)');
            parent::__construct($controllerId, $moduleId, $model);
            $this->columnNamesAndAttributeIndexOrDerivedTypeLabels = $columnNamesAndAttributeIndexOrDerivedTypeLabels;
        }

        /**
         * Override to handle the form layout for this view.
         * @param $form If the layout is editable, then pass a $form otherwise it can
         * be null.
         * @return A string containing the element's content.
          */
        protected function renderFormLayout($form = null)
        {
            assert('$form instanceof ZurmoActiveForm');
            $content  = '<table>'     . "\n";
            $content .= '<tbody>'     . "\n";
            $content .= '<tr><td><h3>' . "\n";
            if (count($this->model->dataAnalyzerMessagesData) == 0)
            {
                $content .= Zurmo::t('ImportModule', 'Data Analysis is complete. Click "Next" to import your data.');
            }
            else
            {
                $content .= Zurmo::t('ImportModule', 'Data Analysis is complete. There are some issues with your data, please review them below. ' .
                                              'When you are ready, click "Next" to import your data.');
            }
            $content .= '</h3></td></tr>'   . "\n";
            foreach ($this->model->dataAnalyzerMessagesData as $columnName => $messagesData)
            {
                $label =  $this->columnNamesAndAttributeIndexOrDerivedTypeLabels[$columnName];
                $content .= '<tr><td>'    . "\n";
                $content .= '<strong>' . $columnName . ' >>> ' . $label . '</strong><br />';
                foreach ($messagesData as $messageData)
                {
                    $content .= $messageData['message'] . "<br />";
                }
                $content .= '</td></tr>'  . "\n";
            }
            $content .= '</tbody>'    . "\n";
            $content .= '</table>'    . "\n";
            return $content;
        }

        /**
         * Override to specify step 6
         */
        protected function renderNextPageLinkContent()
        {
            $route = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/step6/',
                                           array('id' => $this->model->id));
            return ZurmoHtml::link(ZurmoHtml::wrapLabel(Zurmo::t('ImportModule', 'Next')), $route);
        }

        protected function renderPreviousPageLinkContent()
        {
            return $this->getPreviousPageLinkContentByControllerAction('step4');
        }
    }
?>