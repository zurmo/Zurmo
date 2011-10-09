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
     * Helper class to assist with security checks in controllers.
     */
    class ControllerSecurityUtil
    {
        /**
         * @return boolean - true if the current user has permission on model.
         */
        public static function doesCurrentUserHavePermissionOnSecurableItem($securableItem, $permissionToCheck)
        {
            assert('$permissionToCheck == Permission::READ || $permissionToCheck == Permission::WRITE ||
                    $permissionToCheck == Permission::DELETE');
            if (!$securableItem instanceof SecurableItem)
            {
                return true;
            }
            $permission        = $securableItem->getEffectivePermissions(Yii::app()->user->userModel);
            if ($permissionToCheck == ($permission & $permissionToCheck))
            {
                return true;
            }
            return false;
        }

        /**
         * If a current user cannot read the model, then render a AccessFailurePageView
         * and end the application.
         * @param $model - RedBeanModel
         * @return null;
         */
        public static function resolveAccessCanCurrentUserReadModel(RedBeanModel $model, $fromAjax = false)
        {
            if (ControllerSecurityUtil::doesCurrentUserHavePermissionOnSecurableItem($model, Permission::READ))
            {
                return;
            }
            ControllerSecurityUtil::renderAccessFailureView($fromAjax);
            Yii::app()->end(0, false);
        }

        /**
         * If a current user cannot read the model, then render a AccessFailurePageView
         * and end the application.
         * @param $model - RedBeanModel
         * @return null;
         */
        public static function resolveAccessCanCurrentUserWriteModel(RedBeanModel $model, $fromAjax = false)
        {
            if (ControllerSecurityUtil::doesCurrentUserHavePermissionOnSecurableItem($model, Permission::WRITE))
            {
                return;
            }
            ControllerSecurityUtil::renderAccessFailureView($fromAjax);
            Yii::app()->end(0, false);
        }

        /**
         * If a current user cannot read the model, then render a AccessFailurePageView
         * and end the application.
         * @param $model - RedBeanModel
         * @return null;
         */
        public static function resolveAccessCanCurrentUserDeleteModel(RedBeanModel $model, $fromAjax = false)
        {
            if (ControllerSecurityUtil::doesCurrentUserHavePermissionOnSecurableItem($model, Permission::DELETE))
            {
                return;
            }
            ControllerSecurityUtil::renderAccessFailureView($fromAjax);
            Yii::app()->end(0, false);
        }

        /**
         * If a current user cannot read the module, then render a AccessFailurePageView
         * and end the application.
         * @param $model - RedBeanModel
         * @return null;
         */
        public static function resolveAccessCanCurrentUserWriteModule($moduleClassName, $fromAjax = false)
        {
            assert('is_string($moduleClassName)');
            $item       = NamedSecurableItem::getByName($moduleClassName);
            if (ControllerSecurityUtil::doesCurrentUserHavePermissionOnSecurableItem($item, Permission::WRITE))
            {
                return;
            }
            ControllerSecurityUtil::renderAccessFailureView($fromAjax);
            Yii::app()->end(0, false);
        }

        public static function resolveCanCurrentUserAccessModule($moduleClassName, $fromAjax = false)
        {
            assert('is_string($moduleClassName)');
            if (RightsUtil::canUserAccessModule($moduleClassName, Yii::app()->user->userModel))
            {
                return;
            }
            ControllerSecurityUtil::renderAccessFailureView($fromAjax);
            Yii::app()->end(0, false);
        }

        protected static function renderAccessFailureView($fromAjax = false, $nonAjaxFailureMessageContent = null)
        {
            if ($fromAjax)
            {
                $messageView = new AccessFailureAjaxView();
                $view        = new AjaxPageView($messageView);
            }
            else
            {
                $messageView = new AccessFailureView($nonAjaxFailureMessageContent);
                $view        = new AccessFailurePageView($messageView);
            }
            echo $view->render();
        }
    }
?>