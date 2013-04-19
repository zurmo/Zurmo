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
     * Extended class to support saving comments against a related model
     */
    class CommentZurmoControllerUtil extends ZurmoControllerUtil
    {
        protected $relatedModel;

        protected $relationName;

        public function __construct($relatedModel, $relationName)
        {
            assert('is_string($relationName)');
            $this->relatedModel = $relatedModel;
            $this->relationName = $relationName;
        }

       /**
         * Override to handle saving the comment against the conversation/mission/social item
         * if it is not already connected.
         * (non-PHPdoc)
         * @see ModelHasRelatedItemsZurmoControllerUtil::afterSetAttributesDuringSave()
         */
        protected function afterSetAttributesDuringSave($model, $explicitReadWriteModelPermissions)
        {
            assert('$model instanceof Item');
            parent::afterSetAttributesDuringSave($model, $explicitReadWriteModelPermissions);
            FileModelUtil::resolveModelsHasManyFilesFromPost($model, 'files', 'filesIds');
            if ($this->relatedModel->getRelationType($this->relationName) == RedBeanModel::HAS_MANY)
            {
                if (!$this->relatedModel->{$this->relationName}->contains($model))
                {
                    $this->relatedModel->{$this->relationName}->add($model);
                    $saved = $this->relatedModel->save();
                    if (!$saved)
                    {
                        throw new FailedToSaveModelException();
                    }
                }
            }
            else
            {
                //If a comment is connected only HAS_ONE from a related model, then add support for that here.
                throw new NotImplementedException();
            }
        }

        /**
         * Override to handle sending email messages on new comment
         */
        protected function afterSuccessfulSave($model)
        {
            assert('$model instanceof Item');
            parent::afterSuccessfulSave($model);
            $user = Yii::app()->user->userModel;
            if ($this->relatedModel instanceof Conversation)
            {
                $participants = ConversationsUtil::resolvePeopleToSendNotificationToOnNewComment($this->relatedModel, $user);
                CommentsUtil::sendNotificationOnNewComment($this->relatedModel, $model, $user, $participants);
            }
            elseif ($this->relatedModel instanceof Mission)
            {
                $participants = MissionsUtil::resolvePeopleToSendNotificationToOnNewComment($this->relatedModel, $user);
                CommentsUtil::sendNotificationOnNewComment($this->relatedModel, $model, $user, $participants);
            }
        }
    }
?>