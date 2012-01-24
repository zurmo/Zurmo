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

    class ZurmoCustomFieldApiController extends ZurmoModuleApiController
    {
        protected function processList($params)
        {
            $customFieldDataItems = CustomFieldData::getAll();
            $data = array();
            foreach ($customFieldDataItems as $customFieldDataItem)
            {
                $dataAndLabels    = CustomFieldDataUtil::
                    getDataIndexedByDataAndTranslatedLabelsByLanguage($customFieldDataItem, Yii::app()->language);
                $data[$customFieldDataItem->name] = $dataAndLabels;
            }
            $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
            return $result;
        }

        public function actionRead()
        {
            $params = Yii::app()->apiHelper->getRequestParams();
            if(!isset($params['id']))
            {
                $message = Yii::t('Default', 'The ID specified was invalid.');
                throw new ApiException($message);
            }
            $result    =  $this->processRead($params['id']);
            Yii::app()->apiHelper->sendResponse($result);
        }

        protected function processRead($id)
        {
            assert('is_string($id)');
            try{
                $customFieldData = CustomFieldData::getByName($id);
            }
            catch (NotFoundException $e)
            {
                $message = Yii::t('Default', 'Specified custom field name was invalid.');
                throw new ApiException($message);
            }

            $customFieldData    = CustomFieldDataUtil::
                getDataIndexedByDataAndTranslatedLabelsByLanguage($customFieldData, Yii::app()->language);

            try
            {
                $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $customFieldData, null, null);
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }

            return $result;
        }

        public function actionCreate()
        {
            throw new ApiUnsupportedException();
        }

        public function actionUpdate()
        {
            throw new ApiUnsupportedException();
        }

        public function actionDelete()
        {
            throw new ApiUnsupportedException();
        }
    }
?>
