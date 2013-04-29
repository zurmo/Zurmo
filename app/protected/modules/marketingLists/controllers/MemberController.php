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

    class MarketingListsMemberController extends ZurmoModuleController
    {
        public function filters()
        {
            $filters = parent::filters();
            unset($filters['RIGHT_BULK_DELETE']);
            return $filters;
        }

        public function actionMassDelete()
        {
            $this->triggerMarketingListMemberMassAction();
        }

        public function actionMassDeleteProgress()
        {
            $this->triggerMarketingListMemberMassAction();
        }

        public function actionMassSubscribe()
        {
            $this->triggerMarketingListMemberMassAction();
        }

        public function actionMassSubscribeProgress()
        {
            $this->triggerMarketingListMemberMassAction();
        }

        public function actionMassUnsubscribe()
        {
            $this->triggerMarketingListMemberMassAction();
        }

        public function actionMassUnsubscribeProgress()
        {
            $this->triggerMarketingListMemberMassAction();
        }

        protected static function getSearchFormClassName()
        {
            return 'MarketingListMembersSearchForm';
        }

        protected function triggerMarketingListMemberMassAction()
        {
            $this->triggerMassAction('MarketingListMember',
                                        static::getSearchFormClassName(),
                                        'MarketingListMembersPageView',
                                        MarketingListMember::getModelLabelByTypeAndLanguage('Plural'),
                                        'MarketingListMembersSearchView',
                                        null,
                                        false);
        }

        protected static function processModelForMassSubscribe(& $model)
        {
            return static::processModelForMassSubscribeOrUnsubscribe($model, false);
        }

        protected static function processModelForMassUnsubscribe(& $model)
        {
            return static::processModelForMassSubscribeOrUnsubscribe($model, true);
        }

        protected static function processModelForMassSubscribeOrUnsubscribe(& $model, $unsubscribed)
        {
            $model->unsubscribed = $unsubscribed;
            if (!$model->unrestrictedSave())
            {
                throw new FailedToSaveModelException();
            }
            else
            {
                return true;
            }
        }

        protected static function resolveTitleByMassActionId($actionId)
        {
            if (strpos($actionId, 'massSubscribe') === 0 || strpos($actionId, 'massUnsubscribe') === 0)
            {
                $term = 'Mass '. ucfirst(str_replace('mass', '', $actionId));
                return Zurmo::t('MarketingListsModule', $term);
            }
            else
            {
                return parent::resolveTitleByMassActionId($actionId);
            }
        }

        protected function resolveReturnUrlForMassAction()
        {
            return $this->createUrl('/' . $this->getModule()->getId() . '/default/details',
                                                            array('id' => intval(Yii::app()->request->getQuery('id'))));
        }

        protected static function resolvePageValueForMassAction($modelClassName)
        {
            $pageValue = parent::resolvePageValueForMassAction($modelClassName);
            if ($pageValue)
            {
                return $pageValue;
            }
            else
            {
                return intval(Yii::app()->request->getQuery('MarketingListMembersForPortletView_page'));
            }
        }

        protected static function resolveViewIdByMassActionId($actionId, $returnProgressViewName, $moduleName = null)
        {
            if (strpos($actionId, 'massSubscribe') === 0 || strpos($actionId, 'massUnsubscribe') === 0)
            {
                $viewNameSuffix    = (!$returnProgressViewName)? 'View': 'ProgressView';
                $viewNamePrefix    = static::resolveMassActionId($actionId, true);
                $viewNamePrefix    = 'MarketingListMembers' . $viewNamePrefix;
                return $viewNamePrefix . $viewNameSuffix;
            }
            else
            {
                return parent::resolveViewIdByMassActionId($actionId, $returnProgressViewName);
            }
        }

        protected function resolveMetadataBeforeMakingDataProvider(& $metadata)
        {
            $metadata = array(
                            'clauses'   => array(
                                        1   => array(
                                                'attributeName' => 'marketingList',
                                                'operatorType'  => 'equals',
                                                'value'         => Yii::app()->request->getQuery('id')
                                            ),
                                        ),
                            'structure' => 1
                        );
        }
    }
?>