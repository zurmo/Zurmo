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

    class ApiTestModelItemController extends ZurmoModuleApiController
    {

        public function getAll()
        {
            try
            {
                $data = ApiTestModelItem::getAll();
                $outputArray = array();
                if (count($data))
                {
                    $outputArray['status'] = 'SUCCESS';
                    $outputArray['message'] = '';
                    foreach ($data as $k => $model)
                    {
                        $util  = new RedBeanModelToApiDataUtil($model);
                        $outputArray['data'][] = $util->getData();
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
        /*
        public function getById($id)
        {
            try
            {
                $model = ApiTestModelItem::getById($id);
                $util  = new RedBeanModelToApiDataUtil($model);
                $data  = $util->getData();

                $outputArray = array();
                $outputArray['status']  = 'SUCCESS';
                $outputArray['data']    = $data;
                $outputArray['message'] = '';
            }
            catch (Exception $e)
            {
                $outputArray['data'] = null;
                $outputArray['status'] = 'FAILURE';
                $outputArray['message'] = $e->getMessage();
            }
            return $outputArray;
        }
*/
        public function create($data)
        {
            try
            {
                $currencyValue              = new CurrencyValue();
                $currencyValue->value       = $data['currencyValue']['value'];
                $currencyValue->currency    = Currency::getById($data['currencyValue']['currency']['id']);
                $testItem2 = ApiTestModelItem2::getById($data['hasOne']['id']);
                $testItem4 = ApiTestModelItem4::getById($data['hasOneAlso']['id']);

                $model= new ApiTestModelItem();
                $model->firstName     = $data['firstName'];
                $model->lastName      = $data['lastName'];
                $model->boolean       = $data['boolean'];
                $model->date          = $data['date'];
                $model->dateTime      = $data['dateTime'];
                $model->float         = $data['float'];
                $model->integer       = $data['integer'];
                $model->phone         = $data['phone'];
                $model->string        = $data['string'];
                $model->textArea      = $data['textArea'];
                $model->url           = $data['url'];
                $model->currencyValue = $currencyValue;
                $model->hasOne        = $testItem2;
                $model->hasOneAlso    = $testItem4;

                $saved = $model->save();
                $id = $model->id;
                $model->forget();
                unset($model);
                $outputArray = array();
                if ($saved)
                {
                    $model = ApiTestModelItem::getById($id);
                    $util  = new RedBeanModelToApiDataUtil($model);
                    $data  = $util->getData();
                    $outputArray['status']  = 'SUCCESS';
                    $outputArray['data']    = $data;
                    $outputArray['message'] = '';
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

        public function update($id, $data)
        {
            try
            {
                $model = ApiTestModelItem::getById($id);
                $currencyValue              = CurrencyValue::getById($data['currencyValue']['id']);
                $currencyValue->value       = $data['currencyValue']['value'];
                $currencyValue->currency    = Currency::getById($data['currencyValue']['currency']['id']);
                $testItem2 = ApiTestModelItem2::getById($data['hasOne']['id']);
                $testItem4 = ApiTestModelItem4::getById($data['hasOneAlso']['id']);

                $model->firstName     = $data['firstName'];
                $model->lastName      = $data['lastName'];
                $model->boolean       = $data['boolean'];
                $model->date          = $data['date'];
                $model->dateTime      = $data['dateTime'];
                $model->float         = $data['float'];
                $model->integer       = $data['integer'];
                $model->phone         = $data['phone'];
                $model->string        = $data['string'];
                $model->textArea      = $data['textArea'];
                $model->url           = $data['url'];
                $model->currencyValue = $currencyValue;
                $model->hasOne        = $testItem2;
                $model->hasOneAlso    = $testItem4;

                $saved = $model->save();
                $outputArray = array();
                if ($saved)
                {
                    $model = ApiTestModelItem::getById($id);
                    $util  = new RedBeanModelToApiDataUtil($model);
                    $data  = $util->getData();

                    $outputArray['status']  = 'SUCCESS';
                    $outputArray['data']    = $data;
                    $outputArray['message'] = '';
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
/*
        public function delete($id)
        {
            try
            {
                $model = ApiTestModelItem::getById($id);
                $model->delete();
                $outputArray['status'] = 'SUCCESS';
                $outputArray['message'] = '';
            }
            catch (Exception $e)
            {
                $outputArray['status'] = 'FAILURE';
                $outputArray['message'] = $e->getMessage();
            }
            return $outputArray;
        }
        */
    }
?>
