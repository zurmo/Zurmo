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

    class UserSearch
    {
        /**
         * For a give User name, run a partial search by
         * full name and retrieve user models.
         * @param string $partialName
         * @param int $pageSize
         */
        public static function getUsersByPartialFullName($partialName, $pageSize)
        {
            assert('is_string($partialName)');
            assert('is_int($pageSize)');
            $personTableName   = RedBeanModel::getTableName('Person');
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('User');
            $joinTablesAdapter->addFromTableAndGetAliasName($personTableName, "{$personTableName}_id");
            $fullNameSql = DatabaseCompatibilityUtil::concat(array('person.firstname',
                                                                   '\' \'',
                                                                   'person.lastname'));
             $where  = '(_user.hidefromselecting is null OR _user.hidefromselecting = 0) and ';
             $where .= "      (person.firstname      like lower('$partialName%') or "    .
                       "       person.lastname       like lower('$partialName%') or "    .
                       "       $fullNameSql like lower('$partialName%')) ";
            return User::getSubset($joinTablesAdapter, null, $pageSize,
                                            $where, "person.firstname, person.lastname");
        }

        /**
         * @param string $emailAddress
         * @param null|string $operatorType
         * @param bool $filterOutHideFromSelecting
         * @return An
         */
        public static function getUsersByEmailAddress($emailAddress, $operatorType = null, $filterOutHideFromSelecting = false)
        {
            assert('is_string($emailAddress)');
            assert('$operatorType == null || is_string($operatorType)');
            assert('is_bool($filterOutHideFromSelecting)');
            if ($operatorType == null)
            {
              $operatorType = 'equals';
            }
            $metadata = array();
            $metadata['clauses'] = array(
                    1 => array(
                            'attributeName'        => 'primaryEmail',
                            'relatedAttributeName' => 'emailAddress',
                            'operatorType'         => $operatorType,
                            'value'                => $emailAddress,
                    ),
            );
            if ($filterOutHideFromSelecting)
            {
                $metadata['clauses'][2] = array(
                    'attributeName'        => 'hideFromSelecting',
                    'operatorType'         => 'equals',
                    'value'                => 0);
                $metadata['clauses'][3] = array(
                    'attributeName'        => 'hideFromSelecting',
                    'operatorType'         => 'isNull',
                    'value'                => null);
                $metadata['structure'] = '(1 and (2 or 3))';
            }
            else
            {
                $metadata['structure'] = '(1)';
            }
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('User');
            $where  = RedBeanModelDataProvider::makeWhere('User', $metadata, $joinTablesAdapter);
            $users = User::getSubset($joinTablesAdapter, null, null, $where);
            return $users;
        }
    }
?>
