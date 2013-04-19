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

    class CommentsDefaultController extends ZurmoBaseController
    {
        /**
         * Action for saving a new comment inline edit form.
         * @param string or array $redirectUrl
         */
        public function actionInlineCreateSave($redirectUrl = null, $uniquePageId = null)
        {
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'comment-inline-edit-form' . $uniquePageId)
            {
                $this->actionInlineEditValidate(new Comment());
            }
            $this->attemptToSaveModelFromPost(new Comment(), $redirectUrl);
        }

        public function actionAjaxListForRelatedModel($uniquePageId = null)
        {
            $getData                  = GetUtil::getData();
            $relatedModelId           = ArrayUtil::getArrayValue($getData, 'relatedModelId');
            $relatedModelClassName    = ArrayUtil::getArrayValue($getData, 'relatedModelClassName');
            $relatedModelRelationName = ArrayUtil::getArrayValue($getData, 'relatedModelRelationName');
            if (ArrayUtil::getArrayValue($getData, 'noPaging'))
            {
                $pageSize                 = null;
                $retrievalPageSize        = null;
            }
            else
            {
                $pageSize                 = 5;
                $retrievalPageSize        = ($pageSize + 1);
            }
            $commentsData             = Comment::getCommentsByRelatedModelTypeIdAndPageSize($relatedModelClassName,
                                                                                            (int)$relatedModelId,
                                                                                            $retrievalPageSize);
            $getParams                = array('uniquePageId'             => $uniquePageId,
                                              'relatedModelId'           => $relatedModelId,
                                              'relatedModelClassName'    => $relatedModelClassName,
                                              'relatedModelRelationName' => $relatedModelRelationName);
            $relatedModel             = $relatedModelClassName::getById((int)$relatedModelId);
            $view                     = new CommentsForRelatedModelView('default', 'comments', $commentsData, $relatedModel,
                                                                        $pageSize, $getParams, $uniquePageId);
            $content                  = $view->render();
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        public function actionDeleteViaAjax($id)
        {
            $getData                  = GetUtil::getData();
            $relatedModelId           = ArrayUtil::getArrayValue($getData, 'relatedModelId');
            $relatedModelClassName    = ArrayUtil::getArrayValue($getData, 'relatedModelClassName');
            $comment                  = Comment::getById(intval($id));
            $relatedModel             = $relatedModelClassName::getById(intval($relatedModelId));
            if ($comment->createdByUser->id      != Yii::app()->user->userModel->id &&
               $relatedModel->owner->id         != Yii::app()->user->userModel->id &&
               $relatedModel->createdByUser->id != Yii::app()->user->userModel->id)
            {
                $messageView = new AccessFailureAjaxView();
                $view        = new AjaxPageView($messageView);
                echo $view->render();
                Yii::app()->end(0, false);
            }
            $deleted = $comment->delete();
            if (!$deleted)
            {
                throw new FailedToDeleteModelException();
            }
        }

        protected function actionInlineEditValidate($model)
        {
            $postData                      = PostUtil::getData();
            $postFormData                  = ArrayUtil::getArrayValue($postData, get_class($model));
            $sanitizedPostData             = PostUtil::
                                             sanitizePostByDesignerTypeForSavingModel($model, $postFormData);
            $model->setAttributes($sanitizedPostData);
            $model->validate();
            $errorData = ZurmoActiveForm::makeErrorsDataAndResolveForOwnedModelAttributes($model);
            echo CJSON::encode($errorData);
            Yii::app()->end(0, false);
        }

        protected static function getZurmoControllerUtil()
        {
            $getData                  = GetUtil::getData();
            $relatedModelId           = ArrayUtil::getArrayValue($getData, 'relatedModelId');
            $relatedModelClassName    = ArrayUtil::getArrayValue($getData, 'relatedModelClassName');
            $relatedModelRelationName = ArrayUtil::getArrayValue($getData, 'relatedModelRelationName');
            if ($relatedModelId == null || $relatedModelClassName == null || $relatedModelRelationName == null)
            {
                throw new NotSupportedException();
            }
            $relatedModel = $relatedModelClassName::getById((int)$relatedModelId);
            return new CommentZurmoControllerUtil($relatedModel, $relatedModelRelationName);
        }
    }
?>
