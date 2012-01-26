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
     * Helper class for working with action security classes
     */
    class ActionSecurityUtil
    {
        /**
         * @see ActionSecurityUtil::canUserPerformAction
         */
        public static function canCurrentUserPerformAction($actionType, $model)
        {
            return self::canUserPerformAction($actionType, $model, Yii::app()->user->userModel);
        }

        /**
         * Check if user can perform an action. Action type examples:
         * Details, Edit, Delete. Action types are returned by actionElements
         * via getActionType method.  If the model is not a securable model
         * then return true.  If the model is a Permitable such as User this will
         * return true.  This does not necessarily mean the current user is allowed through
         * the user interface to edit the $model (User).  This must be controlled by
         * controller rights filters.
         * @return boolean true if user can perform action.
         */
        public static function canUserPerformAction($actionType, $model, $user)
        {
            assert('$user instanceof User && $user->id > 0');
            assert('$model instanceof Item');
            assert('$actionType == null || is_string($actionType)');
            if (!$model instanceof SecurableItem)
            {
                return true;
            }
            if ($actionType == null)
            {
                return true;
            }

            $actionSecurity = ActionSecurityFactory::createActionSecurityFromActionType(
                                $actionType,
                                $model,
                                $user);

            return $actionSecurity->canUserPerformAction();
        }

        /**
         * Resolve a link to a related model.  Used by @see ListView
         * for each row of a list for example.  If the current user can Permission::READ
         * the related model, then check if the current user has RIGHT_ACCESS_ to
         * the model's related module.  If current user has access then
         * return link, otherwise return text.  If current user cannot Permission::READ
         * then return null.
         * @param $attributeString
         * @param $model
         * @param $moduleClassName
         * @param $linkRoute
         * @return string content.
         */
        public static function resolveLinkToModelForCurrentUser(
            $attributeString,
            $model,
            $moduleClassName,
            $linkRoute)
        {
            assert('is_string($attributeString)');
            assert('$model instanceof Item');
            assert('is_string($moduleClassName)');
            assert('is_string($linkRoute)');
            if (!ActionSecurityUtil::canCurrentUserPerformAction('Details', $model))
            {
                return null;
            }
            if (RightsUtil::canUserAccessModule($moduleClassName, Yii::app()->user->userModel))
            {
                return CHtml::link($attributeString,
                    Yii::app()->createUrl($linkRoute, array("id" => $model->id)));
            }
            return $attributeString;
        }
    }
?>