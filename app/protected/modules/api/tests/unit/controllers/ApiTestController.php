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

    class ApiTestController extends ZurmoModuleController
    {
        public function getAll()
        {
            $data = ApiModelTestItem::getAll();
            foreach ($data as $model)
            {
                $model->delete();
            }
            $apiModelTestItemModel1 = ApiTestHelper::createApiModelTestItem('aaa');
            $apiModelTestItemModel2 = ApiTestHelper::createApiModelTestItem('bbb');
            $apiModelTestItemModel3 = ApiTestHelper::createApiModelTestItem('ccc');

            try
            {
                $data = ApiModelTestItem::getAll();

                $outputArray = array();
                if (count($data))
                {
                    $outputArray['status'] = 'SUCCESS';
                    foreach ($data as $k => $model)
                    {
                        $outputArray['data'][]['name'] = $model->name;
                    }
                }
                else
                {
                    $outputArray['status'] = 'FAILURE';
                    $outputArray['message'] = Yii::t('Default', 'Error');
                }
            }
            catch (Exception $e)
            {
                $outputArray['status'] = 'FAILURE';
                $outputArray['message'] = $e->getMessage();
            }
            return $outputArray;
        }

        public function getById($id)
        {
            try
            {
                $model = ApiModelTestItem::getById($id);
                $outputArray = array();
                $outputArray['status'] = 'SUCCESS';
                $outputArray['data']['name'] = $model->name;
            }
            catch (Exception $e)
            {
                $outputArray['status'] = 'FAILURE';
                $outputArray['message'] = $e->getMessage();
            }
            return $outputArray;
        }

        public function create($name)
        {
            $apiModelTestItemModel1 = ApiTestHelper::createApiModelTestItem($name);
            $outputArray = array();
            $outputArray['id'] = $apiModelTestItemModel1->id;
            return $outputArray;
        }

        public function update($id, $name)
        {
            try
            {
                $model = ApiModelTestItem::getById($id);
                $model->name = $name;
                $saved = $model->save();
                $outputArray = array();
                if ($saved)
                {
                    $outputArray['status'] = 'SUCCESS';
                    $outputArray['data']['name'] = $model->name;
                }
                else
                {
                    $outputArray['status'] = 'FAILURE';
                    $outputArray['message'] = Yii::t('Default', 'Model could not be saved.');
                }
            }
            catch (Exception $e)
            {
                $outputArray['status'] = 'FAILURE';
                $outputArray['message'] = $e->getMessage();
            }
            return $outputArray;
        }

        public function delete($id)
        {
            try
            {
                $model = ApiModelTestItem::getById($id);
                $model->delete();
                $outputArray['status'] = 'SUCCESS';
            }
            catch (Exception $e)
            {
                $outputArray['status'] = 'FAILURE';
                $outputArray['message'] = $e->getMessage();
            }
            return $outputArray;
        }
    }
?>
