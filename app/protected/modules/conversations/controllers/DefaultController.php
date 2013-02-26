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

    class ConversationsDefaultController extends ZurmoModuleController
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
            $this->actionList(ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_PARTICIPANT);
        }

        public function actionList($type = null)
        {
            $pageSize         = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                'listPageSize', get_class($this->getModule()));
            $conversation     = new Conversation(false);
            if ($type == null)
            {
                $type = ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_CREATED;
            }
            if ($type == ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_CREATED)
            {
                $activeActionElementType = 'ConversationsCreatedLink';
            }
            elseif ($type == ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_PARTICIPANT)
            {
                $activeActionElementType = 'ConversationsParticipantLink';
            }
            elseif ($type == ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_CLOSED)
            {
                $activeActionElementType = 'ConversationsClosedLink';
            }
            else
            {
                throw new NotSupportedException();
            }
            $searchAttributes = array();
            $metadataAdapter  = new ConversationsSearchDataProviderMetadataAdapter(
                $conversation,
                Yii::app()->user->userModel->id,
                $searchAttributes,
                $type
            );
            $dataProvider = RedBeanModelDataProviderUtil::makeDataProvider(
                $metadataAdapter->getAdaptedMetadata(),
                'Conversation',
                'RedBeanModelDataProvider',
                'latestDateTime',
                true,
                $pageSize
            );
            $actionBarAndListView = new ActionBarAndListView(
                $this->getId(),
                $this->getModule()->getId(),
                $conversation,
                'Conversations',
                $dataProvider,
                array(),
                'ConversationsActionBarForListView',
                $activeActionElementType
            );
            $view = new ConversationsPageView(ZurmoDefaultViewUtil::
                                              makeStandardViewForCurrentUser($this, $actionBarAndListView));
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $conversation = static::getModelAndCatchNotFoundAndDisplayError('Conversation', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($conversation);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED,
                                      array(strval($conversation), 'ConversationsModule'), $conversation);
            ConversationsUtil::markUserHasReadLatest($conversation, Yii::app()->user->userModel);
            $detailsView              = new ConversationDetailsView($this->getId(), $this->getModule()->getId(), $conversation);
            $breadcrumbLinks          = array(StringUtil::getChoppedStringContent(strval($conversation), 25));
            $view     = new ConversationsPageView(ZurmoDefaultViewUtil::
                                                  makeViewWithBreadcrumbsForCurrentUser($this, $detailsView, $breadcrumbLinks,
                                                                                        'ConversationBreadCrumbView'));

            echo $view->render();
        }

        public function actionCreate()
        {
            $editView = new ConversationEditView($this->getId(), $this->getModule()->getId(),
                                                 $this->attemptToSaveModelFromPost(new Conversation()),
                                                 Zurmo::t('ConversationsModule', 'Create Conversation'));
            $view     = new ConversationsPageView(ZurmoDefaultViewUtil::
                                                  makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $conversation = Conversation::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($conversation);
            $editView = new ConversationEditView($this->getId(), $this->getModule()->getId(),
                                                 $this->attemptToSaveModelFromPost($conversation),
                                                 strval($conversation));
            $view     = new ConversationsPageView(ZurmoDefaultViewUtil::
                                                  makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }

        protected static function getZurmoControllerUtil()
        {
            return new ConversationZurmoControllerUtil('conversationItems', 'ConversationItemForm',
                                                       'ConversationParticipantsForm');
        }

        public function actionUpdateParticipants($id)
        {
            $postData     = PostUtil::getData();
            if (isset($postData['ConversationParticipantsForm']))
            {
                $conversation                      = Conversation::getById((int)$id);
                $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($conversation);
                $currentUserWasParticipant         = ConversationParticipantsUtil::isUserAParticipant($conversation, Yii::app()->user->userModel);
                $peopleAdded                       = ConversationParticipantsUtil::
                                                     resolveConversationHasManyParticipantsFromPost($conversation,
                                                                   $postData['ConversationParticipantsForm'],
                                                                   $explicitReadWriteModelPermissions);
                ConversationParticipantsUtil::resolveEmailInvitesByPeople($conversation, $peopleAdded);
                $saved = $conversation->save();
                if ($saved)
                {
                    $success                   = ExplicitReadWriteModelPermissionsUtil::
                                                 resolveExplicitReadWriteModelPermissions($conversation,
                                                                                          $explicitReadWriteModelPermissions);
                    $currentUserIsParticipant  = ConversationParticipantsUtil::isUserAParticipant($conversation, Yii::app()->user->userModel);
                    if ($currentUserWasParticipant && !$currentUserIsParticipant)
                    {
                        echo 'redirectToList';
                    }
                }
                else
                {
                    throw new FailedToSaveModelException();
                }
            }
        }

        public function actionDelete($id)
        {
            $conversation = Conversation::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($conversation);
            $conversation->delete();
            $this->redirect(array($this->getId() . '/index'));
        }

        /**
         * (non-PHPdoc)
         * @see ZurmoModuleController::actionCreateFromRelation()
         */
        public function actionCreateFromRelation($relationAttributeName, $relationModelId, $relationModuleId, $redirectUrl)
        {
            $getData              = GetUtil::getData();
            if (null == ArrayUtil::getArrayValue($getData, 'relationModelClassName'))
            {
                throw new NotSupportedException();
            }
            $conversation         = $this->resolveNewModelByRelationInformation( new Conversation(),
                                                                                ArrayUtil::getArrayValue($getData, 'relationModelClassName'),
                                                                                (int)$relationModelId,
                                                                                $relationModuleId);
            $this->actionCreateByModel($conversation, $redirectUrl);
        }

        protected function actionCreateByModel(Conversation $conversation, $redirectUrl)
        {
            $editView = new ConversationEditView($this->getId(), $this->getModule()->getId(),
                                                 $this->attemptToSaveModelFromPost($conversation, $redirectUrl),
                                                 Zurmo::t('ConversationsModule', 'Create Conversation'));
            $view     = new ConversationsPageView(ZurmoDefaultViewUtil::
                                             makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }

        /**
         * Override to handle the special scenario of relations for a conversation. Since relations are done in the
         * ConversationItems, the relation information needs to handled in a specific way.
         * @see ZurmoModuleController->resolveNewModelByRelationInformation
         */
        protected function resolveNewModelByRelationInformation(    $model, $relationModelClassName,
                                                                    $relationModelId, $relationModuleId)
        {
            assert('$model instanceof Conversation');
            assert('is_string($relationModelClassName) || null');
            assert('is_int($relationModelId)');
            assert('is_string($relationModuleId)');

            $metadata = Conversation::getMetadata();
            if (in_array($relationModelClassName, $metadata['Conversation']['conversationItemsModelClassNames']))
            {
                $model->conversationItems->add($relationModelClassName::getById((int)$relationModelId));
            }
            else
            {
                throw new NotSupportedException();
            }
            return $model;
        }

        public function actionInlineCreateCommentFromAjax($id, $uniquePageId)
        {
            $comment       = new Comment();
            $redirectUrl   = Yii::app()->createUrl('/conversations/default/inlineCreateCommentFromAjax',
                                                    array('id'           => $id,
                                                          'uniquePageId' => $uniquePageId));
            $urlParameters = array('relatedModelId'           => (int)$id,
                                   'relatedModelClassName'    => 'Conversation',
                                   'relatedModelRelationName' => 'comments',
                                   'redirectUrl'              => $redirectUrl); //After save, the url to go to.
            $uniquePageId  = 'CommentInlineEditForModelView';
            echo             ZurmoHtml::tag('h2', array(), Zurmo::t('CovnersationsModule', 'Add Comment'));
            $inlineView    = new CommentInlineEditView($comment, 'default', 'comments', 'inlineCreateSave',
                                                       $urlParameters, $uniquePageId);
            $view          = new AjaxPageView($inlineView);
            echo $view->render();
        }

        public function actionChangeIsClosed($id)
        {
            $conversation           = Conversation::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($conversation);
            $conversation->isClosed = !($conversation->isClosed);
            $saved                  = $conversation->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            echo true;
        }
    }
?>
