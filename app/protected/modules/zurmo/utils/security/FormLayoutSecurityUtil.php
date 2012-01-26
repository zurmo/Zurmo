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
     * Helper class to handle form layout security
     */
    class FormLayoutSecurityUtil
    {
        //TODO: change this functions once field level security is available.
        /**
         * For now, this determines if there is a subclass of
         * ModelElement and makes the appropriate adjustments
         * based on the user's access to this element and its
         * related attributes.  This is for the Editable render.
         * @return null. Modifies $elementInformation by reference.
         */
        public static function resolveElementForEditableRender($model, & $elementInformation, $user)
        {
            assert('$model instanceof RedBeanModel || $model instanceof CModel');
            assert('is_array($elementInformation)');
            assert('$user instanceof User && $user->id > 0');
            $elementclassname = $elementInformation['type'] . 'Element';
            $attributeName    = $elementInformation['attributeName'];
            if (is_subclass_of($elementclassname, 'ModelElement'))
            {
                if (!ActionSecurityUtil::canUserPerformAction(
                    $elementclassname::getEditableActionType(), $model->$attributeName, $user))
                {
                    $elementInformation['attributeName'] = null;
                    $elementInformation['type']          = 'Null'; // Not Coding Standard
                    //TODO: potentially throw misconfiguration exception if field is required
                    //instead of just setting a null element.
                }
            }
            if (is_subclass_of($elementclassname, 'ModelsElement'))
            {
                $actionType = $elementclassname::getEditableActionType();
                if ($actionType != null)
                {
                    $actionSecurity = ActionSecurityFactory::createRightsOnlyActionSecurityFromActionType($actionType, $user);
                    if (!$actionSecurity->canUserPerformAction())
                    {
                        $elementInformation['attributeName'] = null;
                        $elementInformation['type']          = 'Null'; // Not Coding Standard
                        //TODO: potentially throw misconfiguration exception if field is required
                        //instead of just setting a null element.
                    }
                }
            }
        }

        //TODO: change this functions once field level security is available.
        /**
         * For now, this determines if there is a subclass of
         * ModelElement and makes the appropriate adjustments
         * based on the user's access to this element and its
         * related attributes.  This is for the NonEditable render.
         * @return null. Modifies $elementInformation by reference.
         */
        public static function resolveElementForNonEditableRender($model, & $elementInformation, $user)
        {
            assert('$model instanceof RedBeanModel || $model instanceof CModel');
            assert('is_array($elementInformation)');
            assert('$user instanceof User && $user->id > 0');
            $elementclassname = $elementInformation['type'] . 'Element';
            $attributeName    = $elementInformation['attributeName'];
            if (is_subclass_of($elementclassname, 'ModelElement'))
            {
                $moduleId = $elementclassname::getModuleId();
                $moduleClassName = get_class(Yii::app()->getModule($moduleId));
                assert('is_string($moduleClassName)');
                $userCanAccess   = RightsUtil::canUserAccessModule($moduleClassName, $user);
                $userCanReadItem = ActionSecurityUtil::canUserPerformAction(
                    $elementclassname::getNonEditableActionType(), $model->$attributeName, $user);
                if ($userCanAccess && $userCanReadItem)
                {
                    return;
                }
                elseif (!$userCanAccess && $userCanReadItem)
                {
                    if ($model->$attributeName->id < 0)
                    {
                        $elementInformation['attributeName'] = null;
                        $elementInformation['type']          = 'Null'; // Not Coding Standard
                    }
                    else
                    {
                        $elementInformation['noLink'] = true;
                    }
                }
                else
                {
                    $elementInformation['attributeName'] = null;
                    $elementInformation['type']          = 'Null'; // Not Coding Standard
                }
            }
            elseif (is_subclass_of($elementclassname, 'ExplicitReadWriteModelPermissionsElement'))
            {
                if (ActionSecurityUtil::canUserPerformAction('Edit', $model, $user))
                {
                    return;
                }
                else
                {
                    $elementInformation['type'] = 'Null'; // Not Coding Standard
                }
            }
        }
    }
?>