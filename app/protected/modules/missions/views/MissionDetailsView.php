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

    class MissionDetailsView extends SecuredDetailsView
    {
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type' => 'EditLink'),
                            array('type' => 'MissionDeleteLink'),
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

        protected function renderAfterFormLayoutForDetailsContent()
        {
            $content  = $this->renderMissionContent();
            $content .= $this->renderMissionCommentsContent();
            $content .= $this->renderMissionCreateCommentContent();
            return $content;
        }

        protected function renderMissionContent()
        {
            $userUrl  = Yii::app()->createUrl('/users/default/details', array('id' => $this->model->createdByUser->id));
            $content  = '<div class="comment model-details-summary">';
            $content .= ZurmoHtml::link($this->model->createdByUser->getAvatarImage(100), $userUrl);
            $content .= '<span class="user-details">';
            $content .= ZurmoHtml::link(strval($this->model->createdByUser), $userUrl, array('class' => 'user-link'));
            $content .= '</span>';
            $element  = new TextAreaElement($this->model, 'description');
            $element->nonEditableTemplate = '<div class="comment-content">{content}</div>';
            $content .= $element->render();
            if ($this->model->reward != null)
            {
                $element                      = new TextElement($this->model, 'reward');
                $element->nonEditableTemplate = '<div class="comment-content">' .
                                                Zurmo::t('MissionsModule', 'Reward') . ': {content}</div>';
                $content                     .= $element->render();
            }
            if ($this->model->takenByUser->id > 0)
            {
                $element                      = new UserElement($this->model, 'takenByUser');
                $element->nonEditableTemplate = '<div class="comment-content">' .
                                                Zurmo::t('MissionsModule', 'Taken By') . ': {content}</div>';
                $content                     .= $element->render();
            }
            if (!DateTimeUtil::isDateTimeValueNull($this->model, 'dueDateTime'))
            {
                $element                      = new DateTimeElement($this->model, 'dueDateTime');
                $element->nonEditableTemplate = '<div class="comment-content">' .
                                                Zurmo::t('MissionsModule', 'Due') . ': {content}</div>';
                $content                     .= $element->render();
            }
            $date = '<span class="comment-details"><strong>'. DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                                              $this->model->createdDateTime, 'long', null) . '</strong></span>';
            $content .= $date;
            if ($this->model->files->count() > 0)
            {
                $element  = new FilesElement($this->model, 'null');
                $element->nonEditableTemplate = '<div>{content}</div>';
                $content .= '<div><strong>' . Zurmo::t('MissionsModule', 'Attachments'). '</strong></div>';
                $content .= $element->render();
            }
            $element                      = new MissionStatusElement($this->model, 'status');
            $element->nonEditableTemplate = '<div class="comment-content">' .
                                            Zurmo::t('MissionsModule', 'Status') . ': {content}</div>';
            $content                     .= $element->render();
            $content .= '</div>';
            return ZurmoHtml::tag('div', array('id' => 'ModelDetailsSummaryView'), $content);
        }

        protected function renderMissionCommentsContent()
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

        protected function renderMissionCreateCommentContent()
        {
            $content       = ZurmoHtml::tag('h2', array(), Zurmo::t('MissionsModule', 'Add Comment'));
            $comment       = new Comment();
            $uniquePageId  = 'CommentInlineEditForModelView';
            $redirectUrl   = Yii::app()->createUrl('/missions/default/inlineCreateCommentFromAjax',
                                                    array('id' => $this->model->id,
                                                          'uniquePageId' => $uniquePageId));
            $urlParameters = array('relatedModelId'           => $this->model->id,
                                   'relatedModelClassName'    => 'Mission',
                                   'relatedModelRelationName' => 'comments',
                                   'redirectUrl'              => $redirectUrl); //After save, the url to go to.

            $inlineView    = new CommentInlineEditView($comment, 'default', 'comments', 'inlineCreateSave',
                                                      $urlParameters, $uniquePageId);
            $content      .= $inlineView->render();
            return ZurmoHtml::tag('div', array('id' => 'CommentInlineEditForModelView'), $content);
        }

        protected function getPortletDetailsUrl()
        {
            return Yii::app()->createUrl('/missions/default/inlineCreateComment');
        }

        /**
         * Override to handle the edit link, since it is only editable
         */
        protected function shouldRenderToolBarElement($element, $elementInformation)
        {
            assert('$element instanceof ActionElement');
            assert('is_array($elementInformation)');
            if (!parent::shouldRenderToolBarElement($element, $elementInformation))
            {
                return false;
            }
            if ($elementInformation['type'] == 'EditLink' && $this->model->owner != Yii::app()->user->userModel)
            {
                return false;
            }
            return true;
        }
    }
?>
