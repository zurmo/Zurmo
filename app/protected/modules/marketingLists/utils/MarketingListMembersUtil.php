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
     * Helper class for working with marketing list member views.
     */
    class MarketingListMembersUtil
    {
        public static function makeSearchAttributeData($marketingListId, $filterBySubscriptionType, $filterBySearchTerm)
        {
            assert('is_int($marketingListId)');
            $searchAttributeData            = array();
            $searchAttributeData['clauses'] = array(
                                                    1 => array(
                                                        'attributeName'        => 'marketingList',
                                                        'relatedAttributeName' => 'id',
                                                        'operatorType'         => 'equals',
                                                        'value'                => $marketingListId,
                                                    ),
                                                );
            $searchAttributeData['structure'] = 1;
            if ($filterBySubscriptionType !== null &&
                    $filterBySubscriptionType !== MarketingListMembersConfigurationForm::FILTERED_USER_ALL)
            {
                $UnsubscribeFlag = ($filterBySubscriptionType == MarketingListMembersConfigurationForm::FILTER_USER_UNSUBSCRIBERS)?
                                                1 : 0;
                $searchAttributeData['clauses'][]  = array(
                                                            'attributeName'   =>  'unsubscribed',
                                                            'operatorType'    =>  'equals',
                                                            'value'           =>  $UnsubscribeFlag,
                                                        );
                $searchAttributeData['structure']   = '(1 and 2)';
            }
            if ($filterBySearchTerm)
            {
                $searchTermAttributeClauses = array(
                                                array(  'attributeName'             => 'contact',
                                                        'relatedAttributeName'      => 'firstName',
                                                        'operatorType'              => 'startsWith',
                                                        'value'                     => $filterBySearchTerm
                                                    ),
                                                array(  'attributeName'             => 'contact',
                                                        'relatedAttributeName'      => 'lastName',
                                                        'operatorType'              => 'startsWith',
                                                        'value'                     => $filterBySearchTerm
                                                ),
                    /*
                     // TODO: @Jason: Low: Bug: Undefined Index: attributeName

                                                array(  'attributeName'             => 'contact',
                                                        'relatedModelData'          => array(
                                                            'concatedAttributeNames'        => array('firstName', 'lastName'),
                                                            'operatorType'                  => 'contains',
                                                            'value'                         => $filterBySearchTerm
                                                        ),
                                                ),
                    /**/
                                                array(  'attributeName'             => 'contact',
                                                        'relatedModelData'          => array(
                                                            'attributeName'                 => 'primaryEmail',
                                                            'relatedAttributeName'          => 'emailAddress',
                                                            'operatorType'                  => 'startsWith',
                                                            'value'                         => $filterBySearchTerm
                                                        ),
                                                ),
                                                array(  'attributeName'             => 'contact',
                                                        'relatedModelData'          => array(
                                                            'attributeName'                 => 'secondaryEmail',
                                                            'relatedAttributeName'          => 'emailAddress',
                                                            'operatorType'                  => 'startsWith',
                                                            'value'                         => $filterBySearchTerm
                                                        ),
                                                ),
                                            );

                $clauseStartIndex = count($searchAttributeData['clauses']) + 1;
                foreach ($searchTermAttributeClauses as $index => $searchTermAttributeClause)
                {
                    $clauseIndex = $clauseStartIndex + $index;
                    $searchAttributeData['clauses'][$clauseIndex] = $searchTermAttributeClause;
                    if ($clauseIndex == $clauseStartIndex)
                    {
                        $structure = ' and (';
                    }
                    else
                    {
                        $structure = ' or ';
                    }
                    $structure .= $clauseIndex;
                    if ($index == (count($searchTermAttributeClauses) -1))
                    {
                        $structure .= ')';
                    }
                    $searchAttributeData['structure'] = $searchAttributeData['structure'] . $structure;
                }
            }
            return array(array('MarketingListMember' => $searchAttributeData));
        }

        public static function makeSortAttributeData()
        {
            $sortAttribute = RedBeanModelDataProvider::getSortAttributeName('MarketingListMember');
            return array('MarketingListMember' => $sortAttribute);
        }
    }
?>