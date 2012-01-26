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
     * Zurmo Modules such as Accounts, Contacts, and Opportunities
     * should extend this class to provide generic functionality
     * that is applicable to all standard modules.
     */
    abstract class ZurmoModuleController extends ZurmoBaseController
    {
        public function actionIndex()
        {
            $this->actionList();
        }

        public function actionMassExport()
        {
            echo 'mass export<br/>';
            echo 'not implemented yet';
            exit;
        }

        /**
         * In a detailview, if you click the 'select' link from a sub view, this action is called. It will bring a modal
         * search/list view to select a model from.
         * @param string $portletId
         * @param string $uniqueLayoutId
         * @param string $relationAttributeName
         * @param string $relationModelId
         * @param string $relationModuleId
         * @param string $pageTitle
         */
        public function actionSelectFromRelatedList($portletId,
                                                    $uniqueLayoutId,
                                                    $relationAttributeName,
                                                    $relationModelId,
                                                    $relationModuleId,
                                                    $pageTitle = null,
                                                    $stateMetadataAdapterClassName = null)
        {
            $portlet = Portlet::getById((int)$portletId);
            $modalListLinkProvider = new SelectFromRelatedListModalListLinkProvider(
                                            $relationAttributeName,
                                            (int)$relationModelId,
                                            $relationModuleId,
                                            $portlet->getUniquePortletPageId(),
                                            $uniqueLayoutId,
                                            (int)$portlet->id,
                                            $this->getModule()->getId()
            );
            echo ModalSearchListControllerUtil::
                 setAjaxModeAndRenderModalSearchList($this, $modalListLinkProvider, $pageTitle,
                                                     $stateMetadataAdapterClassName);
        }

        public function actionAutoComplete($term)
        {
            $modelClassName = $this->getModule()->getPrimaryModelName();
            echo $this->renderAutoCompleteResults($modelClassName, $term);
        }

        protected function renderAutoCompleteResults($modelClassName, $term)
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'autoCompleteListPageSize', get_class($this->getModule()));
            $autoCompleteResults = ModelAutoCompleteUtil::getByPartialName($modelClassName, $term, $pageSize);
            return CJSON::encode($autoCompleteResults);
        }

        /**
         * Override to implement.
         */
        public function actionCreateFromRelation($relationAttributeName, $relationModelId, $relationModuleId, $redirectUrl)
        {
            throw new NotImplementedException();
        }

        /**
         * @see actionCreateFromRelation. When a new model is instantiated, this method attaches a relation based
         * on the relation information specified.
         * @return $model;
         */
        protected function resolveNewModelByRelationInformation(    $model, $relationAttributeName,
                                                                    $relationModelId, $relationModuleId)
        {
            assert('$model instanceof RedBeanModel');
            assert('is_string($relationAttributeName)');
            assert('is_int($relationModelId)');
            assert('is_string($relationModuleId)');
            $relationType = $model->getRelationType($relationAttributeName);
            if ($relationType == RedBeanModel::HAS_ONE || RedBeanModel::HAS_ONE_BELONGS_TO)
            {
                $relationModel                   = $model->$relationAttributeName;
                $model->$relationAttributeName = $relationModel::getById((int)$relationModelId);
            }
            else
            {
                $relationModelClassName          = Yii::app()->getModule($relationModuleId)->getPrimaryModelName();
                $relatedModel                    = $relationModelClassName::getById($relationModelId);
                $model->$relationAttributeName->add($relatedModel);
            }
            return $model;
        }

        public function actionAuditEventsModalList($id)
        {
            $modelClassName = $this->getModule()->getPrimaryModelName();
            $model = $modelClassName::getById((int)$id);
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($model);
            $searchAttributeData = AuditEventsListControllerUtil::makeSearchAttributeDataByAuditedModel($model);
            $dataProvider = AuditEventsListControllerUtil::makeDataProviderBySearchAttributeData($searchAttributeData);
            Yii::app()->getClientScript()->setToAjaxMode();
            echo AuditEventsListControllerUtil::renderList($this, $dataProvider,
                                                           strval($model) . ' ' . Yii::t('Default', 'Audit Trail'));
        }
    }
?>