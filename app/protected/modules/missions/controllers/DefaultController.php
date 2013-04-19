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

    class MissionsDefaultController extends ZurmoModuleController
    {
        public function filters()
        {
            return array_merge(parent::filters(),
                array(
                    array(
                        ZurmoModuleController::ZERO_MODELS_CHECK_FILTER_PATH . ' + list, index',
                        'controller' => $this,
                   ),
               )
            );
        }

        public function actionIndex()
        {
            $this->actionList(MissionsListConfigurationForm::LIST_TYPE_AVAILABLE);
        }

        public function actionList($type = null)
        {
            $missionsMashableInboxUrl = Yii::app()->createUrl('mashableInbox/default/list',
                                             array('modelClassName' => 'Mission'));
            $this->redirect($missionsMashableInboxUrl);
        }

        public function actionDetails($id)
        {
            $mission = static::getModelAndCatchNotFoundAndDisplayError('Mission', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($mission);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED,
                                      array(strval($mission), 'MissionsModule'), $mission);
            MissionsUtil::markUserHasReadLatest($mission, Yii::app()->user->userModel);
            $detailsView              = new MissionDetailsView($this->getId(), $this->getModule()->getId(), $mission);
            $missionsMashableInboxUrl = Yii::app()->createUrl('mashableInbox/default/list',
                                             array('modelClassName' => 'Mission'));
            $breadcrumbLinks = array(Zurmo::t('MissionsModule', 'Missions') =>
                                            $missionsMashableInboxUrl,
                                     StringUtil::getChoppedStringContent(strval($mission), 25));
            $view     = new MissionsPageView(ZurmoDefaultViewUtil::
                                             makeViewWithBreadcrumbsForCurrentUser($this, $detailsView, $breadcrumbLinks,
                                                                                    'MissionBreadCrumbView'));
            echo $view->render();
        }

        public function actionCreate()
        {
            $mission         = new Mission();
            $mission->status = Mission::STATUS_AVAILABLE;
            //Set everyone with read/write access on save
            if (isset($_POST['Mission']))
            {
                $_POST['Mission']['explicitReadWriteModelPermissions']['type'] = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;
            }
            $editView = new MissionEditView($this->getId(), $this->getModule()->getId(),
                                                 $this->attemptToSaveModelFromPost($mission),
                                                 Zurmo::t('MissionsModule', 'Create Mission'));
            $missionsMashableInboxUrl = Yii::app()->createUrl('mashableInbox/default/list',
                                             array('modelClassName' => 'Mission'));
            $breadcrumbLinks = array(Zurmo::t('MissionsModule', 'Missions') =>
                                            $missionsMashableInboxUrl,
                                     Zurmo::t('MissionsModule', 'Create'));
            $view     = new MissionsPageView(ZurmoDefaultViewUtil::
                                             makeViewWithBreadcrumbsForCurrentUser($this, $editView, $breadcrumbLinks,
                                                                                    'MissionBreadCrumbView'));
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $mission  = Mission::getById(intval($id));
            MissionAccessUtil::resolveCanCurrentUserWriteOrDeleteMission($mission);
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($mission);
            $editView = new MissionEditView($this->getId(), $this->getModule()->getId(),
                                                 $this->attemptToSaveModelFromPost($mission),
                                                 strval($mission));
            $missionsMashableInboxUrl = Yii::app()->createUrl('mashableInbox/default/list',
                                             array('modelClassName' => 'Mission'));
            $breadcrumbLinks = array(Zurmo::t('MissionsModule', 'Missions') =>
                                        $missionsMashableInboxUrl,
                                     StringUtil::getChoppedStringContent(strval($mission), 25) =>
                                        array('default/details',  'id' => $id), Zurmo::t('MissionsModule', 'Edit'));
            $view     = new MissionsPageView(ZurmoDefaultViewUtil::
                                             makeViewWithBreadcrumbsForCurrentUser($this, $editView, $breadcrumbLinks,
                                                                                    'MissionBreadCrumbView'));
            echo $view->render();
        }

        protected static function getZurmoControllerUtil()
        {
            return new MissionZurmoControllerUtil();
        }

        public function actionDelete($id)
        {
            $mission = Mission::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($mission);
            $mission->delete();
            $this->redirect(array($this->getId() . '/index'));
        }

        public function actionInlineCreateCommentFromAjax($id, $uniquePageId)
        {
            $comment       = new Comment();
            $redirectUrl   = Yii::app()->createUrl('/missions/default/inlineCreateCommentFromAjax',
                                                    array('id'           => $id,
                                                          'uniquePageId' => $uniquePageId));
            $urlParameters = array('relatedModelId'           => (int)$id,
                                   'relatedModelClassName'    => 'Mission',
                                   'relatedModelRelationName' => 'comments',
                                   'redirectUrl'              => $redirectUrl); //After save, the url to go to.
            $uniquePageId  = 'CommentInlineEditForModelView';
            echo             ZurmoHtml::tag('h2', array(), Zurmo::t('MissionsModule', 'Add Comment'));
            $inlineView    = new CommentInlineEditView($comment, 'default', 'comments', 'inlineCreateSave',
                                                       $urlParameters, $uniquePageId);
            $view          = new AjaxPageView($inlineView);
            echo $view->render();
        }

        public function actionAjaxChangeStatus($status, $id)
        {
            $content         = null;
            $save            = true;
            $mission         = Mission::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($mission);
            if ($status == Mission::STATUS_TAKEN)
            {
                if ($mission->takenByUser->id > 0)
                {
                    $save = false;
                }
                else
                {
                    $mission->takenByUser = Yii::app()->user->userModel;
                }
            }
            if ($save)
            {
                $mission->status = $status;
                $saved           = $mission->save();
                if (!$saved)
                {
                    throw new NotSupportedException();
                }
                $statusText        = MissionStatusElement::renderStatusTextContent($mission);
                $statusAction      = MissionStatusElement::renderStatusActionContent($mission, MissionStatusElement::getStatusChangeDivId($mission->id));
                $content          .= $statusText;
                if ($statusAction != null)
                {
                    $content .= ' ' . $statusAction;
                }
            }
            else
            {
                $content .= '<div>' . Zurmo::t('MissionsModule', 'This mission is already taken') . '</div>';
            }
            $content = ZurmoHtml::tag('div', array('id'    => MissionStatusElement::getStatusChangeDivId($mission->id),
                                                   'class' => 'missionStatusChangeArea'), $content);
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }
    }
?>
