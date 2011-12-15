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

    class AccountApiController extends ZurmoModuleApiController
    {
        public function getAll()
        {
            try
            {
                $filterParams = array();
                if (isset($_GET['filter']) && $_GET['filter'] != '')
                {
                    parse_str($_GET['filter'], $filterParams);
                }

                $pageSize    = Yii::app()->pagination->getGlobalValueByType('apiListPageSize');
                if (isset($filterParams['pagination']['pageSize']))
                {
                    $pageSize = $filterParams['pagination']['pageSize'];
                }

                if (isset($filterParams['pagination']['page']))
                {
                    $_GET['Account_page'] = $filterParams['pagination']['page'];
                }

                if (isset($filterParams['sort']))
                {
                    $_GET['Account_sort'] = $filterParams['sort'];
                }


                if (isset($filterParams['search']))
                {
                    $_GET['AccountsSearchForm'] = $filterParams['search'];
                }

                $stateMetadataAdapterClassName = null; //would populate for contacts/leads
                $account = new Account(false);
                $searchForm = new AccountsSearchForm($account);

                $dataProvider = $this->makeRedBeanDataProviderFromGet(
                    $searchForm,
                    'Account',
                    $pageSize,
                    Yii::app()->user->userModel->id,
                    $stateMetadataAdapterClassName);

                $totalItems = $dataProvider->getTotalItemCount();
                $outputArray = array();
                $outputArray['data']['total'] = $totalItems;

                if ($totalItems > 0)
                {
                    $outputArray['status'] = 'SUCCESS';
                    $outputArray['message'] = '';

                    $data = $dataProvider->getData();
                    foreach ($data as $account)
                    {
                        $util  = new RedBeanModelToApiDataUtil($account);
                        $outputArray['data']['array'][] = $util->getData();
                    }
                }
                else
                {
                    $outputArray['data']['array'] = null;
                    $outputArray['status'] = 'FAILURE';
                    $outputArray['message'] = Yii::t('Default', 'Error');
                }
            }
            catch (Exception $e)
            {
                $outputArray['data'] = null;
                $outputArray['status'] = 'FAILURE';
                $outputArray['message'] = $e->getMessage();
            }
            return $outputArray;
        }

        public function getById($id)
        {
            try
            {
                $model = Account::getById($id);
                $isAllowed = ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($model);
                if ($isAllowed === false)
                {
                    throw new Exception('This action is not allowed.');
                }
                $util  = new RedBeanModelToApiDataUtil($model);
                $data  = $util->getData();
                $outputArray = array();
                $outputArray['status'] = 'SUCCESS';
                $outputArray['data']   = $data;
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

        public function create($data)
        {
            try
            {
                $model= new Account();

                if (isset($data['name']))
                {
                    $model->name            = $data['name'];
                }
                if (isset($data['officePhone']))
                {
                    $model->officePhone     = $data['officePhone'];
                }
                if (isset($data['officeFax']))
                {
                    $model->officeFax       = $data['officeFax'];
                }
                if (isset($data['employees']))
                {
                    $model->employees       = $data['employees'];

                }
                if (isset($data['website']))
                {
                    $model->website         = $data['website'];
                }
                if (isset($data['annualRevenue']))
                {
                    $model->annualRevenue   = $data['annualRevenue'];
                }
                if (isset($data['description']))
                {
                    $model->description     = $data['description'];
                }

                if (isset($data['industry']))
                {
                    $model->industry->value        = $data['industry']['value'];
                }
                if (isset($data['type']))
                {
                    $model->type->value            = $data['type']['value'];
                }

                if (isset($data['primaryEmail']))
                {
                    $email = new Email();
                    foreach ($data['primaryEmail'] as $key => $value)
                    {
                        $email->{$key}  = $value;
                    }
                    $model->primaryEmail = $email;
                }

                if (isset($data['secondaryEmail']))
                {
                    $email = new Email();
                    foreach ($data['secondaryEmail'] as $key => $value)
                    {
                        $email->{$key}  = $value;
                    }
                    $model->secondaryEmail = $email;
                }
                if (isset($data['billingAddress']))
                {
                    $address = new Address();
                    foreach ($data['billingAddress'] as $key => $value)
                    {
                        $address->{$key}  = $value;
                    }
                    $model->billingAddress = $address;
                }
                if (isset($data['shippingAddress']))
                {
                    $address = new Address();
                    foreach ($data['shippingAddress'] as $key => $value)
                    {
                        $address->{$key}  = $value;
                    }
                    $model->shippingAddress = $address;
                }
                $saved = $model->save();
                $id = $model->id;
                $model->forget();
                unset($model);
                $outputArray = array();
                if ($saved)
                {
                    $model = Account::getById($id);
                    $util  = new RedBeanModelToApiDataUtil($model);
                    $data  = $util->getData();

                    $outputArray['status']  = 'SUCCESS';
                    $outputArray['data']    = $data;
                    $outputArray['message'] = '';
                }
                else
                {
                    $outputArray['data'] = null;
                    $outputArray['status'] = 'FAILURE';
                    $outputArray['message'] = Yii::t('Default', 'Model could not be saved.');
                }
            }
            catch (Exception $e)
            {
                $outputArray['data'] = null;
                $outputArray['status'] = 'FAILURE';
                $outputArray['message'] = $e->getMessage();
            }
            return $outputArray;
        }

        public function update($id, $data)
        {
            try
            {
                $model = Account::getById($id);
                $isAllowed = ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($model);
                if ($isAllowed === false)
                {
                    throw new Exception('This action is not allowed.');
                }

                if (isset($data['name']))
                {
                    $model->name            = $data['name'];
                }
                else
                {
                    $model->name            = null;
                }

                if (isset($data['officePhone']))
                {
                    $model->officePhone     = $data['officePhone'];
                }
                else
                {
                    $model->officePhone     = null;
                }

                if (isset($data['officeFax']))
                {
                    $model->officeFax       = $data['officeFax'];
                }
                else
                {
                    $model->officeFax       = null;
                }

                if (isset($data['employees']))
                {
                    $model->employees       = $data['employees'];
                }
                else
                {
                    $model->employees       = null;
                }

                if (isset($data['website']))
                {
                    $model->website         = $data['website'];
                }
                else
                {
                    $model->website         = null;
                }

                if (isset($data['annualRevenue']))
                {
                    $model->annualRevenue   = $data['annualRevenue'];
                }
                else
                {
                    $model->annualRevenue   = null;
                }

                if (isset($data['description']))
                {
                    $model->description     = $data['description'];
                }
                else
                {
                    $model->description     = null;
                }

                if (isset($data['industry']))
                {
                    $model->industry->value = $data['industry']['value'];
                }
                else
                {
                    $model->industry        = null;
                }

                if (isset($data['type']))
                {
                    $model->type->value            = $data['type']['value'];
                }
                else
                {
                    $model->type            = null;
                }

                if (isset($data['primaryEmail']))
                {
                    $email = new Email();
                    foreach ($data['primaryEmail'] as $key => $value)
                    {
                        $email->{$key}  = $value;
                    }
                    $model->primaryEmail    = $email;
                }
                else
                {
                    $model->primaryEmail    = null;
                }

                if (isset($data['secondaryEmail']))
                {
                    $email = new Email();
                    foreach ($data['secondaryEmail'] as $key => $value)
                    {
                        $email->{$key}  = $value;
                    }
                    $model->secondaryEmail  = $email;
                }
                else
                {
                    $model->secondaryEmail  = null;
                }

                if (isset($data['billingAddress']))
                {
                    $address = new Address();
                    foreach ($data['billingAddress'] as $key => $value)
                    {
                        $address->{$key}    = $value;
                    }
                    $model->billingAddress  = $address;
                }
                else
                {
                    $model->billingAddress  = null;
                }

                if (isset($data['shippingAddress']))
                {
                    $address = new Address();
                    foreach ($data['shippingAddress'] as $key => $value)
                    {
                        $address->{$key}  = $value;
                    }
                    $model->shippingAddress = $address;
                }
                else
                {
                    $model->shippingAddress = null;
                }

                $saved = $model->save();
                $outputArray = array();
                if ($saved)
                {
                    $model = Account::getById($id);
                    $util  = new RedBeanModelToApiDataUtil($model);
                    $data  = $util->getData();

                    $outputArray['status']  = 'SUCCESS';
                    $outputArray['data']    = $data;
                    $outputArray['message'] = '';
                }
                else
                {
                    $outputArray['data'] = null;
                    $outputArray['status'] = 'FAILURE';
                    $outputArray['message'] = Yii::t('Default', 'Model could not be saved.');
                }
            }
            catch (Exception $e)
            {
                $outputArray['data'] = null;
                $outputArray['status'] = 'FAILURE';
                $outputArray['message'] = $e->getMessage();
            }
            return $outputArray;
        }

        public function delete($id)
        {
            try
            {
                $model = Account::getById($id);
                $isAllowed = ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($model);
                if ($isAllowed === false)
                {
                    throw new Exception('This action is not allowed.');
                }
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
    }
?>
