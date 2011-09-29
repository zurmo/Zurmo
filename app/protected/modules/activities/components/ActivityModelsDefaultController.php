<?php
    /**
     * Activities Modules such as Meetings, Notes, and tasks
     * should extend this class to provide generic actions that are uniform across these models.
     */
    abstract class ActivityModelsDefaultController extends ActivitiesModuleController
    {
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
            $pageViewClassName = $this->getPageViewClassName();
            $view = new $pageViewClassName($this,
                $this->makeTitleBarAndEditAndDetailsView(
                    $this->attemptToSaveModelFromPost($activity, $redirectUrl), 'Details'));
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