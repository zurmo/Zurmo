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
    class AutoresponderTest extends ZurmoBaseTest
    {
        // TODO: @Shoaibi: Medium: Rewrite
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreateAndGetAutoresponderById()
        {
            return; //todo:
            $autoresponder = new Autoresponder();
            $autoresponder->type                  = 1;
            $autoresponder->name                  = 'Test Autoresponder name';
            $autoresponder->subject               = 'Test Autoresponder subject';
            $autoresponder->htmlContent           = 'Test HtmlContent';
            $autoresponder->textContent           = 'Test TextContent';
            $autoresponder->secondsFromSubscribe  = 20;
            $this->assertTrue($autoresponder->save());
            $id = $autoresponder->id;
            unset($autoresponder);
            $autoresponder = Autoresponder::getById($id);
            $this->assertEquals(1                           , $autoresponder->type);
            $this->assertEquals('Test Autoresponder name'   , $autoresponder->name);
            $this->assertEquals('Test Autoresponder subject', $autoresponder->subject);
            $this->assertEquals('Test HtmlContent'          , $autoresponder->htmlContent);
            $this->assertEquals('Test TextContent'          , $autoresponder->textContent);
            $this->assertEquals(20                          , $autoresponder->secondsFromSubscribe);
        }

        /**
         * @depends testCreateAndGetAutoresponderById
         */
        public function testGetAutoresponderByName()
        {
            return; //todo:
            $autoresponder = Autoresponder::getByName('Test Autoresponder name');
            $this->assertEquals(1, count($autoresponder));
            $this->assertEquals('Test Autoresponder name', $autoresponder[0]->name);
        }

        /**
         * @depends testCreateAndGetAutoresponderById
         */
        public function testGetLabel()
        {
            return; //todo:
            $autoresponder = Autoresponder::getByName('Test Autoresponder name');
            $this->assertEquals(1, count($autoresponder));
            $this->assertEquals('Autoresponder',  $autoresponder[0]::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Autoresponders', $autoresponder[0]::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testCreateAndGetAutoresponderById
         */
        public function testDeleteAutoresponder()
        {
            return; //todo:
            $autoresponder = new Autoresponder();
            $autoresponder->type                  = 1;
            $autoresponder->name                  = 'Test Autoresponder name1';
            $autoresponder->subject               = 'Test Autoresponder subject1';
            $autoresponder->htmlContent           = 'Test HtmlContent1';
            $autoresponder->textContent           = 'Test TextContent1';
            $autoresponder->secondsFromSubscribe  = 30;
            $this->assertTrue($autoresponder->save());
            $autoresponder = Autoresponder::getAll();
            $this->assertEquals(2, count($autoresponder));
            $autoresponder[0]->delete();
            $autoresponder = Autoresponder::getAll();
            $this->assertEquals(1, count($autoresponder));
        }
    }
?>