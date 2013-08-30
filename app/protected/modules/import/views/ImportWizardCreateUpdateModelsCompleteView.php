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
     * View for displaying the import results after the sanitization and creation/update of models runs in a
     * sequential process.  This step occurs after the import mapping view is completed.
     * @see ImportCreateUpdateModelSequentialProcess
     */
    class ImportWizardCreateUpdateModelsCompleteView extends ImportWizardView
    {
        /**
         * @var ImportDataProvider
         */
        protected $dataProvider;

        /**
         * @var null|array
         */
        protected $mappingData;

        /**
         * @var int
         */
        protected $modelsCreated  = 0;

        /**
         * @var int
         */
        protected $modelsUpdated  = 0;

        /**
         * @var int
         */
        protected $rowsWithErrors = 0;

        /**
         * @param string $controllerId
         * @param string $moduleId
         * @param ImportWizardForm $model
         * @param ImportDataProvider $dataProvider
         * @param array $mappingData
         * @param int $modelsCreated
         * @param int $modelsUpdated
         * @param int $rowsWithErrors
         */
        public function __construct($controllerId, $moduleId, ImportWizardForm $model, ImportDataProvider $dataProvider,
                                    array $mappingData, $modelsCreated = 0, $modelsUpdated = 0, $rowsWithErrors = 0)
        {
            assert('is_int($modelsCreated)');
            assert('is_int($modelsUpdated)');
            assert('is_int($rowsWithErrors)');
            parent::__construct($controllerId, $moduleId, $model);
            $this->dataProvider             = $dataProvider;
            $this->mappingData              = $mappingData;
            $this->modelsCreated            = $modelsCreated;
            $this->modelsUpdated            = $modelsUpdated;
            $this->rowsWithErrors           = $rowsWithErrors;
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
            $content  = Zurmo::t('ImportModule', 'Congratulations! Your import is complete.  Below is a summary of the results.');
            $content  = ZurmoHtml::tag('h3', array(), $content);
            $content .= $this->renderStatusGroupsContent();
            return $content;
        }

        protected function renderStatusGroupsContent()
        {
            $content  = null;
            $content .= '<ul class="import-summary">';

            $label    = Zurmo::t('ImportModule', 'Created');
            $count    = ZurmoHtml::tag('strong', array(), $this->modelsCreated);
            $led      = ZurmoHtml::tag('i', array('class' => 'led state-true'), '');
            $content .= ZurmoHtml::tag('li', array(), $count . $label . $led );

            $label    = Zurmo::t('ImportModule', 'Updated');
            $count    = ZurmoHtml::tag('strong', array(), $this->modelsUpdated);
            $led      = ZurmoHtml::tag('i', array('class' => 'led'), '');
            $content .= ZurmoHtml::tag('li', array(), $count . $label . $led );

            $label    = Zurmo::t('ImportModule', 'Skipped');
            $count    = ZurmoHtml::tag('strong', array(), $this->rowsWithErrors);
            $led      = ZurmoHtml::tag('i', array('class' => 'led state-false'), '');
            $content .= ZurmoHtml::tag('li', array(), $count . $label . $led );

            $content .= '</ul>';
            return $content;
        }

        protected function renderAfterFormLayout($form)
        {
            $view = new ImportResultsImportTempTableListView($this->controllerId, $this->moduleId, $this->dataProvider,
                    $this->mappingData, $this->model->importRulesType, $this->resolveConfigurationForm(), $form, $this->model->id);
            return $view->render();
        }

        protected function resolveConfigurationForm()
        {
            $configurationForm = new ImportResultsConfigurationForm();
            $this->resolveConfigFormFromRequest($configurationForm);
            return $configurationForm;
        }

        protected function resolveConfigFormFromRequest(& $configurationForm)
        {
            $excludeFromRestore = array();
            if (isset($_GET[get_class($configurationForm)]))
            {
                $configurationForm->setAttributes($_GET[get_class($configurationForm)]);
            }
        }

        /**
         * Override because there is no link to go to. This is the last step.
         */
        protected function renderNextPageLinkContent()
        {
            return null;
        }

        protected function renderTitleContent()
        {
            return null;
        }
    }
?>