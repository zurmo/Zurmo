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
     * For exports with many records we create jobs that will generate export file
     * in background, and send notification to user with export download link,
     * when export job is completed.
     */
    class ExportJob extends BaseJob
    {
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
            return 300;
        }

        public function run()
        {
            $exportItems = ExportItem::getUncompletedItems();
            if (count($exportItems) > 0)
            {
                foreach ($exportItems as $exportItem)
                {
                    if (isset($exportItem->exportFileModel))
                    {
                        //continue;
                    }

                    $unserializedData = unserialize($exportItem->serializedData);
                    if ($unserializedData instanceOf RedBeanModelDataProvider)
                    {
                        $formattedData = $unserializedData->getData();
                    }
                    else
                    {
                        $formattedData = array();
                        foreach ($unserializedData as $idToExport)
                        {
                            $model = call_user_func(array($exportItem->modelClassName, 'getById'), intval($idToExport));
                            $formattedData[] = $model;
                        }
                    }

                    if ($exportItem->exportFileType == 'csv')
                    {
                        $headerData = array();
                        foreach ($formattedData as $model)
                        {
                            if (ControllerSecurityUtil::doesCurrentUserHavePermissionOnSecurableItem($model, Permission::READ))
                            {
                                $modelToExportAdapter  = new ModelToExportAdapter($model);
                                if (count($headerData) == 0)
                                {
                                    $headerData        = $modelToExportAdapter->getHeaderData();
                                }
                                $data[]                = $modelToExportAdapter->getData();
                            }
                        }
                        $output                       = ExportItemToCsvFileUtil::export($data, $headerData);
                        $fileContent                  = new FileContent();
                        $fileContent->content         = $output;
                        $exportFileModel              = new ExportFileModel();
                        $exportFileModel->fileContent = $fileContent;
                        $exportFileModel->name        = $exportItem->exportFileName . ".csv";
                        $exportFileModel->type        = 'application/octet-stream';
                        $exportFileModel->size        = strlen($output);
                        $saved                        = $exportFileModel->save();

                        if ($saved)
                        {
                            $exportItem->isCompleted = 1;
                            $exportItem->exportFileModel = $exportFileModel;
                            $exportItem->save();
                            $message                    = new NotificationMessage();
                            $message->htmlContent       = Zurmo::t('ExportModule', 'Export of {fileName} requested on {dateTime} is completed. <a href="{url}">Click here</a> to download file!',
                                array(
                                    '{fileName}' => $exportItem->exportFileName,
                                    '{url}'      => Yii::app()->createUrl('export/default/download', array('id' => $exportItem->id)),
                                    '{dateTime}' => DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($exportItem->createdDateTime, 'long'),
                                )
                            );
                            $rules                      = new ExportProcessCompletedNotificationRules();
                            NotificationsUtil::submit($message, $rules);
                        }
                    }
                }
            }
            else
            {
                return true;
            }
            return true;
        }
    }
?>