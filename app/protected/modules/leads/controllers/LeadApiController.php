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

    class LeadApiController extends ZurmoModuleApiController
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
                    $_GET['Contact_page'] = $filterParams['pagination']['page'];
                }

                if (isset($filterParams['sort']))
                {
                    $_GET['Contact_sort'] = $filterParams['sort'];
                }


                if (isset($filterParams['search']))
                {
                    $_GET['LeadsSearchForm'] = $filterParams['search'];
                }

                $stateMetadataAdapterClassName = 'LeadsStateMetadataAdapter';
                $stateMetadataAdapterClassName = null;
                $lead= new Contact(false);
                $searchForm = new LeadsSearchForm($lead);

                $dataProvider = $this->makeRedBeanDataProviderFromGet(
                    $searchForm,
                    'Contact',
                    $pageSize,
                    Yii::app()->user->userModel->id,
                    $stateMetadataAdapterClassName);

                $totalItems = $dataProvider->getTotalItemCount();;
                $outputArray = array();
                $outputArray['data']['total'] = $totalItems;

                if ($totalItems > 0)
                {

                    $outputArray['status'] = 'SUCCESS';
                    $outputArray['message'] = '';

                    $data = $dataProvider->getData();
                    foreach ($data as $lead)
                    {
                        $util  = new RedBeanModelToApiDataUtil($lead);
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
                $outputArray['status'] = 'FAILURE';
                $outputArray['message'] = $e->getMessage();
            }
            return $outputArray;
        }

        public function getById($id)
        {
            try
            {
                $model = Contact::getById($id);
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
                $model= new Contact();

                if (isset($data['firstName']))
                {
                    $model->firstName            = $data['firstName'];
                }
                if (isset($data['lastName']))
                {
                    $model->lastName     = $data['lastName'];
                }
                if (isset($data['jobTitle']))
                {
                    $model->jobTitle       = $data['jobTitle'];
                }
                if (isset($data['department']))
                {
                    $model->department       = $data['department'];

                }
                if (isset($data['officePhone']))
                {
                    $model->officePhone         = $data['officePhone'];
                }
                if (isset($data['mobilePhone']))
                {
                    $model->mobilePhone   = $data['mobilePhone'];
                }
                if (isset($data['officeFax']))
                {
                    $model->officeFax     = $data['officeFax'];
                }

                if (isset($data['description']))
                {
                    $model->description     = $data['description'];
                }
                if (isset($data['companyName']))
                {
                    $model->companyName     = $data['companyName'];
                }
                if (isset($data['website']))
                {
                    $model->website     = $data['website'];
                }

                if (isset($data['title']))
                {
                    $model->title->value        = $data['title']['value'];
                }
                if (isset($data['industry']))
                {
                    $model->industry->value        = $data['industry']['value'];
                }
                if (isset($data['source']))
                {
                    $model->source->value        = $data['source']['value'];
                };
                if (isset($data['account']))
                {
                    $account = Account::getById($data['account']['id']);
                    $model->account        = $account;

                }
                if (isset($data['state']))
                {
                    $leadState = ContactState::getById($data['state']['id']);
                    $model->state        = $leadState;
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

                if (isset($data['primaryAddress']))
                {
                    $address = new Address();
                    foreach ($data['primaryAddress'] as $key => $value)
                    {
                        $address->{$key}  = $value;
                    }
                    $model->primaryAddress = $address;
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
                if (isset($data['secondaryAddress']))
                {
                    $address = new Address();
                    foreach ($data['secondaryAddress'] as $key => $value)
                    {
                        $address->{$key}  = $value;
                    }
                    $model->secondaryAddress = $address;
                }
                $saved = $model->save();
                $id = $model->id;
                $model->forget();
                unset($model);
                $outputArray = array();
                if ($saved)
                {
                    $model = Contact::getById($id);
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
                $model = Contact::getById($id);
                $isAllowed = ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($model);
                if ($isAllowed === false)
                {
                    throw new Exception('This action is not allowed.');
                }
                if (isset($data['firstName']))
                {
                    $model->firstName            = $data['firstName'];
                }
                else
                {
                    $model->firstName = null;
                }

                if (isset($data['lastName']))
                {
                    $model->lastName     = $data['lastName'];
                }
                else
                {
                    $model->lastName = null;
                }

                if (isset($data['jobTitle']))
                {
                    $model->jobTitle       = $data['jobTitle'];
                }
                else
                {
                    $model->jobTitle = null;
                }

                if (isset($data['department']))
                {
                    $model->department       = $data['department'];
                }
                else
                {
                    $model->department = null;
                }

                if (isset($data['officePhone']))
                {
                    $model->officePhone         = $data['officePhone'];
                }
                else
                {
                    $model->officePhone = null;
                }

                if (isset($data['mobilePhone']))
                {
                    $model->mobilePhone   = $data['mobilePhone'];
                }
                else
                {
                    $model->mobilePhone = null;
                }

                if (isset($data['officeFax']))
                {
                    $model->officeFax     = $data['officeFax'];
                }
                else
                {
                    $model->officeFax = null;
                }

                if (isset($data['description']))
                {
                    $model->description     = $data['description'];
                }
                else
                {
                    $model->description = null;
                }

                if (isset($data['companyName']))
                {
                    $model->companyName     = $data['companyName'];
                }
                else
                {
                    $model->companyName = null;
                }

                if (isset($data['website']))
                {
                    $model->website     = $data['website'];
                }
                else
                {
                    $model->website = null;
                }

                if (isset($data['title']))
                {
                    $model->title->value        = $data['title']['value'];
                }
                else
                {
                    $model->title = null;
                }

                if (isset($data['industry']))
                {
                    $model->industry->value        = $data['industry']['value'];
                }
                else
                {
                    $model->industry = null;
                }

                if (isset($data['source']))
                {
                    $model->source->value        = $data['source']['value'];
                }
                else
                {
                    $model->source = null;
                }

                if (isset($data['account']))
                {
                    $account = Account::getById($data['account']['id']);
                    $model->account        = $account;
                }
                else
                {
                    $model->account = null;
                }

                if (isset($data['state']))
                {
                    $leadState = ContactState::getById($data['state']['id']);
                    $model->state        = $leadState;
                }
                else
                {
                    $model->state = null;
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
                else
                {
                    $model->primaryEmail = null;
                }

                if (isset($data['primaryAddress']))
                {
                    $address = new Address();
                    foreach ($data['primaryAddress'] as $key => $value)
                    {
                        $address->{$key}  = $value;
                    }
                    $model->primaryAddress = $address;
                }
                else
                {
                    $model->primaryAddress = null;
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
                else
                {
                    $model->secondaryEmail = null;
                }

                if (isset($data['secondaryAddress']))
                {
                    $address = new Address();
                    foreach ($data['secondaryAddress'] as $key => $value)
                    {
                        $address->{$key}  = $value;
                    }
                    $model->secondaryAddress = $address;
                }
                else
                {
                    $model->secondaryAddress = null;
                }

                $saved = $model->save();
                $outputArray = array();
                if ($saved)
                {
                    $model = Contact::getById($id);
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
                $model = Contact::getById($id);
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
