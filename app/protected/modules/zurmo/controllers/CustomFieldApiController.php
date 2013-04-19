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
            $params = Yii::app()->apiRequest->getParams();
            if (!isset($params['id']))
            {
                $message = Zurmo::t('ZurmoModule', 'The ID specified was invalid.');
                throw new ApiException($message);
            }
            $result    =  $this->processRead($params['id']);
            Yii::app()->apiHelper->sendResponse($result);
        }

        protected function processRead($id)
        {
            assert('is_string($id)');
            try
            {
                $customFieldData = CustomFieldData::getByName($id);
            }
            catch (NotFoundException $e)
            {
                $message = Zurmo::t('ZurmoModule', 'Specified custom field name was invalid.');
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

        protected static function getSearchFormClassName()
        {
            return 'CustomFieldsSearchForm';
        }
    }
    ?>
