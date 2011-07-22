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
     * Helper class to make a RightsForm
     * and populate the data attribute.
     */
    class RightsFormUtil
    {
        /**
         * @param $data - combined array of all module rights
         * and existing rights on a permitable.  Organized by module.
         * Example below:
         * @code
            <?php
                $moduleRightsData = array(
                    'UsersModule' => array(
                        'RIGHT_CHANGE_USER_PASSWORDS' => array(
                            'displayName' => UsersModule::RIGHT_CHANGE_USER_PASSWORDS,
                            'selected'    => null,
                            'inherited'   => null,
                        ),
                        'RIGHT_LOGIN_VIA_WEB'  => array(
                            'displayName' => UsersModule::RIGHT_LOGIN_VIA_WEB,
                            'selected'    => null,
                            'inherited'   => null,
                        ),
                        'RIGHT_LOGIN_VIA_MOBILE' => array(
                            'displayName' => UsersModule::RIGHT_LOGIN_VIA_MOBILE,
                            'selected'    => null,
                            'inherited'   => null,
                        ),
                        'RIGHT_LOGIN_VIA_WEB_API'   => array(
                            'displayName' => UsersModule::RIGHT_LOGIN_VIA_WEB_API,
                            'selected'    => null,
                            'inherited'   => null,
                        ),
                    ),
                );
            ?>
         * @endcode
         */
        public static function makeFormFromRightsData($rightsData)
        {
            assert('is_array($rightsData)');
            $form       = new RightsForm();
            $form->data = $rightsData;
            return $form;
        }

        /**
         * Set Permitable Rights from Post
         * @return boolean - true on success
         */
        public static function setRightsFromCastedPost(array $validatedAndCastedPostData, $permitable)
        {
            assert('$permitable instanceof Permitable');
            assert('$permitable->id > 0');
            foreach ($validatedAndCastedPostData as $concatenatedIndex => $value)
            {
                $moduleClassName = self::getModuleClassNameFromPostConcatenatedIndexString(
                                        $concatenatedIndex);
                $right           = self::getRightFromPostConcatenatedIndexString(
                                        $concatenatedIndex);
                $saved           = self::AddorRemoveSpecificRight(
                                        $moduleClassName,
                                        $permitable,
                                        $right,
                                        $value);
                if (!$saved)
                {
                    return false;
                }
            }
            return true;
        }

        protected static function AddorRemoveSpecificRight($moduleClassName, $permitable, $right, $value)
        {
            assert('is_string($moduleClassName)');
            assert('$permitable instanceof Permitable');
            assert('$permitable->id > 0');
            assert('is_string($right)');
            assert('is_int($value) || $value == null || $value == ""');
            if (!empty($value) && $value    == Right::ALLOW)
            {
                $permitable->setRight   ($moduleClassName, $right);
            }
            elseif (!empty($value) && $value == Right::DENY)
            {
                $permitable->setRight   ($moduleClassName, $right, Right::DENY);
            }
            else
            {
                $permitable->removeRight($moduleClassName, $right);
            }
            return $permitable->save();
        }

        /**
         * @return $moduleClassName string
         */
        protected static function getModuleClassNameFromPostConcatenatedIndexString($string)
        {
            assert('is_string($string)');
            $nameParts                      = explode(FormModelUtil::DELIMITER, $string);
            list($moduleClassName, $policy) = $nameParts;
            return $moduleClassName;
        }

        /**
         * @return right string
         */
        protected static function getRightFromPostConcatenatedIndexString($string)
        {
            assert('is_string($string)');
            $nameParts                     = explode(FormModelUtil::DELIMITER, $string);
            list($moduleClassName, $right) = $nameParts;
            return constant($moduleClassName . '::' . $right);
        }

        /**
         * Used to properly type cast incoming POST data
         */
        public static function typeCastPostData($postData)
        {
            assert('is_array($postData)');
            foreach ($postData as $concatenatedIndex => $value)
            {
                if ($value != '')
                {
                    $postData[$concatenatedIndex] = intval($value);
                }
            }
            return $postData;
        }
    }
?>