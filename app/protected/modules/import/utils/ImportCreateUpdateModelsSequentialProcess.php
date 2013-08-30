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
     * Sequential processing for the final step of creating and updating models for each row of data to be imported.
     * There is one step of looping over the data from the data provider.
     */
    class ImportCreateUpdateModelsSequentialProcess extends ImportSequentialProcess
    {
        protected $explicitReadWriteModelPermissions;

        public function __construct(Import $import, ImportDataProvider $dataProvider)
        {
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
            return Zurmo::t('ImportModule', 'Importing data...');
        }

        protected function steps()
        {
            return array('processRows', 'complete');
        }

        protected function stepMessages()
        {
            return array('processRows'  => Zurmo::t('ImportModule', 'Processing'),
                         'complete'     => Zurmo::t('ImportModule', 'Completing...')
                    );
        }

        protected function processRows($params)
        {
            $page = static::resolvePageByParams($params);
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
            return $this->resolveNextPagingAndParams($page, $params);
        }
    }
?>