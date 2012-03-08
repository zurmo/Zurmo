<?php

class JobLogViewWalkthroughTest extends ZurmoWalkthroughBaseTest
{

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SecurityTestHelper::createSuperAdmin();
        $super = User::getByUsername('super');
        Yii::app()->user->userModel = $super;
    }

    public function testRenderStatusAndMessageListContent()
    {
        $super                  = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
        $jobLog                 = new JobLog();
        $jobLog->isProcessed    = true;
        $jobLog->type           = 'Monitor';
        $jobLog->startDateTime  = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
        $jobLog->endDateTime    = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
        $jobLog->status         = JobLog::STATUS_COMPLETE_WITHOUT_ERROR;
        $this->assertTrue($jobLog->save());
        $this->setGetArray(array('type' => 'Monitor'));
        $content                = $this->runControllerWithNoExceptionsAndGetContent('jobsManager/default/jobLogsModalList');
        $this->assertTrue(stripos($content, Yii::t('Default', 'Completed')) !== false);

        $jobLog->status         = JobLog::STATUS_COMPLETE_WITH_ERROR;
        $this->assertTrue($jobLog->save());
        $this->setGetArray(array('type' => 'Monitor'));
        $content                = $this->runControllerWithNoExceptionsAndGetContent('jobsManager/default/jobLogsModalList');
        $this->assertTrue(stripos($content, Yii::t('Default', 'Completed with Errors')) !== false);

        $jobLog->status         = null;
        $this->assertFalse($jobLog->save());
    }

}

?>
