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
     * Collection of helper methods for working with models, posts, and gets in conjunction with controller actions.
     */
    class ZurmoControllerUtil
    {
        public function saveModelFromPost($postData, $model, & $savedSuccessfully, & $modelToStringValue)
        {
            $sanitizedPostData                 = PostUtil::sanitizePostByDesignerTypeForSavingModel(
                                                 $model, $postData);
            return $this->saveModelFromSanitizedData($sanitizedPostData, $model, $savedSuccessfully, $modelToStringValue);
        }

        public function saveModelFromSanitizedData($sanitizedData, $model, & $savedSuccessfully, & $modelToStringValue)
        {
            //note: the logic for ExplicitReadWriteModelPermission might still need to be moved up into the
            //post method above, not sure how this is coming in from API.
            $explicitReadWriteModelPermissions = static::resolveAndMakeExplicitReadWriteModelPermissions($sanitizedData,
                                                                                                         $model);
            $readyToUseData                    = ExplicitReadWriteModelPermissionsUtil::
                                                 removeIfExistsFromPostData($sanitizedData);

            $sanitizedOwnerData            = PostUtil::sanitizePostDataToJustHavingElementForSavingModel(
                                                 $readyToUseData, 'owner');
            $sanitizedDataWithoutOwner     = PostUtil::
                                                 removeElementFromPostDataForSavingModel($readyToUseData, 'owner');
            $model->setAttributes($sanitizedDataWithoutOwner);
            $this->afterSetAttributesDuringSave($model, $explicitReadWriteModelPermissions);
            if ($model->validate())
            {
                $modelToStringValue = strval($model);
                if ($sanitizedOwnerData != null)
                {
                    $model->setAttributes($sanitizedOwnerData);
                }
                if ($model instanceof OwnedSecurableItem)
                {
                    $passedOwnerValidation = $model->validate(array('owner'));
                }
                else
                {
                    $passedOwnerValidation = true;
                }
                if ($passedOwnerValidation && $model->save(false))
                {
                    if ($explicitReadWriteModelPermissions != null)
                    {
                        $success = ExplicitReadWriteModelPermissionsUtil::
                        resolveExplicitReadWriteModelPermissions($model, $explicitReadWriteModelPermissions);
                        //todo: handle if success is false, means adding/removing permissions save failed.
                    }
                    $savedSuccessfully = true;
                    $this->afterSuccessfulSave($model);
                }
            }
            else
            {
            }
            return $model;
        }

        protected static function resolveAndMakeExplicitReadWriteModelPermissions($sanitizedData, $model)
        {
            if ($model instanceof SecurableItem)
            {
                return ExplicitReadWriteModelPermissionsUtil::resolveByPostDataAndModelThenMake($sanitizedData, $model);
            }
            else
            {
                return null;
            }
        }

        protected function afterSetAttributesDuringSave($model, $explicitReadWriteModelPermissions)
        {
        }

        protected function afterSuccessfulSave($model)
        {
        }
    }
?>