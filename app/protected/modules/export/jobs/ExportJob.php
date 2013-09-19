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
     * For exports with many records we create jobs that will generate export file
     * in background, and send notification to user with export download link,
     * when export job is completed.
     */
    class ExportJob extends BaseJob
    {
        /**
         * Incremented as each model is processed. Utilized to determine the max models processing and when
         * that has been reached.
         * @var int
         */
        protected $totalModelsProcessed = 0;

        /**
         * @returns Translated label that describes this job type.
         */
        public static function getDisplayName()
        {
           return Zurmo::t('ExportModule', 'Export Job');
        }

        /**
         * @return The type of the NotificationRules
         */
        public static function getType()
        {
            return 'Export';
        }

        /**
         * @return string
         */
        public static function getRecommendedRunFrequencyContent()
        {
            return Zurmo::t('ExportModule', 'Every 2 minutes.');
        }

        /**
        * @returns the threshold for how long a job is allowed to run. This is the 'threshold'. If a job
        * is running longer than the threshold, the monitor job might take action on it since it would be
        * considered 'stuck'.
        */
        public static function getRunTimeThresholdInSeconds()
        {
            return 600;
        }

        /**
         * @return int
         */
        public function getTotalModelsProcessed()
        {
            return $this->totalModelsProcessed;
        }

        /*
         * Run uncompleted export items and create export files
         */
        public function run()
        {
            $exportItems = ExportItem::getUncompletedItems();
            $startTime   = Yii::app()->performance->startClock();
            Yii::app()->performance->startMemoryUsageMarker();
            if (count($exportItems) > 0)
            {
                foreach ($exportItems as $exportItem)
                {
                    $originalUser               = Yii::app()->user->userModel;
                    Yii::app()->user->userModel = $exportItem->owner;
                    $message = Zurmo::t('ExportModule', 'run: Beginning processing of export item with ID: {id} ', array('{id}' => $exportItem->id));
                    $this->getMessageLogger()->addInfoMessage($message);
                    try
                    {
                        $this->processExportItem($exportItem);
                    }
                    catch (SecurityException $e)
                    {
                        $message = Zurmo::t('ExportModule', 'Export Item could not be processed due a SecurityException ' . $e->getMessage());
                        $this->getMessageLogger()->addInfoMessage($message);
                        $this->processCompletedWithSecurityExceptionExportItem($exportItem);
                    }
                    if ($this->hasReachedMaximumProcessingCount())
                    {
                        $this->addMaxmimumProcessingCountMessageForAllExportItems();
                        break;
                    }
                }
                Yii::app()->user->userModel = $originalUser;
            }
            $this->processEndMemoryUsageMessage((int)$startTime);
            return true;
        }

        /**
         * @param ExportItem $exportItem
         */
        protected function processExportItem(ExportItem $exportItem)
        {
            $dataProviderOrIdsToExport = unserialize($exportItem->serializedData);
            if ($dataProviderOrIdsToExport instanceOf RedBeanModelDataProvider)
            {
                $this->processRedBeanModelDataProviderExport($exportItem, $dataProviderOrIdsToExport);
            }
            elseif ($dataProviderOrIdsToExport instanceOf ReportDataProvider)
            {
                if ($dataProviderOrIdsToExport instanceOf MatrixReportDataProvider)
                {
                    $this->processMatrixReportDataProviderExport($exportItem, $dataProviderOrIdsToExport);
                }
                else
                {
                    $this->processReportDataProviderExport($exportItem, $dataProviderOrIdsToExport);
                }
            }
            else
            {
                $this->processIdsToExport($exportItem, $dataProviderOrIdsToExport);
            }
            unset($dataProviderOrIdsToExport);
        }

        /**
         * @param ExportItem $exportItem
         * @param RedBeanModelDataProvider $dataProvider
         */
        protected function processRedBeanModelDataProviderExport(ExportItem $exportItem, RedBeanModelDataProvider $dataProvider)
        {
            $headerData = array();
            $data       = array();
            $dataProvider->getPagination()->setPageSize($this->getAsynchronousPageSize());
            $offset     = (int)$exportItem->processOffset;
            $exportCompleted     = true;
            $startingMemoryUsage = memory_get_usage();
            while (true === $this->processExportPage($dataProvider, (int)$offset, $headerData, $data,
                                                    ($exportItem->exportFileModel->id < 0)))
            {
                $this->addMemoryMarkerMessageAfterPageIsProcessed($startingMemoryUsage);
                $startingMemoryUsage = memory_get_usage();
                $offset              = $offset + $this->getAsynchronousPageSize();
                if ($this->hasReachedMaximumProcessingCount())
                {
                    $this->addMaxmimumProcessingCountMessage($exportItem);
                    $exportCompleted = false;
                    break;
                }
            }
            $content         = ExportItemToCsvFileUtil::export($data, $headerData);
            if ($exportItem->exportFileModel->id > 0)
            {
                $exportFileModel = $this->updateExportFileModelByExportItem($content, $exportItem);
            }
            else
            {
                $exportFileModel = $this->makeExportFileModelByContent($content, $exportItem->exportFileName);
            }
            if (!$exportCompleted)
            {
                $this->processInProgressExportItem($exportItem, $exportFileModel, $offset);
            }
            else
            {
                $this->processCompletedExportItem($exportItem, $exportFileModel);
            }
        }

        /**
         * @param ExportItem $exportItem
         * @param ReportDataProvider $dataProvider
         */
        protected function processReportDataProviderExport(ExportItem $exportItem, ReportDataProvider $dataProvider)
        {
            $headerData                          = array();
            $data                                = array();
            $offset                              = (int)$exportItem->processOffset;
            $exportCompleted                     = true;
            $startingMemoryUsage                 = memory_get_usage();
            $dataProvider->pagination->pageSize  = $this->getAsynchronousPageSize();
            $dataProvider->offset                = $offset;
            while (true === $this->processReportExportPage($dataProvider, (int)$offset, $headerData, $data,
                                                    ($exportItem->exportFileModel->id < 0)))
            {
                $this->addMemoryMarkerMessageAfterPageIsProcessed($startingMemoryUsage);
                $startingMemoryUsage = memory_get_usage();
                $offset              = $offset + $this->getAsynchronousPageSize();
                if ($this->hasReachedMaximumProcessingCount())
                {
                    $this->addMaxmimumProcessingCountMessage($exportItem);
                    $exportCompleted = false;
                    break;
                }
            }
            $content         = ExportItemToCsvFileUtil::export($data, $headerData);
            if ($exportItem->exportFileModel->id > 0)
            {
                $exportFileModel = $this->updateExportFileModelByExportItem($content, $exportItem);
            }
            else
            {
                $exportFileModel = $this->makeExportFileModelByContent($content, $exportItem->exportFileName);
            }
            if (!$exportCompleted)
            {
                $this->processInProgressExportItem($exportItem, $exportFileModel, $offset);
            }
            else
            {
                $this->processCompletedExportItem($exportItem, $exportFileModel);
            }
        }

        protected function processMatrixReportDataProviderExport(ExportItem $exportItem, MatrixReportDataProvider $dataProvider)
        {
            $reportToExportAdapter  = ReportToExportAdapterFactory::
                                            createReportToExportAdapter($dataProvider->getReport(), $dataProvider);
            $headerData             = $reportToExportAdapter->getHeaderData();
            $data                   = $reportToExportAdapter->getData();
            $content                = ExportItemToCsvFileUtil::export($data, $headerData);
            $exportFileModel        = $this->makeExportFileModelByContent($content, $exportItem->exportFileName);
            $this->processCompletedExportItem($exportItem, $exportFileModel);
        }

        /**
         * @param ExportItem $exportItem
         * @param $idsToExport
         */
        protected function processIdsToExport(ExportItem $exportItem, $idsToExport)
        {
            $headerData = array();
            $data       = array();
            $models     = array();
            foreach ($idsToExport as $idToExport)
            {
                $models[] = call_user_func(array($exportItem->modelClassName, 'getById'), intval($idToExport));
                $this->totalModelsProcessed++;
            }
            $this->processExportModels($models, $headerData, $data);
            $content         = ExportItemToCsvFileUtil::export($data, $headerData);
            $exportFileModel = $this->makeExportFileModelByContent($content, $exportItem->exportFileName);
            $this->processCompletedExportItem($exportItem, $exportFileModel);
        }

        /**
         * @param $content
         * @param ExportItem $exportItem
         * @return A
         * @throws FailedToSaveFileModelException
         */
        protected function updateExportFileModelByExportItem($content, ExportItem $exportItem)
        {
            $exportItem->exportFileModel->fileContent->content .= $content;
            $saved = $exportItem->exportFileModel->save();
            if (!$saved)
            {
                throw new FailedToSaveFileModelException();
            }
            return $exportItem->exportFileModel;
        }

        /**
         * @param string $content
         * @param string $exportFileName
         * @return ExportFileModel
         * @throws FailedToSaveFileModelException
         */
        protected function makeExportFileModelByContent($content, $exportFileName)
        {
            assert('is_string($exportFileName)');
            $fileContent                  = new FileContent();
            $fileContent->content         = $content;
            $exportFileModel              = new ExportFileModel();
            $exportFileModel->fileContent = $fileContent;
            $exportFileModel->name        = $exportFileName . ".csv";
            $exportFileModel->type        = 'application/octet-stream';
            $exportFileModel->size        = strlen($content);
            $saved = $exportFileModel->save();
            if (!$saved)
            {
                throw new FailedToSaveFileModelException();
            }
            return $exportFileModel;
        }

        /**
         * @param ExportItem $exportItem
         * @param ExportFileModel $exportFileModel
         * @param int $offset
         * @throws FailedToSaveFileModelException
         */
        protected function processInProgressExportItem(ExportItem $exportItem, ExportFileModel $exportFileModel, $offset)
        {
            assert('is_int($offset)');
            $exportItem->exportFileModel = $exportFileModel;
            $exportItem->processOffset   = $offset;
            $saved = $exportItem->save();
            if (!$saved)
            {
                throw new FailedToSaveFileModelException();
            }
        }

        /**
         * @param ExportItem $exportItem
         * @param ExportFileModel $exportFileModel
         * @throws FailedToSaveFileModelException
         */
        protected function processCompletedExportItem(ExportItem $exportItem, ExportFileModel $exportFileModel)
        {
            $exportItem->isCompleted     = true;
            $exportItem->exportFileModel = $exportFileModel;
            $saved = $exportItem->save();
            if (!$saved)
            {
               throw new FailedToSaveFileModelException();
            }
            $message                    = new NotificationMessage();
            $message->htmlContent       = Zurmo::t('ExportModule', 'Export of {fileName} requested on {dateTime} is completed. <a href="{url}">Click here</a> to download file!',
                array(
                    '{fileName}' => $exportItem->exportFileName,
                    '{url}'      => Yii::app()->createUrl('export/default/download', array('id' => $exportItem->id)),
                    '{dateTime}' => DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($exportItem->createdDateTime, 'long'),
                )
            );
            $rules                      = $this->getExportProcessCompletedNotificationRulesForExportItem($exportItem);
            NotificationsUtil::submit($message, $rules);
        }

        /**
         * @param ExportItem $exportItem
         * @throws FailedToSaveFileModelException
         */
        protected function processCompletedWithSecurityExceptionExportItem(ExportItem $exportItem)
        {
            $exportItem->isCompleted     = true;
            $saved = $exportItem->save();
            if (!$saved)
            {
                throw new FailedToSaveFileModelException();
            }
            $message                    = new NotificationMessage();
            $message->htmlContent       = Zurmo::t('ExportModule', 'Export requested on {dateTime} was unable to be completed due to a permissions error.',
                array(
                    '{dateTime}' => DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($exportItem->createdDateTime, 'long'),
                )
            );
            $rules                      = $this->getExportProcessCompletedNotificationRulesForExportItem($exportItem);
            NotificationsUtil::submit($message, $rules);
        }

        /**
         * @param integer $startTime
         */
        protected function processEndMemoryUsageMessage($startTime)
        {
            assert('is_int($startTime)');
            $memoryUsageIncrease = Yii::app()->performance->getMemoryMarkerUsage();
            $endTime             = Yii::app()->performance->endClockAndGet();

            $this->getMessageLogger()->addInfoMessage(
                Zurmo::t('ExportModule',
                    'processEndMemoryUsageMessage: Memory in use: {memoryInUse} Memory Increase: {memoryUsageIncrease} ' .
                    'Processing Time: {processingTime}',
                    array('{memoryInUse}'         => Yii::app()->performance->getMemoryUsage(),
                        '{memoryUsageIncrease}' => $memoryUsageIncrease,
                        '{processingTime}'      => number_format(($endTime - $startTime), 3))));
        }

        /**
         * @param CDataProvider $dataProvider
         * @param int $offset
         * @param $headerData
         * @param $data
         * @param bool $resolveForHeader
         * @return bool
         */
        protected function processExportPage(CDataProvider $dataProvider, $offset, & $headerData, & $data, $resolveForHeader)
        {
            assert('is_int($offset)');
            assert('is_bool($resolveForHeader)');
            $dataProvider->setOffset($offset);
            $models = $dataProvider->getData(true);
            $modelCount = count($models);
            $this->totalModelsProcessed = $this->totalModelsProcessed + $modelCount;
            $this->processExportModels($models, $headerData, $data, $resolveForHeader);
            $this->getMessageLogger()->addInfoMessage(
                Zurmo::t('ExportModule', 'processExportPage: models processed: {count} ' .
                                         'with asynchronousPageSize of {pageSize}' ,
                                         array('{count}'    => $modelCount,
                                               '{pageSize}' => $this->getAsynchronousPageSize())));
            if ($modelCount >= $this->getAsynchronousPageSize())
            {
                return true;
            }
            return false;
        }

        protected function processReportExportPage(ReportDataProvider $dataProvider, $offset, & $headerData, & $data, $resolveForHeader)
        {
            assert('is_int($offset)');
            assert('is_bool($resolveForHeader)');
            $dataProvider->offset   = $offset;
            $reportToExportAdapter  = ReportToExportAdapterFactory::
                createReportToExportAdapter($dataProvider->getReport(), $dataProvider);
            $rows = $reportToExportAdapter->getData();
            $rowsCount = count($rows);
            $this->totalModelsProcessed = $this->totalModelsProcessed + $rowsCount;
            if (count($headerData) == 0 && $resolveForHeader)
            {
                $headerData = array_merge($headerData, $reportToExportAdapter->getHeaderData());
            }
            if (is_array($rows))
            {
                $data = array_merge($data, $rows);
            }
            $this->getMessageLogger()->addInfoMessage(
                Zurmo::t('ExportModule', 'processExportPage: rows processed: {count} ' .
                                         'with asynchronousPageSize of {pageSize}' ,
                                         array('{count}'    => $rowsCount,
                                               '{pageSize}' => $this->getAsynchronousPageSize())));
            if ($rowsCount >= $this->getAsynchronousPageSize())
            {
                return true;
            }
            return false;
        }

        /**
         * @param array $models
         * @param array $headerData
         * @param array $data
         * @param bool $resolveForHeader
         */
        protected function processExportModels(array $models, & $headerData, & $data, $resolveForHeader = true)
        {
            foreach ($models as $model)
            {
                $canRead = ControllerSecurityUtil::doesCurrentUserHavePermissionOnSecurableItem($model, Permission::READ);
                if ($canRead)
                {
                    $modelToExportAdapter  = new ModelToExportAdapter($model);
                    if (count($headerData) == 0 && $resolveForHeader)
                    {
                        $headerData        = $modelToExportAdapter->getHeaderData();
                    }
                    $data[]                = $modelToExportAdapter->getData();
                    unset($modelToExportAdapter);
                }
                $this->runGarbageCollection($model);
            }
            unset($models);
        }

        /**
         * @param $model
         */
        protected function runGarbageCollection($model)
        {
            foreach ($model->attributeNames() as $attributeName)
            {
                if ($model->isRelation($attributeName) && $model->{$attributeName} instanceof RedBeanModel)
                {
                    $model->{$attributeName}->forgetValidators();
                    $model->{$attributeName}->forget();
                }
            }
            $model->forgetValidators();
            $model->forget();
        }

        /**
         * @return int
         */
        protected function getAsynchronousPageSize()
        {
            return ExportModule::$asynchronousPageSize;
        }

        /**
         * @return int
         */
        protected function getAsynchronousMaximumModelsToProcess()
        {
            return ExportModule::$asynchronousMaximumModelsToProcess;
        }

        /**
         * @return bool
         */
        protected function hasReachedMaximumProcessingCount()
        {
            if (($this->totalModelsProcessed + $this->getAsynchronousPageSize()) >
                $this->getAsynchronousMaximumModelsToProcess())
            {
                return true;
            }
            return false;
        }

        /**
         * @param ExportItem $exportItem
         */
        protected function addMaxmimumProcessingCountMessage(ExportItem $exportItem)
        {
            $message = Zurmo::t('ExportModule', 'Export Item with ID: {id} must be finished on next run because the ' .
                                                'maximum processing count has been reached.',
                                                array('{id}' => $exportItem->id));
            $this->getMessageLogger()->addInfoMessage($message);
        }

        protected function addMaxmimumProcessingCountMessageForAllExportItems()
        {
            $message = Zurmo::t('ExportModule', 'Remaining export items must be finished on next run because the ' .
                'maximum processing count has been reached.');
            $this->getMessageLogger()->addInfoMessage($message);
        }

        /**
         * @param int $startingMemoryUsage
         */
        protected function addMemoryMarkerMessageAfterPageIsProcessed($startingMemoryUsage)
        {
            assert('is_int($startingMemoryUsage)');
            $memoryInUse = Yii::app()->performance->getMemoryUsage();
            $message     = Zurmo::t('ExportModule', 'addMemoryMarkerMessageAfterPageIsProcessed: Memory in use: ' .
                                                    '{memoryInUse} Memory Increase: {memoryUsageIncrease}',
                                                    array('{memoryInUse}'         => $memoryInUse,
                                                          '{memoryUsageIncrease}' => $memoryInUse - $startingMemoryUsage));
            $this->getMessageLogger()->addInfoMessage($message);
        }

        /**
         * @param ExportItem $exportItem
         * @return ExportProcessCompletedNotificationRules
         */
        protected function getExportProcessCompletedNotificationRulesForExportItem(ExportItem $exportItem)
        {
            $rules = new ExportProcessCompletedNotificationRules();
            $rules->addUser($exportItem->owner);
            return $rules;
        }
    }
?>