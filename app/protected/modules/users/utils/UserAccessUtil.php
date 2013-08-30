<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class UserAccessUtil
    {
        /**
         * @param int $userId
         */
        public static function resolveCanCurrentUserAccessAction($userId)
        {
            if (Yii::app()->user->userModel->id == $userId ||
                RightsUtil::canUserAccessModule('UsersModule', Yii::app()->user->userModel))
            {
                return;
            }
            $messageView = new AccessFailureView();
            $view = new AccessFailurePageView($messageView);
            echo $view->render();
            Yii::app()->end(0, false);
        }

        public static function resolveCanCurrentUserAccessRootUser(User $user, $renderAccessViewOnFailure = true)
        {
            if (!$user->isRootUser)
            {
                return true;
            }
            if ($user->id != Yii::app()->user->userModel->id)
            {
                if (!$renderAccessViewOnFailure)
                {
                    return false;
                }
                else
                {
                    $messageView = new AccessFailureView();
                    $view = new AccessFailurePageView($messageView);
                    echo $view->render();
                    Yii::app()->end(0, false);
                }
            }
            else
            {
                return true;
            }
        }

        public static function resolveAccessingASystemUser($user, $renderAccessViewOnFailure = true)
        {
            if (!$user->isSystemUser)
            {
                return true;
            }
            elseif (!$renderAccessViewOnFailure)
            {
                return false;
            }
            else
            {
                $messageView = new AccessFailureView();
                $view = new AccessFailurePageView($messageView);
                echo $view->render();
                Yii::app()->end(0, false);
            }
        }

        /**
         * @see ActionBarForUserEditAndDetailsView, most pill box links are only available to a user viewing the profile
         * under certain conditions.
         * @param User $user
         * @return boolean if the current user can view the edit type links or not
         */
        public static function canCurrentUserViewALinkRequiringElevatedAccess(User $user)
        {
            if (Yii::app()->user->userModel->id == $user->id ||
                RightsUtil::canUserAccessModule('UsersModule', Yii::app()->user->userModel))
            {
                if (!$user->isRootUser)
                {
                    return true;
                }
                elseif ($user->id != Yii::app()->user->userModel->id)
                {
                    return false;
                }
                else
                {
                    return true;
                }
            }
            else
            {
                return false;
            }
        }
    }
?>