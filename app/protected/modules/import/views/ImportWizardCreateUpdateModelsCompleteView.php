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
     * View for displaying the import results after the sanitization and creation/update of models runs in a
     * sequential process.  This step occurs after the import mapping view is completed.
     * @see ImportCreateUpdateModelSequentialProcess
     */
    class ImportWizardCreateUpdateModelsCompleteView extends ImportWizardView
    {
        protected $modelsCreated    = 0;

        protected $modelsUpdated    = 0;

        protected $rowsWithErrors   = 0;

        protected $importErrorsListView;

        public function __construct($controllerId, $moduleId, ImportWizardForm $model,
                                    $modelsCreated = 0, $modelsUpdated = 0, $rowsWithErrors = 0,
                                    ImportErrorsListView $importErrorsListView)
        {
            assert('is_int($modelsCreated)');
            assert('is_int($modelsUpdated)');
            assert('is_int($rowsWithErrors)');
            parent::__construct($controllerId, $moduleId, $model);
            $this->modelsCreated            = $modelsCreated;
            $this->modelsUpdated            = $modelsUpdated;
            $this->rowsWithErrors           = $rowsWithErrors;
            $this->importErrorsListView     = $importErrorsListView;
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
            $content  = null;
            $content .= '<h3>' . Zurmo::t('ImportModule', 'Congratulations! Your import is complete.  Below is a summary of the results.') . '</h3>';
            $content .= '<ul class="import-summary">';
            $content .= '<li>' . Zurmo::t('ImportModule', 'Records created: {created}', array('{created}' => $this->modelsCreated)) . '</li>';
            $content .= '<li>' . Zurmo::t('ImportModule', 'Records updated: {updated}', array('{updated}' => $this->modelsUpdated)) . '</li>';
            $content .= '<li>' . Zurmo::t('ImportModule', 'Rows with errors: {errors}', array('{errors}' => $this->rowsWithErrors)) . '</li>';
            $content .= '</ul>';
            $content .= $this->renderErrorListContent();
            return $content;
        }

        protected function renderActionElementBar($renderedInForm)
        {
            assert('$renderedInForm == true');
            if ($this->rowsWithErrors > 0)
            {
                return $this->renderActionLinksContent();
            }
        }

        protected function renderErrorListContent()
        {
            $content  = '<h3>' . Zurmo::t('ImportModule', 'Information about the rows with errors') . '</h3>';
            $content .= $this->importErrorsListView->render();
            return $content;
        }

        /**
         * Override because there is no link to go to. This is the last step.
         */
        protected function renderNextPageLinkContent()
        {
            return null;
        }
    }
?>