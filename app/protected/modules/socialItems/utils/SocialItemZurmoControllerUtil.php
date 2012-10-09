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
     * Extended class to support saving social items
     */
    class SocialItemZurmoControllerUtil extends ZurmoControllerUtil
    {
        protected $relatedUser;

        public function __construct($relatedUser)
        {
            assert('$relatedUser == null || ($relatedUser instanceof User && $relatedUser->id > 0)');
            $this->relatedUser = $relatedUser;
        }

        /**
         * Handles when a user posts to another user's profile social feed.  Sets the toUser in that case.
         * (non-PHPdoc)
         * @see ZurmoControllerUtil::saveModelFromPost()
         */
        public function saveModelFromPost($postData, $model, & $savedSucessfully, & $modelToStringValue)
        {
            $sanitizedPostData                 = PostUtil::sanitizePostByDesignerTypeForSavingModel(
                                                 $model, $postData);
            if ($this->relatedUser != null && !Yii::app()->user->userModel->isSame($this->relatedUser))
            {
                $model->toUser = $this->relatedUser;
            }
            return $this->saveModelFromSanitizedData($sanitizedPostData, $model, $savedSucessfully, $modelToStringValue);
        }

       /**
         * Override to handle saving file attachments
         * (non-PHPdoc)
         * @see ModelHasRelatedItemsZurmoControllerUtil::afterSetAttributesDuringSave()
         */
        protected function afterSetAttributesDuringSave($model, $explicitReadWriteModelPermissions)
        {
            assert('$model instanceof Item');
            parent::afterSetAttributesDuringSave($model, $explicitReadWriteModelPermissions);
            FileModelUtil::resolveModelsHasManyFilesFromPost($model, 'files', 'filesIds');
        }
    }
?>