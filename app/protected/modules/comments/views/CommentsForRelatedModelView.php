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
     * A view that displays comments for a related model
     *
     */
    class CommentsForRelatedModelView extends View
    {
        protected $controllerId;

        protected $moduleId;

        protected $commentsData;

        protected $relatedModel;

        protected $pageSize;

        protected $getParams;

        protected $uniquePageId;

        public function __construct($controllerId, $moduleId, $commentsData, Item $relatedModel, $pageSize, $getParams, $uniquePageId = null)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_array($commentsData)');
            assert('$relatedModel->id > 0');
            assert('is_int($pageSize) || $pageSize == null');
            assert('is_array($getParams)');
            assert('is_string($uniquePageId) || $uniquePageId == null');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->commentsData           = $commentsData;
            $this->relatedModel           = $relatedModel;
            $this->pageSize               = $pageSize;
            $this->getParams              = $getParams;
            $this->uniquePageId           = $uniquePageId;
        }

        protected function getId()
        {
            return 'CommentsForRelatedModelView' . $this->uniquePageId;
        }

        protected function renderContent()
        {
            $content = '<div>' . $this->renderHiddenRefreshLinkContent() . '</div>';
            if (count($this->commentsData) > 0)
            {
                if (count($this->commentsData) > $this->pageSize && $this->pageSize != null)
                {
                    $content .= '<div>' . $this->renderShowAllLinkContent() . '</div>';
                }
                $content .= '<div id="CommentList' . $this->uniquePageId . '" class="CommentList">' . $this->renderCommentsContent() . '</div>';
            }
            return $content;
        }

        protected function renderHiddenRefreshLinkContent()
        {
            $url     =   Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/ajaxListForRelatedModel',
                            $this->getParams);
            return       ZurmoHtml::ajaxLink('Refresh', $url,
                         array('type' => 'GET',
                               'success' => 'function(data){$("#CommentsForRelatedModelView' . $this->uniquePageId . '").replaceWith(data)}'),
                         array('id'         => 'hiddenCommentRefresh'. $this->uniquePageId,
                                'class'     => 'hiddenCommentRefresh',
                                'namespace' => 'refresh',
                                'style'     => 'display:none;'));
        }

        protected function renderShowAllLinkContent()
        {
            $url     =   Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/ajaxListForRelatedModel',
                            array_merge($this->getParams, array('noPaging' => true)));
            return       ZurmoHtml::ajaxLink(Yii::t('Default', 'Show older comments'), $url,
                         array('type' => 'GET',
                               'success' => 'function(data){$("#CommentsForRelatedModelView' . $this->uniquePageId . '").replaceWith(data)}'),
                         array('id'         => 'showAllCommentsLink' . $this->uniquePageId,
                                'class'     => 'showAllCommentsLink',
                                'namespace' => 'refresh'));
        }

        protected function renderCommentsContent()
        {
            $content  = null;
            $rows = 0;
            foreach (array_reverse($this->commentsData) as $comment)
            {
                //Skip the first if the page size is smaller than what is returned.
                if (count(($this->commentsData)) > $this->pageSize && $this->pageSize != null && $rows == 0)
                {
                    $rows++;
                    continue;
                }
                $userUrl        = Yii::app()->createUrl('/users/default/details', array('id' => $comment->createdByUser->id));
                $stringContent  = ZurmoHtml::link($comment->createdByUser->getAvatarImage(36), $userUrl);
                $userName       = ZurmoHtml::link(strval($comment->createdByUser), $userUrl, array('class' => 'user-link'));
                $stringContent .= '<div class="comment-content"><p>' . $userName . ': ' . $comment->description . '</p>';

                //attachments
                if ($comment->files->count() > 0)
                {
                    $stringContent .= FileModelDisplayUtil::renderFileDataDetailsWithDownloadLinksContent($comment, 'files', true);
                }
                if ($comment->createdByUser == Yii::app()->user->userModel ||
                   $this->relatedModel->createdByUser == Yii::app()->user->userModel ||
                   ($this->relatedModel instanceof OwnedSecurableItem && $this->relatedModel->owner == Yii::app()->user->userModel))
                {
                    $deleteCommentLink   = ' · <span class="delete-comment">' . $this->renderDeleteLinkContent($comment) . '</span>';
                    $editCommentLink     = ' · <span class="edit-comment">' . $this->renderEditLinkContent($comment) . '</span>';
                }
                else
                {
                    $deleteCommentLink = null;
                    $editCommentLink   = null;
                }
                $editCommentLink   = null; //temporary until edit link is added
                $stringContent .= '<span class="comment-details"><strong>'. DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                                              $comment->createdDateTime, 'long', null) . '</strong></span>' . $editCommentLink . $deleteCommentLink;

                $stringContent .= '</div>';

                $content .= '<div class="comment">' . $stringContent . '</div>';
                $rows++;
            }
            return $content;
        }

        protected function renderDeleteLinkContent(Comment $comment)
        {
            $url     =   Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/deleteViaAjax',
                            array_merge($this->getParams, array('id' => $comment->id)));
            // Begin Not Coding Standard
            return       ZurmoHtml::ajaxLink(Yii::t('Default', 'Delete'), $url,
                         array('type'     => 'GET',
                               'complete' => "function(XMLHttpRequest, textStatus){
                                              $('#deleteCommentLink" . $comment->id . "').parent().parent().parent().remove();}"),
                         array('id'         => 'deleteCommentLink' . $comment->id,
                                'class'     => 'deleteCommentLink' . $comment->id,
                                'namespace' => 'delete'));
            // End Not Coding Standard
        }

        /*TODO*/
        protected function renderEditLinkContent(Comment $comment)
        {
            $url     =   '';
            // Begin Not Coding Standard
            return       ZurmoHtml::ajaxLink(Yii::t('Default', 'Edit'), $url);
            // End Not Coding Standard
        }

        public function isUniqueToAPage()
        {
            return false;
        }
    }
?>