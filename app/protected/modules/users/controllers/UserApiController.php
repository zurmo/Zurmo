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

    class UserApiController extends ZurmoModuleApiController
    {
        public function getById($modelClassName, $id)
        {
            try
            {
                $model = User::getById($id);

                $isAllowed = ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($model);
                if ($isAllowed === false)
                {
                    throw new Exception('This action is not allowed.');
                }

                if ($this->resolveCanCurrentUserAccessAction($id))
                {
                    $util  = new RedBeanModelToApiDataUtil($model);
                    $data  = $util->getData();
                    $outputArray = array();
                    $outputArray['status'] = 'SUCCESS';
                    $outputArray['data']   = $data;
                    $outputArray['message'] = '';
                }
                else
                {
                    $outputArray['data'] = null;
                    $outputArray['status'] = 'FAILURE';
                    $outputArray['message'] = Yii::t('Default', 'This action is not allowed.');
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

        public function create($data)
        {
            try
            {
                $model= new User();

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

                if (isset($data['username']))
                {
                    $model->username     = $data['username'];
                }
                if (isset($data['password']) && $data['password'] != '')
                {
                    $model->hash     = md5($data['username']);
                }
                if (isset($data['language']))
                {
                    $model->language     = $data['language'];
                }
                if (isset($data['timeZone']))
                {
                    $model->timeZone     = $data['timeZone'];
                }

                if (isset($data['title']))
                {
                    $model->title->value        = $data['title']['value'];
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

                if (isset($data['currency']))
                {
                    //$currency    = Currency::getById($data['currency']['id']);
                    //$model->currency = $currency;
                }
                if (isset($data['manager']))
                {
                    $manager = User::getById($data['manager']['id']);
                    $model->manager        = $manager;
                }

                $saved = $model->save();
                $id = $model->id;
                $model->forget();
                unset($model);
                $outputArray = array();;
                if ($saved)
                {
                    $model = User::getById($id);
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
                $model = User::getById($id);
                $isAllowed = ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($model);
                if ($isAllowed === false || !$this->resolveCanCurrentUserAccessAction($id))
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
                }else
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

                if (isset($data['username']))
                {
                    $model->username     = $data['username'];
                }
                else
                {
                    $model->username = null;
                }

                if (isset($data['password']) && $data['password'] != '')
                {
                    $model->hash     = md5($data['username']);
                }


                if (isset($data['language']))
                {
                    $model->language     = $data['language'];
                }
                else
                {
                    $model->language = null;
                }

                if (isset($data['timeZone']))
                {
                    $model->timeZone     = $data['timeZone'];
                }
                else
                {
                    $model->timeZone = null;
                }


                if (isset($data['title']))
                {
                    $model->title->value        = $data['title']['value'];
                }
                else
                {
                    $model->title = null;
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


                if (isset($data['currency']))
                {
                    //$currency    = Currency::getById($data['currency']['id']);
                    //$model->currency = $currency;
                }
                else
                {
                    $model->currency = null;
                }

                if (isset($data['manager']))
                {
                    $manager = User::getById($data['manager']['id']);
                    $model->manager        = $manager;
                }
                else
                {
                    $model->manager = null;
                }

                $saved = $model->save();
                $outputArray = array();
                if ($saved)
                {
                    $model = User::getById($id);
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

        public function delete($modelClassName, $id)
        {
            try
            {
                $model = User::getById($id);
                $isAllowed = ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($model);
                if ($isAllowed === false || !$this->resolveCanCurrentUserAccessAction($id))
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

        protected function resolveCanCurrentUserAccessAction($userId)
        {
            if (Yii::app()->user->userModel->id == $userId ||
            RightsUtil::canUserAccessModule('UsersModule', Yii::app()->user->userModel))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }
?>
