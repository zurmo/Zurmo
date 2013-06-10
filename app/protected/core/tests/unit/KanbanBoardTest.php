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

    class KanbanBoardTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $values = array(
                'Automotive',
                'Adult Entertainment',
                'Financial Services',
                'Mercenaries & Armaments',
            );
            $industryFieldData = CustomFieldData::getByName('Industries');
            $industryFieldData->defaultValue = $values[1];
            $industryFieldData->serializedData = serialize($values);
            if (!$industryFieldData->save())
            {
                throw new FailedToSaveRedBeanModelException();
            }
        }

        public function testResolveKanbanBoardOptionsForSearchModelFromGetArray()
        {
            $_GET['test'] = array('groupByAttributeVisibleValues' => '', 'selectedTheme' => '');
            $kanbanBoard = new KanbanBoard(new AAA(), 'industry');
            $kanbanBoard->setGroupByAttributeVisibleValues(array('a', 'b'));
            $kanbanBoard->setSelectedTheme('someTheme');
            $this->assertEquals(array('a', 'b'), $kanbanBoard->getGroupByAttributeVisibleValues());
            $this->assertEquals('someTheme', $kanbanBoard->getSelectedTheme());
            $this->assertNull($kanbanBoard->getIsActive());
            $searchModel = new AAASearchFormTestModel(new AAA());
            $searchModel->setKanbanBoard($kanbanBoard);
            KanbanBoard::resolveKanbanBoardOptionsForSearchModelFromGetArray($searchModel, 'test');
            $this->assertNull($kanbanBoard->getGroupByAttributeVisibleValues());
            $this->assertNull($kanbanBoard->getSelectedTheme());
            $this->assertNull($kanbanBoard->getIsActive());

            //Now test setting in a selectedTheme and visibleValues
            $_GET['test'] = array('groupByAttributeVisibleValues' => array('c', 'd'), 'selectedTheme' => 'aTheme');
            KanbanBoard::resolveKanbanBoardOptionsForSearchModelFromGetArray($searchModel, 'test');
            $this->assertEquals(array('c', 'd'), $kanbanBoard->getGroupByAttributeVisibleValues());
            $this->assertEquals('aTheme', $kanbanBoard->getSelectedTheme());
            $this->assertTrue($kanbanBoard->getIsActive());
        }

        public function testGetGridViewWidgetPath()
        {
            $compareString = 'application.core.kanbanBoard.widgets.KanbanBoardExtendedGridView';
            $this->assertEquals($compareString, KanbanBoard::getGridViewWidgetPath());
        }

        public function testGetSetIsActive()
        {
            $kanbanBoard = new KanbanBoard(new AAA(), 'industry');
            $this->assertNull($kanbanBoard->getIsActive());
            $kanbanBoard->setIsActive();
            $this->assertTrue($kanbanBoard->getIsActive());
            $kanbanBoard->setIsNotActive();
            $this->assertFalse($kanbanBoard->getIsActive());
        }

        public function testGetGridViewParams()
        {
            $kanbanBoard = new KanbanBoard(new AAA(), 'industry');
            $params      = $kanbanBoard->getGridViewParams();
            $compareData = array('groupByAttribute' => 'industry',
                                 'groupByAttributeVisibleValues'  => array(
                                     'Automotive',
                                     'Adult Entertainment',
                                     'Financial Services',
                                     'Mercenaries & Armaments'),
                                 'groupByDataAndTranslatedLabels' => array(
                                     'Automotive'              => 'Automotive',
                                     'Adult Entertainment'     => 'Adult Entertainment',
                                     'Financial Services'      => 'Financial Services',
                                     'Mercenaries & Armaments' => 'Mercenaries & Armaments'),
                                 'selectedTheme' => null);
            $this->assertEquals($compareData, $params);
        }

        public function testGetAndSetGroupByAttributeVisibleValues()
        {
            $kanbanBoard = new KanbanBoard(new AAA(), 'industry');
            $kanbanBoard->setGroupByAttributeVisibleValues(array('c', 'd'));
            $this->assertEquals(array('c', 'd'), $kanbanBoard->getGroupByAttributeVisibleValues());
        }

        public function testGetAndSetSelectedTheme()
        {
            $kanbanBoard = new KanbanBoard(new AAA(), 'industry');
            $kanbanBoard->setSelectedTheme('red');
            $this->assertEquals('red', $kanbanBoard->getSelectedTheme());
            $themeNamesAndLabelsCompare = array('' => 'White',
                                                'kanban-background-football'    => 'Football',
                                                'kanban-background-tennis'      => 'Tennis',
                                                'kanban-background-motor'       => 'Motor Sport',
                                                'kanban-background-yoga'        => 'Yoga',
                                                );
            $this->assertEquals($themeNamesAndLabelsCompare, $kanbanBoard->getThemeNamesAndLabels());
        }

        public function getAndSetClearSticky()
        {
            $kanbanBoard = new KanbanBoard(new AAA(), 'industry');
            $this->assertFalse($kanbanBoard->getClearSticky());
            $kanbanBoard->setClearSticky();
            $this->assertFalse($kanbanBoard->getClearSticky());
        }

        public function testResolveVisibleValuesForAdaptedMetadata()
        {
            $metadata = array('clauses' => array(), 'structure' => '');
            $kanbanBoard = new KanbanBoard(new AAA(), 'industry');
            $kanbanBoard->resolveVisibleValuesForAdaptedMetadata($metadata);
            $compareData = array();
            $compareData['structure'] = '(1 or 2 or 3 or 4)';
            $compareData['clauses'][1]   = array('attributeName'        => 'industry',
                                                'relatedAttributeName' => 'value',
                                                'operatorType'         => 'equals',
                                                'value'                => 'Automotive');
            $compareData['clauses'][2]   = array('attributeName'        => 'industry',
                                                'relatedAttributeName' => 'value',
                                                'operatorType'         => 'equals',
                                                'value'                => 'Adult Entertainment');
            $compareData['clauses'][3]   = array('attributeName'        => 'industry',
                                                'relatedAttributeName' => 'value',
                                                'operatorType'         => 'equals',
                                                'value'                => 'Financial Services');
            $compareData['clauses'][4]   = array('attributeName'        => 'industry',
                                                'relatedAttributeName' => 'value',
                                                'operatorType'         => 'equals',
                                                'value'                => 'Mercenaries & Armaments');
            $this->assertEquals($compareData, $metadata);

            //Now resolve with pre-existing metadata
            $metadata = array('clauses' => array(1 => 'firstClause'), 'structure' => '1');
            $kanbanBoard = new KanbanBoard(new AAA(), 'industry');
            $kanbanBoard->resolveVisibleValuesForAdaptedMetadata($metadata);
            $compareData['structure'] = '(1) and (2 or 3 or 4 or 5)';
            $compareData['clauses'][1]   = 'firstClause';
            $compareData['clauses'][2]   = array('attributeName'        => 'industry',
                                                'relatedAttributeName' => 'value',
                                                'operatorType'         => 'equals',
                                                'value'                => 'Automotive');
            $compareData['clauses'][3]   = array('attributeName'        => 'industry',
                                                'relatedAttributeName' => 'value',
                                                'operatorType'         => 'equals',
                                                'value'                => 'Adult Entertainment');
            $compareData['clauses'][4]   = array('attributeName'        => 'industry',
                                                'relatedAttributeName' => 'value',
                                                'operatorType'         => 'equals',
                                                'value'                => 'Financial Services');
            $compareData['clauses'][5]   = array('attributeName'        => 'industry',
                                                'relatedAttributeName' => 'value',
                                                'operatorType'         => 'equals',
                                                'value'                => 'Mercenaries & Armaments');
            $this->assertEquals($compareData, $metadata);
        }
    }
?>
