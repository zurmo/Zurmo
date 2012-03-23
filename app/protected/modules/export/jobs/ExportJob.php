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
     * A job to generate export files.
     */
    class ExportJob extends BaseJob
    {
        /**
         * @returns Translated label that describes this job type.
         */
        public static function getDisplayName()
        {
           return Yii::t('Default', 'Export Job');
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
            return Yii::t('Default', 'Every 5 minutes.');
        }

        /**
        * @returns the threshold for how long a job is allowed to run. This is the 'threshold'. If a job
        * is running longer than the threshold, the monitor job might take action on it since it would be
        * considered 'stuck'.
        */
        public static function getRunTimeThresholdInSeconds()
        {
            return 30;
        }

        public function run()
        {
            $exportItems = ExportItem::getUncompletedItems();
            foreach ($exportItems as $exportItem)
            {
                if (isset($exportItem->exportFileModel))
                {
                    //continue;
                }

                $dataProvider = unserialize($exportItem->serializedData);
                $formattedData = $dataProvider->getData();
                if ($exportItem->exportFileType == 'csv')
                {
                    foreach ($formattedData as $model)
                    {
                        $redBeanModelToExportAdapter  = new RedBeanModelToExportAdapter($model);
                        $data[] = $redBeanModelToExportAdapter->getData();
                    }
                    $output = ExportItemToCsvFileUtil::export($data);

                    $fileContent          = new FileContent();
                    $fileContent->content = $output;

                    $exportFileModel = new ExportFileModel();
                    $exportFileModel->exportItem = $exportItem;
                    $exportFileModel->fileContent = $fileContent;
                    $exportFileModel->name = "export.csv";
                    $exportFileModel->type    = 'application/octet-stream';
                    $exportFileModel->size    = strlen($output);
                    $saved         = $exportFileModel->save();

                    if ($saved)
                    {
                        $exportItem->isCompleted = 1;
                        $exportItem->exportFileModel = $exportFileModel;
                        $exportItem->save();
                    }
                }
            }
        }
    }
?>