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
    class MarketingListMembersUtilTest extends ZurmoBaseTest
    {
        public function testMakeSearchAttributeData()
        {
            $marketingListId            = 1;
            $filterBySubscriptionType   = null;
            $filterBySearchTerm         = null;
            $searchAttributeData1       = MarketingListMembersUtil::makeSearchAttributeData($marketingListId,
                                                                                            $filterBySubscriptionType,
                                                                                             $filterBySearchTerm);

            $this->assertCount(1, $searchAttributeData1);
            $this->assertArrayHasKey(0, $searchAttributeData1);
            $this->assertArrayHasKey('MarketingListMember', $searchAttributeData1[0]);
            $this->assertArrayHasKey('clauses', $searchAttributeData1[0]['MarketingListMember']);
            $this->assertArrayHasKey('structure', $searchAttributeData1[0]['MarketingListMember']);
            $this->assertEquals(1, $searchAttributeData1[0]['MarketingListMember']['structure']);

            $clauses1       = $searchAttributeData1[0]['MarketingListMember']['clauses'];
            $this->assertCount(1, $clauses1);
            $this->assertArrayHasKey(1, $clauses1);
            $this->assertCount(4, $clauses1[1]);
            $this->assertArrayHasKey('attributeName', $clauses1[1]);
            $this->assertEquals('marketingList', $clauses1[1]['attributeName']);
            $this->assertArrayHasKey('relatedAttributeName', $clauses1[1]);
            $this->assertEquals('id', $clauses1[1]['relatedAttributeName']);
            $this->assertArrayHasKey('operatorType', $clauses1[1]);
            $this->assertEquals('equals', $clauses1[1]['operatorType']);
            $this->assertArrayHasKey('value', $clauses1[1]);
            $this->assertEquals($marketingListId, $clauses1[1]['value']);

            $filterBySubscriptionType   = MarketingListMembersConfigurationForm::FILTERED_USER_ALL;
            $searchAttributeData2       = MarketingListMembersUtil::makeSearchAttributeData($marketingListId,
                                                                                            $filterBySubscriptionType,
                                                                                            $filterBySearchTerm);
            $this->assertEquals($searchAttributeData1, $searchAttributeData2);

            $filterBySubscriptionType   = MarketingListMembersConfigurationForm::FILTER_USER_SUBSCRIBERS;
            $searchAttributeData3       = MarketingListMembersUtil::makeSearchAttributeData($marketingListId,
                                                                                        $filterBySubscriptionType,
                                                                                        $filterBySearchTerm);
            $this->assertCount(1, $searchAttributeData3);
            $this->assertArrayHasKey(0, $searchAttributeData3);
            $this->assertArrayHasKey('MarketingListMember', $searchAttributeData3[0]);
            $this->assertArrayHasKey('clauses', $searchAttributeData3[0]['MarketingListMember']);
            $this->assertArrayHasKey('structure', $searchAttributeData3[0]['MarketingListMember']);
            $this->assertEquals('(1 and 2)', $searchAttributeData3[0]['MarketingListMember']['structure']);

            $clauses3       = $searchAttributeData3[0]['MarketingListMember']['clauses'];
            $this->assertCount(2, $clauses3);
            $this->assertArrayHasKey(2, $clauses3);
            $this->assertCount(3, $clauses3[2]);
            $this->assertArrayHasKey('attributeName', $clauses3[2]);
            $this->assertEquals('unsubscribed', $clauses3[2]['attributeName']);
            $this->assertArrayHasKey('operatorType', $clauses3[2]);
            $this->assertEquals('equals', $clauses3[2]['operatorType']);
            $this->assertArrayHasKey('value', $clauses3[2]);
            $this->assertEquals(0, $clauses3[2]['value']);

            $filterBySubscriptionType   = MarketingListMembersConfigurationForm::FILTER_USER_UNSUBSCRIBERS;
            $searchAttributeData4       = MarketingListMembersUtil::makeSearchAttributeData($marketingListId,
                                                                                            $filterBySubscriptionType,
                                                                                            $filterBySearchTerm);
            $this->assertEquals(1, $searchAttributeData4[0]['MarketingListMember']['clauses'][2]['value']);

            $filterBySearchTerm         = 'ja';
            $searchAttributeData5       = MarketingListMembersUtil::makeSearchAttributeData($marketingListId,
                                                                                            $filterBySubscriptionType,
                                                                                            $filterBySearchTerm);

            $this->assertCount(1, $searchAttributeData5);
            $this->assertArrayHasKey(0, $searchAttributeData5);
            $this->assertArrayHasKey('MarketingListMember', $searchAttributeData5[0]);
            $this->assertArrayHasKey('clauses', $searchAttributeData5[0]['MarketingListMember']);
            $this->assertArrayHasKey('structure', $searchAttributeData5[0]['MarketingListMember']);
            $this->assertEquals('(1 and 2) and (3 or 4 or 5 or 6)', $searchAttributeData5[0]['MarketingListMember']['structure']);

            $clauses5       = $searchAttributeData5[0]['MarketingListMember']['clauses'];
            $this->assertCount(6, $clauses5);
            $this->assertArrayHasKey(3, $clauses5);
            $this->assertCount(4, $clauses5[3]);
            $this->assertArrayHasKey('attributeName', $clauses5[3]);
            $this->assertEquals('contact', $clauses5[3]['attributeName']);
            $this->assertArrayHasKey('relatedAttributeName', $clauses5[3]);
            $this->assertEquals('firstName', $clauses5[3]['relatedAttributeName']);
            $this->assertArrayHasKey('operatorType', $clauses5[3]);
            $this->assertEquals('startsWith', $clauses5[3]['operatorType']);
            $this->assertArrayHasKey('value', $clauses5[3]);
            $this->assertEquals($filterBySearchTerm, $clauses5[3]['value']);

            $this->assertArrayHasKey(4, $clauses5);
            $this->assertCount(4, $clauses5[4]);
            $this->assertArrayHasKey('attributeName', $clauses5[4]);
            $this->assertEquals('contact', $clauses5[4]['attributeName']);
            $this->assertArrayHasKey('relatedAttributeName', $clauses5[4]);
            $this->assertEquals('lastName', $clauses5[4]['relatedAttributeName']);
            $this->assertArrayHasKey('operatorType', $clauses5[4]);
            $this->assertEquals('startsWith', $clauses5[4]['operatorType']);
            $this->assertArrayHasKey('value', $clauses5[4]);
            $this->assertEquals($filterBySearchTerm, $clauses5[4]['value']);

            $this->assertArrayHasKey(5, $clauses5);
            $this->assertCount(2, $clauses5[5]);
            $this->assertArrayHasKey('attributeName', $clauses5[5]);
            $this->assertEquals('contact', $clauses5[5]['attributeName']);
            $this->assertArrayHasKey('relatedModelData', $clauses5[5]);
            $this->assertCount(4, $clauses5[5]['relatedModelData']);
            $this->assertArrayHasKey('attributeName', $clauses5[5]['relatedModelData']);
            $this->assertEquals('primaryEmail', $clauses5[5]['relatedModelData']['attributeName']);
            $this->assertArrayHasKey('relatedAttributeName', $clauses5[5]['relatedModelData']);
            $this->assertEquals('emailAddress', $clauses5[5]['relatedModelData']['relatedAttributeName']);
            $this->assertArrayHasKey('operatorType', $clauses5[5]['relatedModelData']);
            $this->assertEquals('startsWith', $clauses5[5]['relatedModelData']['operatorType']);
            $this->assertArrayHasKey('value', $clauses5[5]['relatedModelData']);
            $this->assertEquals($filterBySearchTerm, $clauses5[5]['relatedModelData']['value']);

            $this->assertArrayHasKey(6, $clauses5);
            $this->assertCount(2, $clauses5[6]);
            $this->assertArrayHasKey('attributeName', $clauses5[6]);
            $this->assertEquals('contact', $clauses5[6]['attributeName']);
            $this->assertArrayHasKey('relatedModelData', $clauses5[6]);
            $this->assertCount(4, $clauses5[6]['relatedModelData']);
            $this->assertArrayHasKey('attributeName', $clauses5[6]['relatedModelData']);
            $this->assertEquals('secondaryEmail', $clauses5[6]['relatedModelData']['attributeName']);
            $this->assertArrayHasKey('relatedAttributeName', $clauses5[6]['relatedModelData']);
            $this->assertEquals('emailAddress', $clauses5[6]['relatedModelData']['relatedAttributeName']);
            $this->assertArrayHasKey('operatorType', $clauses5[6]['relatedModelData']);
            $this->assertEquals('startsWith', $clauses5[6]['relatedModelData']['operatorType']);
            $this->assertArrayHasKey('value', $clauses5[6]['relatedModelData']);
            $this->assertEquals($filterBySearchTerm, $clauses5[6]['relatedModelData']['value']);
        }

        public function testMakeSortAttributeData()
        {
            $sortAttributes = MarketingListMembersUtil::makeSortAttributeData();
            $this->assertArrayHasKey('MarketingListMember', $sortAttributes);
            $this->assertCount(1, $sortAttributes);
            $this->assertEquals('createdDateTime', $sortAttributes['MarketingListMember']);
        }
    }
?>