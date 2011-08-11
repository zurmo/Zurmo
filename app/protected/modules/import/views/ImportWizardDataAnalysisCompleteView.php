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
            foreach($this->model->dataAnalyzerMessagesData as $columnName => $messagesData)
            {
                $label =  $this->columnNamesAndAttributeIndexOrDerivedTypeLabels[$columnName];
                $content .= '<tr><td>'    . "\n";
                $content .= '<b>' . $columnName . ' >>> ' . $label . '</b></br>';
                foreach($messagesData as $messageData)
                {
                    $content .= $messageData['message'] . "</br>";
                }
                $content .= '</td></tr>'  . "\n";
            }
            $content .= '</tbody>'    . "\n";
            $content .= '</table>'    . "\n";
            $content .= $this->renderActionLinksContent();
            return $content;
        }

        protected function renderPreviousPageLinkContent()
        {
            return $this->getPreviousPageLinkContentByControllerAction('step4');
        }
    }
?>