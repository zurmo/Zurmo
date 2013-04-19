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

    class SocialItemsDefaultController extends ZurmoBaseController
    {
        /**
         * Action for saving a new social item inline edit form.
         * @param string or array $redirectUrl
         */
        public function actionInlineCreateSave($redirectUrl = null)
        {
            $socialItem = new SocialItem();
            $socialItem->setScenario('createPost');
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'social-item-inline-edit-form')
            {
                $this->actionInlineEditValidate($socialItem, 'SocialItem');
            }
            $_POST['SocialItem']['explicitReadWriteModelPermissions']['type'] = ExplicitReadWriteModelPermissionsUtil::
                                                                                MIXED_TYPE_EVERYONE_GROUP;
            $this->attemptToSaveModelFromPost($socialItem, $redirectUrl);
        }

        public function actionPostGameNotificationToProfile($content)
        {
            $socialItem                        = new SocialItem();
            $socialItem->description           = $content;
            $socialItem->addPermissions(Group::getByName(Group::EVERYONE_GROUP_NAME),
                                        Permission::READ_WRITE_CHANGE_PERMISSIONS_CHANGE_OWNER);
            $saved                             = $socialItem->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
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
            $relatedUserId           = ArrayUtil::getArrayValue($getData, 'relatedUserId');
            if ($relatedUserId == null)
            {
                $relatedUser = null;
            }
            else
            {
                $relatedUser = User::getById((int)$relatedUserId);
            }
            return new SocialItemZurmoControllerUtil($relatedUser);
        }

        /**
         * @see SocialItemsUtil::renderCreateCommentContent for a similar render that occurs on initial page load
         */
        public function actionInlineCreateCommentFromAjax($id, $uniquePageId)
        {
            $comment       = new Comment();
            $redirectUrl   = Yii::app()->createUrl('/socialItems/default/inlineCreateCommentFromAjax',
                                                    array('id'           => $id,
                                                          'uniquePageId' => $uniquePageId));
            $urlParameters = array('uniquePageId'             => $uniquePageId,
                                   'relatedModelId'           => (int)$id,
                                   'relatedModelClassName'    => 'SocialItem',
                                   'relatedModelRelationName' => 'comments',
                                   'redirectUrl'              => $redirectUrl); //After save, the url to go to.
            $socialItem    = SocialItem::getById((int)$id);
            $uniquePageId  = SocialItemsUtil::makeUniquePageIdByModel($socialItem);
            $content       = ZurmoHtml::tag('span', array(),
                                            ZurmoHtml::link(Zurmo::t('SocialItemsModule', 'Comment'), '#',
                                                            array('class' => 'show-create-comment')));
            $inlineView    = new CommentForSocialItemInlineEditView($comment, 'default', 'comments', 'inlineCreateSave',
                                                                    $urlParameters, $uniquePageId, $socialItem->id);
            $view          = new AjaxPageView($inlineView);
            echo $content . ZurmoHtml::tag('div', array('style' => 'display:none;'), $view->render());
        }

        public function actionDeleteViaAjax($id)
        {
            $socialItem = SocialItem::getById(intval($id));
            if (!$socialItem->canUserDelete(Yii::app()->user->userModel) &&
                $socialItem->owner->id  != Yii::app()->user->userModel->id &&
                $socialItem->toUser->id != Yii::app()->user->userModel->id)
            {
                $messageView = new AccessFailureAjaxView();
                $view        = new AjaxPageView($messageView);
                echo $view->render();
                Yii::app()->end(0, false);
            }
            $deleted = $socialItem->delete();
            if (!$deleted)
            {
                throw new FailedToDeleteModelException();
            }
        }
    }
?>
