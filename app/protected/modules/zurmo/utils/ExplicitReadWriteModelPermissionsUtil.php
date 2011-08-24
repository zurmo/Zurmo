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
     * Helper class for managing the explicit setting of permissions against a model in the user interface.
     * @see ExplicitReadWriteModelPermissions
     * @see ExplicitReadWriteModelPermissionsElement
     */
    class ExplicitReadWriteModelPermissionsUtil
    {
        /**
         * Defines the type as being the everyone group.
         * @var intger
         */
        const MIXED_TYPE_EVERYONE_GROUP    = 1;

        /**
         * Defines the type as being a specific group, but not the everyone group.
         * @var intger
         */
        const MIXED_TYPE_NONEVERYONE_GROUP = 2;

        /**
         * Given a mixed permitables data array, make a explicitReadWriteModelPermissions object. The
         * $mixedPermitablesData is an array with 2 sub-arrays.  readOnly and readWrite. These sub-arrays each
         * contain an array of permitable objects.
         * @param array $mixedPermitablesData
         */
        public static function makeByMixedPermitablesData($mixedPermitablesData)
        {
            assert('is_array($mixedPermitablesData)');
            assert('isset($mixedPermitablesData["readOnly"])');
            assert('isset($mixedPermitablesData["readWrite"])');
            $explicitReadWriteModelPermissions = new ExplicitReadWriteModelPermissions();
            foreach($mixedPermitablesData['readOnly'] as $permitableData)
            {
                $permitableClassName = key($permitableData);
                $permitableId        = $permitableData[$permitableClassName];
                $explicitReadWriteModelPermissions->addReadOnlyPermitable($permitableClassName::getById($permitableId));
            }
            foreach($mixedPermitablesData['readWrite'] as $permitableData)
            {
                $permitableClassName = key($permitableData);
                $permitableId        = $permitableData[$permitableClassName];
                $explicitReadWriteModelPermissions->addReadWritePermitable($permitableClassName::getById($permitableId));
            }
            return $explicitReadWriteModelPermissions;
        }

        /**
         * Given a explicitReadWriteModelPermissions object, make a $mixedPermitablesData array.
         * @param object $explicitReadWriteModelPermissions
         */
        public static function makeMixedPermitablesDataByExplicitReadWriteModelPermissions(
                               $explicitReadWriteModelPermissions)
        {
            assert('$explicitReadWriteModelPermissions instanceof ExplicitReadWriteModelPermissions ||
                    $explicitReadWriteModelPermissions == null');
            if($explicitReadWriteModelPermissions == null)
            {
                return null;
            }
            if($explicitReadWriteModelPermissions->getReadOnlyPermitablesCount() == 0 &&
               $explicitReadWriteModelPermissions->getReadWritePermitablesCount() == 0)
            {
               return null;
            }
            $mixedPermitablesData = array();
            $mixedPermitablesData['readOnly'] = array();
            $mixedPermitablesData['readWrite'] = array();
            foreach($explicitReadWriteModelPermissions->getReadOnlyPermitables() as $permitable)
            {
                $mixedPermitablesData['readOnly'][] = array(get_class($permitable) => $permitable->id);
            }
            foreach($explicitReadWriteModelPermissions->getReadWritePermitables() as $permitable)
            {
                $mixedPermitablesData['readWrite'][] = array(get_class($permitable) => $permitable->id);
            }
            return $mixedPermitablesData;
        }

        /**
         * Given post data, which would be coming most likely from the ExplicitReadWriteModelPermissionsElement,
         * transform the post data into a ExplicitReadWriteModelPermissions object.  If the post data contains a 'type'
         * value that is not supported, an exception is thrown.
         * @param array $postData
         * @see ExplicitReadWriteModelPermissionsElement
         */
        public static function makeByPostData($postData)
        {
            assert('is_array($postData)');
            $explicitReadWriteModelPermissions = new ExplicitReadWriteModelPermissions();
            if($postData['type'] == null)
            {
                return $explicitReadWriteModelPermissions;
            }
            elseif($postData['type'] == ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP)
            {
                $explicitReadWriteModelPermissions->addReadWritePermitable(Group::getByName(Group::EVERYONE_GROUP_NAME));
                return $explicitReadWriteModelPermissions;
            }
            elseif($postData['type'] == ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_NONEVERYONE_GROUP)
            {
                assert('isset($postData["nonEveryoneGroup"])');
                $explicitReadWriteModelPermissions->addReadWritePermitable(
                                                    Group::getById((int)$postData["nonEveryoneGroup"]));
                return $explicitReadWriteModelPermissions;
            }
            else
            {
                throw notSupportedException();
            }
        }
    }
?>