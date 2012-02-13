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
     * Activities Modules such as Meetings, Notes, and tasks
     * should extend this class to provide generic actions that are uniform across these models.
     */
    abstract class ActivityModelsDefaultController extends ActivitiesModuleController
    {
        public function filters()
        {
            $modelClassName   = $this->getModule()->getPrimaryModelName();
            $viewClassName    = $modelClassName . 'EditAndDetailsView';
            return array_merge(parent::filters(),
                array(
                    array(
                        ZurmoBaseController::REQUIRED_ATTRIBUTES_FILTER_PATH . ' + create, createFromRelation, edit',
                        'moduleClassName' => get_class($this->getModule()),
                        'viewClassName'   => $viewClassName,
                   ),
               )
            );
        }

        protected function getPageViewClassName()
        {
            return $this->getModule()->getPluralCamelCasedName() . 'PageView';
        }

        public function actionCreateFromRelation($relationAttributeName, $relationModelId, $relationModuleId, $redirectUrl)
        {
            $modelClassName   = $this->getModule()->getPrimaryModelName();
            $activity         = $this->resolveNewModelByRelationInformation( new $modelClassName(),
                                                                                $relationAttributeName,
                                                                                (int)$relationModelId,
                                                                                $relationModuleId);
            $this->actionCreateByModel($activity, $redirectUrl);
        }

        protected function actionCreateByModel(Activity $activity, $redirectUrl)
        {
            $titleBarAndEditView = $this->makeTitleBarAndEditAndDetailsView(
                                            $this->attemptToSaveModelFromPost($activity, $redirectUrl), 'Edit');
            $pageViewClassName = $this->getPageViewClassName();
            $view = new $pageViewClassName($this, $titleBarAndEditView);
            echo $view->render();
        }

        public function actionDetails($id, $redirectUrl = null)
        {
            $modelClassName    = $this->getModule()->getPrimaryModelName();
            $activity          = $modelClassName::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($activity);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, strval($activity), $activity);
            $pageViewClassName = $this->getPageViewClassName();
            $view = new $pageViewClassName($this,
                $this->makeTitleBarAndEditAndDetailsView($activity, 'Details'));
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $modelClassName    = $this->getModule()->getPrimaryModelName();
            $activity          = $modelClassName::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($activity);
            $pageViewClassName = $this->getPageViewClassName();
            $view = new $pageViewClassName($this,
                $this->makeTitleBarAndEditAndDetailsView(
                    $this->attemptToSaveModelFromPost($activity, $redirectUrl), 'Edit'));
            echo $view->render();
        }

        public function actionDelete($id, $redirectUrl = null)
        {
            if ($redirectUrl == null)
            {
                $redirectUrl = array('/home/default');
            }
            $modelClassName    = $this->getModule()->getPrimaryModelName();
            $activity          = $modelClassName::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($activity);
            $activity->delete();
            $this->redirect($redirectUrl);
        }
    }
?>