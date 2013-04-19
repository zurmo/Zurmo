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

    /**
     * Helper class for social module processes
     */
    class SocialItemsUtil
    {
        /**
         * Renders and returns string content of summary content for the given model.
         * @param RedBeanModel $model
         * @param mixed $redirectUrl
         * @param string $ownedByFilter
         * @param string $viewModuleClassName
         * @return string content
         */
        public static function renderItemAndCommentsContent(SocialItem $model, $redirectUrl, $renderToUserString)
        {
            assert('is_string($redirectUrl) || $redirectUrl == null');
            $userUrl  = Yii::app()->createUrl('/users/default/details', array('id' => $model->owner->id));
            $content  = '<div class="social-item">';
            $avatarImage = $model->owner->getAvatarImage(50);
            $content .= '<div class="comment model-details-summary clearfix">';
            $content .= '<span class="user-details">' . ZurmoHtml::link($avatarImage, $userUrl);
            $content .= '</span>';
            $userLink = ZurmoHtml::link(strval($model->owner), $userUrl, array('class' => 'user-link'));
            $content .= '<div class="comment-content"><p>';

            if ($model->toUser->id > 0 && $renderToUserString)
            {
                $toUserUrl  = Yii::app()->createUrl('/users/default/details', array('id' => $model->toUser->id));
                $toUserLink = ZurmoHtml::link(strval($model->toUser), $toUserUrl, array('class' => 'user-link'));
                $content   .= Zurmo::t('SocialItemsModule', '{postedFromUser} to {postedToUser}',
                                                array('{postedFromUser}' => $userLink,
                                                      '{postedToUser}'   => $toUserLink));
            }
            else
            {
                $content   .= $userLink;
            }
            $content .= '</p>';
            $content .= self::renderModelDescription($model) . '</div>';
            $content .= self::renderAfterDescriptionContent($model);
            $content .= self::renderItemFileContent($model);

            $content .= '<span class="comment-details"><strong>'. DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                                    $model->createdDateTime, 'long', null) . '</strong>';
            if ($model->canUserDelete(Yii::app()->user->userModel))
            {
                $content .= ' · <span class="delete-comment">' . self::renderDeleteLinkContent($model) . '</span></span>';
            }
            $content .= '</div>';

            $content .= self::renderCommentsContent($model);
            $content .= self::renderCreateCommentContent($model);

            $content .= '</div>';
            self::registerListColumnScripts();
            return $content;
        }

        private static function renderModelDescription(SocialItem $model)
        {
            if ($model->note->id > 0)
            {
                return Yii::app()->format->html($model->note->description);
            }
            else
            {
                return Yii::app()->format->html($model->description);
            }
        }

        private static function renderAfterDescriptionContent(SocialItem $model)
        {
            if ($model->note->id > 0)
            {
                $content = null;
                if ($model->note->activityItems->count() > 0)
                {
                    $element                      = new NoteActivityItemsForSocialItemsListElement($model->note, null);
                    $element->nonEditableTemplate = '{content}';
                    $content                     .= $element->render();
                    $content                     .= '<br/>';
                }
                return $content;
            }
        }

        private static function renderItemFileContent(SocialItem $model)
        {
            return ZurmoHtml::tag('span', array(), FileModelDisplayUtil::
                                                   renderFileDataDetailsWithDownloadLinksContent($model, 'files'));
        }

        public static function makeUniquePageIdByModel(SocialItem $model)
        {
            return 'CreateCommentForSocialItem-' . $model->id;
        }

        private static function renderDeleteLinkContent(SocialItem $model)
        {
            $url     =   Yii::app()->createUrl('socialItems/default/deleteViaAjax',
                                               array('id' => $model->id));
            // Begin Not Coding Standard
            return       ZurmoHtml::ajaxLink(Zurmo::t('SocialItemsModule', 'Delete'), $url,
                         array('type'     => 'GET',
                               'complete' => "function(XMLHttpRequest, textStatus){
                                              $('#deleteSocialItemLink" . $model->id . "').parent().parent().parent().parent().parent().parent().remove();}"),
                         array('id'         => 'deleteSocialItemLink'   . $model->id,
                                'class'     => 'deleteSocialItemLink'   . $model->id,
                                'namespace' => 'delete'));
            // End Not Coding Standard
        }

        private static function renderCommentsContent(SocialItem $model)
        {
            $getParams    = array('uniquePageId'             => self::makeUniquePageIdByModel($model),
                                  'relatedModelId'           => $model->id,
                                  'relatedModelClassName'    => 'SocialItem',
                                  'relatedModelRelationName' => 'comments');
            $pageSize     = 5;
            $commentsData = Comment::getCommentsByRelatedModelTypeIdAndPageSize('SocialItem',
                                                                                $model->id, ($pageSize + 1));
            $view         = new CommentsForRelatedModelView('default',
                                                            'comments',
                                                            $commentsData,
                                                            $model,
                                                            $pageSize,
                                                            $getParams,
                                                            self::makeUniquePageIdByModel($model));
            $content      = $view->render();
            return $content;
        }

        /**
         * @see SocialItemsDefaultController::actionInlineCreateCommentFromAjax for a similar render that occurs
         * on ajax load instead of this method which renders on the initial page load
         */
        private static function renderCreateCommentContent(SocialItem $model)
        {
            $content       = ZurmoHtml::tag('span', array(),
                                            ZurmoHtml::link(Zurmo::t('SocialItemsModule', 'Comment'), '#',
                                                            array('class' => 'show-create-comment')));
            $comment       = new Comment();
            $uniquePageId  = self::makeUniquePageIdByModel($model);
            $redirectUrl   = Yii::app()->createUrl('/socialItems/default/inlineCreateCommentFromAjax',
                                                    array('id' => $model->id,
                                                          'uniquePageId' => $uniquePageId));
            $urlParameters = array('uniquePageId'             => $uniquePageId,
                                   'relatedModelId'           => $model->id,
                                   'relatedModelClassName'    => 'SocialItem',
                                   'relatedModelRelationName' => 'comments',
                                   'redirectUrl'              => $redirectUrl); //After save, the url to go to.

            $inlineView    = new CommentForSocialItemInlineEditView($comment, 'default', 'comments', 'inlineCreateSave',
                                                      $urlParameters, $uniquePageId, $model->id);
            $content      .= ZurmoHtml::tag('div', array('style' => 'display:none;'), $inlineView->render());
            return ZurmoHtml::tag('div', array('id' => $uniquePageId), $content);
        }

        private static function registerListColumnScripts()
        {
            // Begin Not Coding Standard
            Yii::app()->clientScript->registerScript('socialItemComments', "
                $('.show-create-comment').live('click', function()
                    {
                        $(this).parent().parent().find('div:first').show();
                        $(this).parent().hide();
                        return false;
                    }
                );
            ");
            // End Not Coding Standard
        }
    }
?>