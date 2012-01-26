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
     * Sequential processing for the final step of creating and updating models for each row of data to be imported.
     * There is one step of looping over the data from the data provider.
     */
    class ImportCreateUpdateModelsSequentialProcess extends SequentialProcess
    {
        protected $import;

        protected $mappingData;

        protected $importRules;

        protected $dataProvider;

        protected $explicitReadWriteModelPermissions;

        public function __construct(Import $import, $dataProvider)
        {
            assert('$dataProvider instanceof AnalyzerSupportedDataProvider');
            $unserializedData             = unserialize($import->serializedData);
            $this->import                 = $import;
            $this->mappingData            = $unserializedData['mappingData'];
            $this->importRules            = ImportRulesUtil::makeImportRulesByType($unserializedData['importRulesType']);
            $this->dataProvider           = $dataProvider;
            if (isset($unserializedData['explicitReadWriteModelPermissions']))
            {
                $this->explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                           makeByMixedPermitablesData(
                                                           $unserializedData['explicitReadWriteModelPermissions']);
            }
            else
            {
                $this->explicitReadWriteModelPermissions = new ExplicitReadWriteModelPermissions();
            }
        }

        public function getAllStepsMessage()
        {
            return Yii::t('Default', 'Importing data...');
        }

        protected function steps()
        {
            return array('processRows');
        }

        protected function stepMessages()
        {
            return array('processRows' => Yii::t('Default', 'Processing'));
        }

        protected function processRows($params)
        {
            $completionPosition = 1;
            if (!isset($params['page']))
            {
                $page = 0;
            }
            else
            {
                $page = $params['page'];
            }
            $this->dataProvider->getPagination()->setCurrentPage($page);
            $importResultsUtil = new ImportResultsUtil($this->import);
            $messageLogger     = new ImportMessageLogger();
            ImportUtil::importByDataProvider($this->dataProvider,
                                             $this->importRules,
                                             $this->mappingData,
                                             $importResultsUtil,
                                             $this->explicitReadWriteModelPermissions,
                                             $messageLogger);
            $importResultsUtil->processStatusAndMessagesForEachRow();

            $pageCount                             = $this->dataProvider->getPagination()->getPageCount();
            $pageSize                              = $this->dataProvider->getPagination()->getPageSize();
            $totalItemCount                        = $this->dataProvider->getTotalItemCount();
            $this->subSequenceCompletionPercentage = (($page + 1) / $pageCount) * 100;

            if (($page + 1) == $pageCount)
            {
                $this->nextStep    = null;
                $this->nextMessage = null;
                $this->complete    = true;
                return null;
            }
            else
            {
                $params['page'] = ($page + 1);
                $this->nextStep = 'processRows';
                $this->setNextMessageByStep($this->nextStep);
                $startItemCount = (($page + 1) * $pageSize) + 1;
                if (($startItemCount + ($pageSize - 1) > $totalItemCount))
                {
                    $endItemCount = $totalItemCount;
                }
                else
                {
                    $endItemCount = ($page + 2) * $pageSize;
                }
                $labelParams = array('{startItemCount}' => $startItemCount,
                                     '{endItemCount}'   => $endItemCount,
                                     '{totalItemCount}' => $totalItemCount);
                $nextMessage = ' ' . Yii::t('Default', 'Record(s) {startItemCount} - {endItemCount} of {totalItemCount}',
                                     $labelParams);
                $this->nextMessage .= $nextMessage;
                return $params;
            }
        }
    }
?>