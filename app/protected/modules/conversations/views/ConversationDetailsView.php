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

    class ConversationDetailsView extends SecuredDetailsView
    {
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type' => 'EditLink'),
                            array('type' => 'ConversationDeleteLink'),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        protected function renderFormLayout($form = null)
        {
            //override since the details are done @see renderConversationContent
        }

        public function getTitle()
        {
            return strval($this->model);
        }

        protected function renderRightSideContent($form = null)
        {
            assert('$form == null');
            $content  = '<div id="right-side-edit-view-panel"><div class="buffer"><div>';
            $content .= "<h3>".Yii::t('Default', 'Participants') . '</h3>';
            $content .= $this->renderConversationParticipantsContent();
            $content .= '</div></div></div>';
            return $content;
        }

        protected function renderAfterFormLayoutForDetailsContent()
        {
            $content  = $this->renderConversationContent();
            $content .= $this->renderConversationCommentsContent();
            $content .= $this->renderConversationCreateCommentContent();
            return $content;
        }

        protected function renderConversationParticipantsContent()
        {
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                                'ZurmoActiveForm',
                                                                array_merge(
                                                                    array('id' => 'participants-edit-form')
                                                                )
                                                            );
            $params   = array('formName' => 'participants-edit-form');
            $content  = $formStart;
            $element  = new OnChangeProcessMultiplePeopleForConversationElement($this->model, null, $form, $params);
            $element->editableTemplate = '{content}{error}';
            $content .= $element->render();
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            return $content;
        }

        protected function renderConversationContent()
        {
            $userUrl  = Yii::app()->createUrl('/users/default/details', array('id' => $this->model->createdByUser->id));
            $content  = '<div class="comment model-details-summary clearfix">';
            $content .= ZurmoHtml::link($this->model->createdByUser->getAvatarImage(100), $userUrl);
            $content .= '<span class="user-details">';
            $content .= ZurmoHtml::link(strval($this->model->createdByUser), $userUrl, array('class' => 'user-link'));
            $content .= '</span>';

            if ($this->model->description == null)
            {
                $element  = new TextElement($this->model, 'subject');
            }
            else
            {
                $element  = new TextAreaElement($this->model, 'description');
            }
            $element->nonEditableTemplate = '<div class="comment-content">{content}</div>';
            $content .= $element->render();

            $date = '<span class="comment-details"><strong>'. DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                                              $this->model->createdDateTime, 'long', null) . '</strong></span>';
            $content .= $date;
            if ($this->model->files->count() > 0)
            {
                $element  = new FilesElement($this->model, 'null');
                $element->nonEditableTemplate = '<div>{content}</div>';
                $content .= '<div><strong>' . Yii::t('Default', 'Attachments'). '</strong></div>';
                $content .= $element->render();
            }
            $element  = new ConversationItemsElement($this->model, 'null');
            $element->nonEditableTemplate = '<div>{content}</div>';
            $content .= $element->render();
            $content .= '</div>';
            return ZurmoHtml::tag('div', array('id' => 'ModelDetailsSummaryView'), $content);
        }

        protected function renderConversationCommentsContent()
        {
            $getParams    = array('relatedModelId'           => $this->model->id,
                                  'relatedModelClassName'    => get_class($this->model),
                                  'relatedModelRelationName' => 'comments');
            $pageSize     = 5;
            $commentsData = Comment::getCommentsByRelatedModelTypeIdAndPageSize(get_class($this->model),
                                                                                $this->modelId, ($pageSize + 1));
            $view         = new CommentsForRelatedModelView('default', 'comments', $commentsData, $this->model, $pageSize, $getParams);
            $content      = $view->render();
            return $content;
        }

        protected function renderConversationCreateCommentContent()
        {
            $content       = ZurmoHtml::tag('h2', array(), Yii::t('Default', 'Add Comment'));
            $comment       = new Comment();
            $uniquePageId  = 'CommentInlineEditForModelView';
            $redirectUrl   = Yii::app()->createUrl('/conversations/default/inlineCreateCommentFromAjax',
                                                    array('id' => $this->model->id,
                                                          'uniquePageId' => $uniquePageId));
            $urlParameters = array('relatedModelId'           => $this->model->id,
                                   'relatedModelClassName'    => 'Conversation',
                                   'relatedModelRelationName' => 'comments',
                                   'redirectUrl'              => $redirectUrl); //After save, the url to go to.

            $inlineView    = new CommentInlineEditView($comment, 'default', 'comments', 'inlineCreateSave',
                                                      $urlParameters, $uniquePageId);
            $content      .= $inlineView->render();
            return ZurmoHtml::tag('div', array('id' => 'CommentInlineEditForModelView'), $content);
        }

        protected function getPortletDetailsUrl()
        {
            return Yii::app()->createUrl('/conversations/default/inlineCreateComment');
        }
    }
?>
