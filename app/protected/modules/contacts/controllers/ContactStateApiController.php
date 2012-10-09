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
    * Contact State API Controller
    */
    class ContactsContactStateApiController extends ZurmoModuleApiController
    {
        protected function getModelName()
        {
            return 'ContactState';
        }

        protected static function getSearchFormClassName()
        {
            return 'ContactStateSearchForm';
        }

        /**
        * We cant use Module::getStateMetadataAdapterClassName() because that references
        * to Contact model and we are using ContactState model.
        */
        public function getStateMetadataAdapterClassName()
        {
            return null;
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

         public function actionListContactStates()
        {
            $this->sendStatesByLeadOrContact('contact');
        }

        public function actionListLeadStates()
        {
            $this->sendStatesByLeadOrContact('lead');
        }

        /**
         * Get states by type.
         * @param string $state
         * @throws ApiException
         */
        protected function sendStatesByLeadOrContact($state = 'contact')
        {
            try
            {
                $states = array();
                if ($state =='contact')
                {
                    $states = ContactsUtil::getContactStateDataFromStartingStateLabelByLanguage(Yii::app()->language);
                }
                elseif ($state == 'lead')
                {
                    $states = LeadsUtil::getLeadStateDataFromStartingStateLabelByLanguage(Yii::app()->language);
                }
                foreach ($states as $model)
                {
                    $redBeanModelToApiDataUtil  = new RedBeanModelToApiDataUtil($model);
                    $data['items'][] = $redBeanModelToApiDataUtil->getData();
                }

                $data['totalCount'] = count($data['items']);
                $data['currentPage'] = 1;
                $result = new ApiResult(ApiResponse::STATUS_SUCCESS, $data, null, null);
                Yii::app()->apiHelper->sendResponse($result);
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
                throw new ApiException($message);
            }
        }
    }
?>