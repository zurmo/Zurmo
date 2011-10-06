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

    class PortletTest extends BaseTest
    {
        public function testSavePortlet()
        {
            $user = UserTestHelper::createBasicUser('Billy');
            $portlet = new Portlet();
            $portlet->column    = 2;
            $portlet->position  = 5;
            $portlet->layoutId  = 'Test';
            $portlet->collapsed = true;
            $portlet->viewType  = 'RssReader';
            $portlet->serializedViewData = serialize(array('a' => 'apple', 'b' => 'bannana'));
            $portlet->user      = $user;
            $this->assertTrue($portlet->save());
            $portlet = Portlet::getById($portlet->id);
            $this->assertEquals(2,                                          $portlet->column);
            $this->assertEquals(5,                                          $portlet->position);
            $this->assertEquals('Test',                                     $portlet->layoutId);
            //$this->assertEquals(true,                                       $portlet->collapsed); //reenable once working
            $this->assertEquals('RssReader',                               $portlet->viewType);
            $this->assertEquals($user->id,                                  $portlet->user->id);
            $this->assertNotEquals(array('a' => 'apple', 'b' => 'bannana'), $portlet->serializedViewData);
            $this->assertEquals   (array('a' => 'apple', 'b' => 'bannana'), unserialize($portlet->serializedViewData));
        }

        /**
         * @depends testSavePortlet
         */
        public function testSaveCollectionUsingDefaultMetadata()
        {
            $user = User::getByUserName('billy');
            $params = array('test' => 'test');
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition('abc', $user->id, $params);
            $this->assertEmpty($portlets);

            $defaultMetadata = array(
                'global' => array(
                    'columns' => array(
                        array(
                            'rows' => array(
                                array(
                                    'type' => 'RssReader',
                                ),
                                array(
                                    'type' => 'RssReader',
                                ),
                            )
                        ),
                        array(
                            'rows' => array(
                                array(
                                    'type' => 'RssReader',
                                ),
                                array(
                                    'type' => 'RssReader',
                                ),
                            )
                        )
                    )
                )
            );

            $portletCollection = Portlet::makePortletsUsingMetadataSortedByColumnIdAndPosition('abc', $defaultMetadata, $user, $params);
            $this->assertNotEmpty($portletCollection);
            $testCount = 0;
            foreach ($portletCollection as $column => $columns)
            {
                foreach ($columns as $position => $portlet)
                {
                    $testCount++;
                }
            }
            $this->assertEquals($testCount, 4);
            Portlet::savePortlets($portletCollection);

            $portletCollection = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition('abc', $user->id, $params);
            $this->assertNotEmpty($portletCollection);
            $testCount = 0;
            foreach ($portletCollection as $column => $columns)
            {
                foreach ($columns as $position => $portlet)
                {
                    $this->assertEquals($portlet->params, $params);
                    $testCount++;
                }
            }
            $this->assertEquals($testCount, 4);
        }

        public function testShiftPositionsBasedOnColumnReduction()
        {
            $user = User::getByUserName('billy');
            for ($i = 1; $i <= 3; $i++)
            {
                $portlet = new Portlet();
                $portlet->column    = 1;
                $portlet->position  = $i;
                $portlet->layoutId  = 'shiftTest';
                $portlet->collapsed = true;
                $portlet->viewType  = 'RssReader';
                $portlet->user      = $user;
                $this->assertTrue($portlet->save());
            }
            for ($i = 1; $i <= 5; $i++)
            {
                $portlet = new Portlet();
                $portlet->column    = 2;
                $portlet->position  = $i;
                $portlet->layoutId  = 'shiftTest';
                $portlet->collapsed = true;
                $portlet->viewType  = 'RssReader';
                $portlet->user      = $user;
                $this->assertTrue($portlet->save());
            }
            for ($i = 1; $i <= 4; $i++)
            {
                $portlet = new Portlet();
                $portlet->column    = 3;
                $portlet->position  = $i;
                $portlet->layoutId  = 'shiftTest';
                $portlet->collapsed = true;
                $portlet->viewType  = 'RssReader';
                $portlet->user      = $user;
                $this->assertTrue($portlet->save());
            }

            $this->assertEquals(count(Portlet::getByLayoutIdAndUserSortedById('shiftTest', $user->id)), 12);
            $portletCollection = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition('shiftTest', $user->id, array());
            Portlet::shiftPositionsBasedOnColumnReduction($portletCollection, 2);
            $portletCollection = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition('shiftTest', $user->id, array());
            $this->assertEquals(count($portletCollection), 2);
            $this->assertEquals(count($portletCollection[1]), 7);
            Portlet::shiftPositionsBasedOnColumnReduction($portletCollection, 1);
            $portletCollection = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition('shiftTest', $user->id, array());
            $this->assertEquals(count($portletCollection), 1);
            $this->assertEquals(count($portletCollection[1]), 12);
        }

        /**
         * @depends testSavePortlet
         */
        public function testBooleanSaveValueMatchesBooleanRetrieveValue()
        {
            $user = User::getByUserName('billy');
            $portlet = new Portlet();
            $portlet->column    = 1;
            $portlet->position  = 1;
            $portlet->layoutId  = 'Test';
            $portlet->collapsed = true;
            $portlet->viewType  = 'RssReader';
            $portlet->user      = $user;
            $this->assertTrue($portlet->save());
            $portlet = Portlet::getById($portlet->id);
            $this->assertEquals(1, $portlet->collapsed);
        }

        public function testPortletRulesFactory()
        {
            $viewClassName = 'DetailsView';
            $portletRules = PortletRulesFactory::createPortletRulesByView($viewClassName);
            $this->assertNull($portletRules);
            $viewClassName = 'RelatedListView';
            $portletRules = PortletRulesFactory::createPortletRulesByView($viewClassName);
            $this->assertTrue($portletRules instanceof PortletRules);
            $this->assertTrue($portletRules->allowOnRelationView());
            $this->assertFalse($portletRules->allowOnDashboard());
        }
    }
?>
