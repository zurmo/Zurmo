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
     * View for displaying the data analysis results after the data analysis runs in a sequential process.
     * This step occurs after the import mapping view is completed.
     * @see ImportDataAnalysisSequentialProcess
     */
    class ImportWizardDataAnalysisCompleteView extends ImportWizardView
    {
        /**
         * @var array
         */
        protected $columnNamesAndAttributeIndexOrDerivedTypeLabels;

        /**
         * @var ImportDataProvider
         */
        protected $dataProvider;

        /**
         * @var null|array
         */
        protected $mappingData;

        /**
         * @param string $controllerId
         * @param string $moduleId
         * @param ImportWizardForm $model
         * @param null $columnNamesAndAttributeIndexOrDerivedTypeLabels
         * @param ImportDataProvider $dataProvider
         * @param array $mappingData
         */
        public function __construct($controllerId, $moduleId, ImportWizardForm $model,
                                    $columnNamesAndAttributeIndexOrDerivedTypeLabels, ImportDataProvider $dataProvider,
                                    array $mappingData)
        {
            assert('is_array($columnNamesAndAttributeIndexOrDerivedTypeLabels)');
            parent::__construct($controllerId, $moduleId, $model);
            $this->columnNamesAndAttributeIndexOrDerivedTypeLabels = $columnNamesAndAttributeIndexOrDerivedTypeLabels;
            $this->dataProvider = $dataProvider;
            $this->mappingData  = $mappingData;
        }

        public static function resolveConfigurationForm()
        {
            $configurationForm = new ImportResultsConfigurationForm();
            static::resolveConfigFormFromRequest($configurationForm);
            return $configurationForm;
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
            $content  = Zurmo::t('ImportModule', 'Data Analysis is complete. Please review the results below. ' .
                                          'When you are ready, click "Next" to import your data.');
            $content  = ZurmoHtml::tag('h3', array(), $content);
            $content .= $this->renderStatusGroupsContent();
            return $content;
        }

        protected function renderAfterFormLayout($form)
        {
            $view = new AnalysisResultsImportTempTableListView($this->controllerId, $this->moduleId, $this->dataProvider,
                        $this->mappingData, $this->model->importRulesType, static::resolveConfigurationForm(), $form, $this->model->id);
            return $view->render();
        }

        protected static function resolveConfigFormFromRequest(& $configurationForm)
        {
            $excludeFromRestore = array();
            if (isset($_GET[get_class($configurationForm)]))
            {
                $configurationForm->setAttributes($_GET[get_class($configurationForm)]);
            }
        }

        /**
         * Override to specify step 6
         */
        protected function renderNextPageLinkContent()
        {
            $route = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/step6/',
                                           array('id' => $this->model->id));
            return ZurmoHtml::link(ZurmoHtml::wrapLabel($this->renderNextPageLinkLabel()), $route, array('class' => 'green-button'));
        }

        protected function renderPreviousPageLinkContent()
        {
            return $this->getPreviousPageLinkContentByControllerAction('step4');
        }

        protected function renderStatusGroupsContent()
        {
            $groupData = $this->dataProvider->getCountDataByGroupByColumnName('analysisStatus');
            $content  = null;
            $content .= '<ul class="import-summary">';

            $label    = Zurmo::t('ImportModule', 'Ok');
            $count    = ZurmoHtml::tag('strong', array(), self::findCountByGroupDataAndStatus($groupData, ImportDataAnalyzer::STATUS_CLEAN));
            $led      = ZurmoHtml::tag('i', array('class' => 'led state-true'), '');
            $content .= ZurmoHtml::tag('li', array(), $count . $label . $led );

            $label    = Zurmo::t('ImportModule', 'Warning');
            $count    = ZurmoHtml::tag('strong', array(), self::findCountByGroupDataAndStatus($groupData, ImportDataAnalyzer::STATUS_WARN));
            $led      = ZurmoHtml::tag('i', array('class' => 'led'), '');
            $content .= ZurmoHtml::tag('li', array(), $count . $label . $led );

            $label    = Zurmo::t('ImportModule', 'Skip');
            $count    = ZurmoHtml::tag('strong', array(), self::findCountByGroupDataAndStatus($groupData, ImportDataAnalyzer::STATUS_SKIP));
            $led      = ZurmoHtml::tag('i', array('class' => 'led state-false'), '');
            $content .= ZurmoHtml::tag('li', array(), $count . $label . $led );

            $content .= '</ul>';
            return $content;
        }

        protected function findCountByGroupDataAndStatus(array $groupData, $status)
        {
            assert('is_int($status)');
            foreach ($groupData as $group)
            {
                if ((int)$group['analysisStatus'] === $status)
                {
                    return (int)$group['count'];
                }
            }
            return 0;
        }

        protected function renderPreviousPageLinkLabel()
        {
            return Zurmo::t('ImportModule', 'Map Fields');
        }

        protected function renderNextPageLinkLabel()
        {
            return Zurmo::t('ImportModule', 'Import Data');
        }

        protected function renderTitleContent()
        {
            return null;
        }
    }
?>