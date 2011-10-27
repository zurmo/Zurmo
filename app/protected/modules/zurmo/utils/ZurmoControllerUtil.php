<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * Collection of helper methods for working with models, posts, and gets in conjunction with controller actions.
     */
    class ZurmoControllerUtil
    {
        public static function saveModelFromPost($postData, $model, & $savedSucessfully, & $modelToStringValue)
        {
            if ($model instanceof SecurableItem)
            {
                $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                     resolveByPostDataAndModelThenMake($postData, $model);
            }
            else
            {
                $explicitReadWriteModelPermissions = null;
            }
            $readyToUsePostData                = ExplicitReadWriteModelPermissionsUtil::
                                                 removeIfExistsFromPostData($postData);
            $sanitizedPostData                 = PostUtil::sanitizePostByDesignerTypeForSavingModel(
                                                 $model, $readyToUsePostData);
            $sanitizedOwnerPostData            = PostUtil::sanitizePostDataToJustHavingElementForSavingModel(
                                                 $sanitizedPostData, 'owner');
            $sanitizedPostDataWithoutOwner     = PostUtil::
                                                 removeElementFromPostDataForSavingModel($sanitizedPostData, 'owner');
            $model->setAttributes($sanitizedPostDataWithoutOwner);
            if ($model->validate())
            {
                $modelToStringValue = strval($model);
                if ($sanitizedOwnerPostData != null)
                {
                    $model->setAttributes($sanitizedOwnerPostData);
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
                    $savedSucessfully = true;
                }
            }
            return $model;
        }
    }
?>