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

    class JobsManagerTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testJobInProcess()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $jobInProcess          = new JobInProcess();
            $jobInProcess->type    = 'Monitor';
            $this->assertTrue($jobInProcess->save());

            $id = $jobInProcess->id;

            try
            {
                $jobInProcess = JobInProcess::getByType('SomethingElse');
                $this->fail();
            }
            catch (NotFoundException $e)
            {
                //nothing. passes.
            }
            $jobInProcess = JobInProcess::getByType('Monitor');
            $this->assertEquals(1, count($jobInProcess));
            $this->assertEquals($id, $jobInProcess->id);
            $jobInProcess->delete();
            $this->assertEquals(0, count(JobInProcess::getAll()));
        }

        public function testJobLog()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $jobLog                = new JobLog();
            $jobLog->type          = 'Monitor';
            $jobLog->startDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $jobLog->endDateTime   = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $jobLog->status        = JobLog::STATUS_COMPLETE_WITHOUT_ERROR;

            //Should fail to save because isProcessed is not specified.
            $this->assertFalse($jobLog->save());
            $jobLog->isProcessed = false;
            $this->assertTrue($jobLog->save());
            $id = $jobLog->id;
            $jobLog = JobLog::getById($id);
            $jobLog->delete();
            $this->assertEquals(0, count(JobInProcess::getAll()));
        }
    }
?>
