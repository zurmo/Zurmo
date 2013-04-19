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
     * Dynamic search util tests that utilize Zurmo module data to perform the tests. This is why this class is located
     * here instead of in the framework.
     */
    class ZurmoDynamicSearchUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testGetSearchableAttributesAndLabels()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $searchableAttributesAndLabels = DynamicSearchUtil::
                                             getSearchableAttributesAndLabels('OpportunitiesSearchView', 'Opportunity');
             $compareData = array(
                'createdByUser' => 'Created By User',
                'modifiedByUser' => 'Modified By User',
                'owner' => 'Owner',
                'name' => 'Name',
                'probability' => 'Probability',
                'account' => 'Account',
                'amount' => 'Amount',
                'stage' => 'Stage',
                'source' => 'Source',
                'ownedItemsOnly' => 'Only Items I Own',
                'createdDateTime__DateTime' => 'Created Date Time',
                'modifiedDateTime__DateTime' => 'Modified Date Time',
                'closeDate__Date' => 'Close Date',
                'account___name' => 'Account - Name',
            );
            $this->assertEquals($compareData, $searchableAttributesAndLabels);
        }

        public function testResolveAndAddViewDefinedNestedAttributes()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $attributeIndexOrDerivedTypeAndLabels = array();
            DynamicSearchUtil::
                resolveAndAddViewDefinedNestedAttributes(new Contact(), 'ContactsSearchView',
                                                         $attributeIndexOrDerivedTypeAndLabels);
            $compareData = array('account___name' => 'Account - Name');
            $this->assertEquals($compareData, $attributeIndexOrDerivedTypeAndLabels);

            //Test adding more rercursive attributes.
            $metadata = ContactsSearchView::getMetadata();
            $tempMetadata = $metadata;
            $metadata['global']['definedNestedAttributes'] = array(
                        array('account' => array(
                            'opportunities' => array(
                                'name'
                            ),
                        )),
                        array('opportunities' => array(
                            'account' => array(
                                'name'
                            ),
                        )),
            );
            ContactsSearchView::setMetadata($metadata);
            DynamicSearchUtil::
                resolveAndAddViewDefinedNestedAttributes(new Contact(), 'ContactsSearchView',
                                                         $attributeIndexOrDerivedTypeAndLabels);
            $compareData = array(
                                'account___name' => 'Account - Name',
                                'account___opportunities___name' => 'Account - Opportunities - Name',
                                'opportunities___account___name' => 'Opportunities - Account - Name');
            $this->assertEquals($compareData, $attributeIndexOrDerivedTypeAndLabels);
            //Reset metadata.
            ContactsSearchView::setMetadata($tempMetadata);
        }
    }
?>