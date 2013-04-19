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

    class MashableUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = User::getByUsername('super');
            $billy = UserTestHelper::createBasicUser('billy');
        }

        public function testCreateMashableInboxRulesByModel()
        {
            $mashableInboxRules = MashableUtil::createMashableInboxRulesByModel('conversation');
            $this->assertEquals('ConversationMashableInboxRules', get_class($mashableInboxRules));
        }

        public function testGetModelDataForCurrentUserByInterfaceName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $mashableModelData = MashableUtil::getModelDataForCurrentUserByInterfaceName('MashableInboxInterface');
            $this->assertEquals(3, count($mashableModelData));
            Yii::app()->user->userModel = User::getByUsername('billy');
            $mashableModelData = MashableUtil::getModelDataForCurrentUserByInterfaceName('MashableInboxInterface');
            $this->assertEquals(1, count($mashableModelData));
        }

        public function testGetUnreadCountForCurrentUserByModelClassName()
        {
            $rules = $this->getMock('ConversationMashableInboxRules', array('getUnreadCountForCurrentUser'));
            $rules->expects($this->once())
                  ->method('getUnreadCountForCurrentUser')
                  ->will($this->returnValue(100));
            $mashableUtil = $this->getMockClass('MashableUtil', array('createMashableInboxRulesByModel'));
            $mashableUtil::staticExpects($this->once())
                ->method('createMashableInboxRulesByModel')
                ->will($this->returnValue($rules));
            $count = $mashableUtil::getUnreadCountForCurrentUserByModelClassName('Conversation');
            $this->assertEquals($count, 100);
        }

        public function testGetUnreadCountMashableInboxForCurrentUser()
        {
            $mashableInboxModels = array(
                'Conversation'  => 'conversationLabel',
                'Mission'       => 'missionLabel',
            );
            $mashableUtil = $this->getMockClass('MashableUtil', array('getModelDataForCurrentUserByInterfaceName',
                                                                      'getUnreadCountForCurrentUserByModelClassName'));
            $mashableUtil::staticExpects($this->once())
                ->method('getModelDataForCurrentUserByInterfaceName')
                ->will($this->returnValue($mashableInboxModels));
            $mashableUtil::staticExpects($this->exactly(2))
                ->method('getUnreadCountForCurrentUserByModelClassName')
                ->will($this->onConsecutiveCalls(27, 11));
            $count = $mashableUtil::GetUnreadCountMashableInboxForCurrentUser();
            $this->assertEquals($count, 38);
        }

        public function testGetSearchAttributeMetadataForMashableInboxByModelClassName()
        {
            $metadataMashableInboxForModel1
                = array(
                    'clauses'       => array(1 => 'testMetadataForMashableInboxModel1'),
                    'structure'     => '1',
                );
            $searchAttributeDataForModel1
                = array(
                    'clauses'       => array(1 => 'testSearchClauseForModel1'),
                    'structure'     => '1',
                );
            $metadataFilteredByFilteredByForModel1
                = array(
                    'clauses'       => array(1 => 'testClauseForFilteredByForModel1'),
                    'structure'     => '1',
                );
            $searchAttributeDataForModel2
                = array(
                    'clauses'       => array(1 => 'testSearchClauseForModel2'),
                    'structure'     => '1',
                );
            $metadataFilteredByFilteredByForModel2
                = array(
                    'clauses'       => array(1 => 'testClauseForFilteredByForModel2'),
                    'structure'     => '1',
                );
            $rules
                = $this->getMock('ConversationMashableInboxRules', array('getMetadataForMashableInbox',
                                                                         'getSearchAttributeData',
                                                                         'getMetadataFilteredByFilteredBy'));
            $rules
                ->expects($this->exactly(2))
                ->method('getMetadataForMashableInbox')
                ->will($this->onConsecutiveCalls($metadataMashableInboxForModel1, null));
            $rules
                ->expects($this->exactly(2))
                ->method('getSearchAttributeData')
                ->will($this->onConsecutiveCalls($searchAttributeDataForModel1, $searchAttributeDataForModel2));
            $rules
                ->expects($this->exactly(2))
                ->method('getMetadataFilteredByFilteredBy')
                ->will($this->onConsecutiveCalls($metadataFilteredByFilteredByForModel1, $metadataFilteredByFilteredByForModel2));
            $mashableUtil
                = $this->getMockClass('MashableUtil', array('createMashableInboxRulesByModel'));
            $mashableUtil
                ::staticExpects($this->exactly(2))
                ->method('createMashableInboxRulesByModel')
                ->will($this->returnValue($rules));
            $searchAttributesData
                = $mashableUtil::getSearchAttributeMetadataForMashableInboxByModelClassName(
                                      array('model1', 'model2'),
                                      MashableInboxForm::FILTERED_BY_ALL);
            $this->assertEquals(
                    array('model1' => array(
                                        'clauses'   => array(1 => 'testMetadataForMashableInboxModel1',
                                                             2 => 'testSearchClauseForModel1',
                                                             3 => 'testClauseForFilteredByForModel1'),
                                        'structure' => '((1) and (2)) and (3)')),
                   $searchAttributesData[0]);
            $this->assertEquals(
                    array('model2' => array(
                                        'clauses'   => array(1 => 'testSearchClauseForModel2',
                                                             2 => 'testClauseForFilteredByForModel2'),
                                        'structure' => '(1) and (2)')),
                   $searchAttributesData[1]);
        }

        public function testGetSortAttributesByMashableInboxModelClassNames()
        {
            $rules
                = $this->getMock('ConversationMashableInboxRules',
                                 array('getMachableInboxOrderByAttributeName'));
            $rules
                ->expects($this->exactly(2))
                ->method('getMachableInboxOrderByAttributeName')
                ->will($this->onConsecutiveCalls('attributeForModel1', 'attributeForModel2'));
            $mashableUtil
                = $this->getMockClass('MashableUtil', array('createMashableInboxRulesByModel'));
            $mashableUtil
                ::staticExpects($this->exactly(2))
                ->method('createMashableInboxRulesByModel')
                ->will($this->returnValue($rules));
            $sortAttributes
                = $mashableUtil::getSortAttributesByMashableInboxModelClassNames(
                                      array('model1', 'model2'));
            $this->assertEquals('attributeForModel1', $sortAttributes['model1']);
            $this->assertEquals('attributeForModel2', $sortAttributes['model2']);
        }

        public function testRenderSummaryContent()
        {
            $model
                = $this->getMockForAbstractClass('RedBeanModel');
            $rules
                = $this->getMock('ConversationMashableInboxRules',
                                 array('getSummaryContentTemplate',
                                       'getModelStringContent',
                                       'getModelCreationTimeContent'));
            $rules
                ->expects($this->once())
                ->method('getSummaryContentTemplate')
                ->will($this->returnValue('{modelStringContent} - {modelCreationTimeContent}'));
            $rules
                ->expects($this->once())
                ->method('getModelStringContent')
                ->with($model)
                ->will($this->returnValue('string'));
            $rules
                ->expects($this->once())
                ->method('getModelCreationTimeContent')
                ->with($model)
                ->will($this->returnValue('time'));
            $mashableUtil
                = $this->getMockClass('MashableUtil', array('createMashableInboxRulesByModel'));
            $mashableUtil
                ::staticExpects($this->once())
                ->method('createMashableInboxRulesByModel')
                ->will($this->returnValue($rules));
            $content
                = $mashableUtil::renderSummaryContent($model);
            $this->assertContains('string - time',          $content);
            $this->assertContains('model-tag conversation', $content);
        }

        public function testResolveContentTemplate()
        {
            $data = array(
                'testVar1' => 'subVar1',
                'testVar2' => 'subVar2',
            );
            $template = '{testVar1} will be resolved and {testVar2} too';
            $content = MashableUtil::resolveContentTemplate($template, $data);
            $this->assertEquals('subVar1 will be resolved and subVar2 too', $content);
            $data = array(
                'testVar1' => 'subVar1',
            );
            $content = MashableUtil::resolveContentTemplate($template, $data);
            $this->assertEquals($content, 'subVar1 will be resolved and {testVar2} too');
        }

        public function testgetTimeSinceLatestUpdate()
        {
            //30 minutes ago
            $timeStampLatestUpdate  = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - (30 * 60));
            $timeSinceLastestUpdate = MashableUtil::getTimeSinceLatestUpdate($timeStampLatestUpdate);
            $this->assertEquals($timeSinceLastestUpdate, '0 hours ago');

            //58 minutes ago
            $timeStampLatestUpdate  = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - (58 * 60));
            $timeSinceLastestUpdate = MashableUtil::getTimeSinceLatestUpdate($timeStampLatestUpdate);
            $this->assertEquals($timeSinceLastestUpdate, '0 hours ago');

            //61 minutes ago
            $timeStampLatestUpdate  = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - (61 * 60));
            $timeSinceLastestUpdate = MashableUtil::getTimeSinceLatestUpdate($timeStampLatestUpdate);
            $this->assertEquals($timeSinceLastestUpdate, '1 hour ago');

            //3 hours ago
            $timeStampLatestUpdate  = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - (3 * 60 * 60));
            $timeSinceLastestUpdate = MashableUtil::getTimeSinceLatestUpdate($timeStampLatestUpdate);
            $this->assertEquals($timeSinceLastestUpdate, '3 hours ago');

            //27 hours ago
            $timeStampLatestUpdate  = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - (27 * 60 * 60));
            $timeSinceLastestUpdate = MashableUtil::getTimeSinceLatestUpdate($timeStampLatestUpdate);
            $this->assertEquals($timeSinceLastestUpdate, '1 day ago');

            //10 days ago
            $timeStampLatestUpdate  = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - (10 * 24 * 60 * 60));
            $timeSinceLastestUpdate = MashableUtil::getTimeSinceLatestUpdate($timeStampLatestUpdate);
            $this->assertEquals($timeSinceLastestUpdate, '10 days ago');
        }

        public function testMergeMetada()
        {
            $firstMetadata  = null;
            $secondMetadata = null;
            $mergedMetadata = MashableUtil::mergeMetada($firstMetadata, $secondMetadata);
            $this->assertEquals($mergedMetadata['clauses'],   array());
            $this->assertEquals($mergedMetadata['structure'], null);

            $firstMetadata  = array(
                    'clauses'       => array(1 => 'testClause1'),
                    'structure'     => '1',
            );
            $secondMetadata = null;
            $mergedMetadata = MashableUtil::mergeMetada($firstMetadata, $secondMetadata);
            $this->assertEquals(array(1 => 'testClause1'), $mergedMetadata['clauses']);
            $this->assertEquals('1', $mergedMetadata['structure']);

            $firstMetadata  = null;
            $secondMetadata = array(
                    'clauses'       => array(1 => 'testClause1'),
                    'structure'     => '1',
            );
            $mergedMetadata = MashableUtil::mergeMetada($firstMetadata, $secondMetadata);
            $this->assertEquals($mergedMetadata['clauses'],   array(1 => 'testClause1'));
            $this->assertEquals($mergedMetadata['structure'], '1');

            $firstMetadata  = array(
                    'clauses'       => array(1 => 'testClause1'),
                    'structure'     => '1',
            );
            $secondMetadata  = array(
                    'clauses'       => array(1 => 'testClause1ForSecondMetadata'),
                    'structure'     => '1',
            );
            $mergedMetadata = MashableUtil::mergeMetada($firstMetadata, $secondMetadata);
            $this->assertEquals(array(1 => 'testClause1',
                                      2 => 'testClause1ForSecondMetadata'),
                                $mergedMetadata['clauses']);
            $this->assertEquals('(1) and (2)', $mergedMetadata['structure']);

            $firstMetadata  = array(
                    'clauses'       => array(1 => 'testClause1',
                                             2 => 'testClause2',
                                             3 => 'testClause3',
                                        ),
                    'structure'     => '1 and (2 or 3)',
            );
            $secondMetadata  = array(
                    'clauses'       => array(1 => 'testClause1ForSecondMetadata',
                                             2 => 'testClause2ForSecondMetadata',
                                        ),
                    'structure'     => '1 and 2',
            );
            $mergedMetadata = MashableUtil::mergeMetada($firstMetadata, $secondMetadata, false);
            $this->assertEquals($mergedMetadata['clauses'],   array(1 => 'testClause1',
                                                                    2 => 'testClause2',
                                                                    3 => 'testClause3',
                                                                    4 => 'testClause1ForSecondMetadata',
                                                                    5 => 'testClause2ForSecondMetadata'));
            $this->assertEquals($mergedMetadata['structure'], '(1 and (2 or 3)) or (4 and 5)');

            $firstMetadata  = array(
                    'clauses'       => array(1 => 'testClause1'),
                    'structure'     => '1',
            );
            $secondMetadata  = array(
                    'clauses'       => array(1 => 'testClause1ForSecondMetadata'),
                    'structure'     => '1',
            );
            $mergedMetadata = MashableUtil::mergeMetada($firstMetadata, $secondMetadata);
            $this->assertEquals(array(1 => 'testClause1',
                                      2 => 'testClause1ForSecondMetadata'),
                                $mergedMetadata['clauses']);
            $this->assertEquals('(1) and (2)', $mergedMetadata['structure']);

            $firstMetadata  = array(
                    'clauses'       => array(1 => 'testClause1'),
                    'structure'     => '1',
            );
            $secondMetadata  = array(
                    'clauses'       => array(1 => 'testClause1ForSecondMetadata',
                                             2 => 'testClause2ForSecondMetadata',
                                             3 => 'testClause3ForSecondMetadata',
                                             4 => 'testClause4ForSecondMetadata'),
                    'structure'     => '((1 and 2) or (3 and 4))',
            );
            $mergedMetadata = MashableUtil::mergeMetada($firstMetadata, $secondMetadata);
            $this->assertEquals(array(1 => 'testClause1',
                                      2 => 'testClause1ForSecondMetadata',
                                      3 => 'testClause2ForSecondMetadata',
                                      4 => 'testClause3ForSecondMetadata',
                                      5 => 'testClause4ForSecondMetadata'),
                                $mergedMetadata['clauses']);
            $this->assertEquals('(1) and (((2 and 3) or (4 and 5)))', $mergedMetadata['structure']);
        }

        public function testSaveSelectedOptionsAsStickyData()
        {
            $testData = array(
                'optionForModel'    => 'aaaaa',
                'filteredBy'        => 'bbbbb',
                'searchTerm'        => 'ccccc');
            $mashableInboxForm = new MashableInboxForm();
            $mashableInboxForm->setAttributes($testData);
            $key = MashableUtil::resolveKeyByModuleAndModel('MashableInboxModule', 'testClassName');
            MashableUtil::saveSelectedOptionsAsStickyData($mashableInboxForm, 'testClassName');
            $this->assertEquals($testData, StickyUtil::getDataByKey($key));

            $testData2 = array(
                'optionForModel'    => 'aaaaa',
                'filteredBy'        => 'bbbbb',
                'searchTerm'        => 'ccccc',
                'selectedIds'       => 'ddddd',
                'massAction'        => 'eeeee');
            $mashableInboxForm = new MashableInboxForm();
            $mashableInboxForm->setAttributes($testData);
            StickyUtil::clearDataByKey($key);
            MashableUtil::saveSelectedOptionsAsStickyData($mashableInboxForm, 'testClassName');
            $this->assertEquals($testData, StickyUtil::getDataByKey($key));
        }

        public function testRestoreSelectedOptionsAsStickyData()
        {
            $key = MashableUtil::
                        resolveKeyByModuleAndModel('MashableInboxModule', 'testClassName');
            StickyUtil::clearDataByKey($key);
            $mashableInboxForm = MashableUtil::
                                    restoreSelectedOptionsAsStickyData('testClassName');
            $mashableInboxFormForCompare = new MashableInboxForm();
            $this->assertEquals($mashableInboxFormForCompare->attributes,
                                $mashableInboxForm->attributes);
            $testData = array(
                'optionForModel'    => 'aaaaa',
                'filteredBy'        => 'bbbbb',
                'searchTerm'        => 'ccccc');
            $key = MashableUtil::
                        resolveKeyByModuleAndModel('MashableInboxModule', 'testClassName');
            StickyUtil::clearDataByKey($key);
            StickyUtil::setDataByKeyAndData($key, $testData);
            $mashableInboxForm = MashableUtil::
                                    restoreSelectedOptionsAsStickyData('testClassName');
            $this->assertEquals($testData, array_intersect($testData, StickyUtil::getDataByKey($key)));
        }

        public function testResolveKeyByModuleAndModel()
        {
            $key = MashableUtil::resolveKeyByModuleAndModel('testModule', 'testClassName');
            $this->assertEquals('testModule_testClassName', $key);
        }
    }
?>