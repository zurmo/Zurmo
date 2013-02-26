<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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

    class ManageMetadataCommandTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testSaveAllMetadata()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->assertTrue(ContactsModule::loadStartingData());
            $messageLogger              = new MessageLogger();
            InstallUtil::autoBuildDatabase($messageLogger);

            chdir(COMMON_ROOT . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . 'commands');
            $command = "php zurmocTest.php manageMetadata super saveAllMetadata";
            if (!IS_WINNT)
            {
                $command .= ' 2>&1';
            }

            exec($command, $output);

            // Check if data are saved for some specific View
            $moduleMetadata = R::getRow("SELECT * FROM globalmetadata WHERE classname='NotesModule'");
            $this->assertTrue($moduleMetadata['id'] > 0);
            $this->assertTrue(strlen($moduleMetadata['serializedmetadata']) > 0);

            // Check if data are saved for some specific View
            $modelMetadata = R::getRow("SELECT * FROM globalmetadata WHERE classname='Note'");
            $this->assertTrue($modelMetadata['id']> 0);
            $this->assertTrue(strlen($modelMetadata['serializedmetadata']) > 0);

            // Check if data are saved for some specific View
            $viewMetadata = R::getRow("SELECT * FROM globalmetadata WHERE classname='ContactsListView'");
            $this->assertTrue($viewMetadata['id'] > 0);
            $this->assertTrue(strlen($viewMetadata['serializedmetadata']) > 0);
        }
    }
?>
