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
     * Helper class to make a PoliciesForm
     * and populate the data attribute.
     */
    class PoliciesFormUtil
    {
        /**
         * @param $data - combined array of all policies
         * and existing policies on a permitable.  Organized by module.
         * Example below:
         * @code
            <?php
                $data = array(
                    'UsersModule' => array(
                        'POLICY_ENFORCE_STRONG_PASSWORDS'   => array(
                            'displayName' => UsersModule::POLICY_ENFORCE_STRONG_PASSWORDS,
                            'explicit'    => Policy::YES,
                            'inherited'   => null,
                        ),
                        'POLICY_MINIMUM_PASSWORD_LENGTH'   => array(
                            'displayName' => UsersModule::POLICY_MINIMUM_PASSWORD_LENGTH,
                            'explicit'    => null,
                            'inherited'   => null,
                        ),
                        'POLICY_MINIMUM_USERNAME_LENGTH'   => array(
                            'displayName' => UsersModule::POLICY_MINIMUM_USERNAME_LENGTH,
                            'explicit'    => null,
                            'inherited'   => null,
                        ),
                        'POLICY_PASSWORD_EXPIRES'   => array(
                            'displayName' => UsersModule::POLICY_PASSWORD_EXPIRES,
                            'explicit'    => null,
                            'inherited'   => Policy::YES,
                        ),
                        'POLICY_PASSWORD_EXPIRY_DAYS'   => array(
                            'displayName' => UsersModule::POLICY_PASSWORD_EXPIRY_DAYS,
                            'explicit'    => null,
                            'inherited'   => 15,
                        ),
                    ),
                );
            ?>
         * @endcode
         */
        public static function makeFormFromPoliciesData($data)
        {
            assert('is_array($data)');
            $form       = new PoliciesForm();
            $form->data = $data;
            return $form;
        }

        /**
         * Set permitable policies from post
         * @return boolean - true on success
         */
        public static function setPoliciesFromCastedPost(array $validatedAndCastedPostData, $permitable)
        {
            assert('$permitable instanceof Permitable');
            assert('$permitable->id > 0');

            foreach ($validatedAndCastedPostData as $concatenatedIndex => $value)
            {
                $moduleClassName = self::getModuleClassNameFromPostConcatenatedIndexString($concatenatedIndex);
                $policy          = self::getPolicyFromPostConcatenatedIndexString($concatenatedIndex);
                $saved           = self::AddorRemoveSpecificPolicy(
                                        $moduleClassName,
                                        $permitable,
                                        $policy,
                                        $value);
                if (!$saved)
                {
                    return false;
                }
            }
            return true;
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
         * @return policy integer
         */
        protected static function getPolicyFromPostConcatenatedIndexString($string)
        {
            assert('is_string($string)');
            $nameParts                      = explode(FormModelUtil::DELIMITER, $string);
            list($moduleClassName, $policy) = $nameParts;
            return constant($moduleClassName . '::' . $policy);
        }

        /**
         * @return policy id string
         */
        protected static function getPolicyIdFromPostConcatenatedIndexString($string)
        {
            assert('is_string($string)');
            $nameParts                        = explode(FormModelUtil::DELIMITER, $string);
            list($moduleClassName, $policyId) = $nameParts;
            return $policyId;
        }

        /**
         * @return type string
         */
        protected static function getTypeFromPostConcatenatedIndexString($string)
        {
            assert('is_string($string)');
            $nameParts                             = explode(FormModelUtil::DELIMITER, $string);
            list($moduleClassName, $policy, $type) = $nameParts;
            return $type;
        }

        protected static function AddorRemoveSpecificPolicy($moduleClassName, $permitable, $policy, $value)
        {
            assert('is_string($moduleClassName)');
            assert('$permitable instanceof Permitable');
            assert('$permitable->id > 0');
            assert('is_string($policy)');
            assert('is_int($value) || $value == null || $value == ""');
            if (!empty($value))
            {
                $permitable->setPolicy   ($moduleClassName, $policy, $value);
            }
            else
            {
                $permitable->removePolicy($moduleClassName, $policy);
            }
            $saved = $permitable->save();
            return $saved;
        }

        public static function loadFormFromCastedPost(PoliciesForm $form, array $validatedAndCastedPostData)
        {
            $delimiter = FormModelUtil::DELIMITER;
            foreach ($validatedAndCastedPostData as $concatenatedIndex => $value)
            {
                $concatenatedIndex = $form::resolveNameForDelimiterSplit($concatenatedIndex, $delimiter);
                $moduleClassName   = self::getModuleClassNameFromPostConcatenatedIndexString($concatenatedIndex);
                $policyId          = self::getPolicyIdFromPostConcatenatedIndexString($concatenatedIndex);
                $type              = self::getTypeFromPostConcatenatedIndexString($concatenatedIndex);
                if ($value == '')
                {
                    $value = null;
                }
                if ($type == 'helper')
                {
                    $form->data[$moduleClassName][$policyId]['helper']   = $value;
                }
                elseif ($type == null)
                {
                    $form->data[$moduleClassName][$policyId]['explicit'] = $value;
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            return $form;
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