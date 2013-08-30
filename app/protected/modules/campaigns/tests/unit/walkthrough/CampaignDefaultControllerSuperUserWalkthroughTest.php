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

    class CampaignDefaultControllerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected $user;

        protected $campaign;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            EmailTemplateTestHelper::createEmailTemplateByName(EmailTemplate::TYPE_CONTACT,
                                                                'Subject 01',
                                                                'Contact',
                                                                'EmailTemplate 01',
                                                                'html',
                                                                'text');
            EmailTemplateTestHelper::createEmailTemplateByName(EmailTemplate::TYPE_CONTACT,
                                                                'Subject 02',
                                                                'Contact',
                                                                'EmailTemplate 02',
                                                                'html',
                                                                'text');
            EmailTemplateTestHelper::createEmailTemplateByName(EmailTemplate::TYPE_CONTACT,
                                                                'Subject 03',
                                                                'Contact',
                                                                'EmailTemplate 03',
                                                                'html',
                                                                'text');
            MarketingListTestHelper::createMarketingListByName('MarketingListName',
                                                               'MarketingList Description',
                                                               'first',
                                                               'first@zurmo.com');
            CampaignTestHelper::createCampaign('campaign01',
                                               'campaign subject 01',
                                               'text content for campaign 01',
                                                'html content for campaign 01',
                                                'fromCampaign',
                                                'fromCampaign@zurmo.com');
            CampaignTestHelper::createCampaign('campaign02',
                                                'campaign subject 02',
                                                'text content for campaign 02',
                                                'html content for campaign 02',
                                                'fromCampaign2',
                                                'fromCampaign2@zurmo.com');
        }

        public function setUp()
        {
            parent::setUp();
            $this->user = User::getByUsername('super');
            Yii::app()->user->userModel = $this->user;
            $campaigns = Campaign::getAll();
            $this->campaign = $campaigns[0];
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            // Test all default controller actions that do not require any POST/GET variables to be passed.
            $this->runControllerWithNoExceptionsAndGetContent('campaigns/default');
            $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/index');
            $content = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/list');
            $compareContent = 'Campaigns will not run properly until scheduled jobs are set up. Contact your administrator.';
            $this->assertTrue(strpos($content, $compareContent) === false);
            $content = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/create');
            $compareContent = 'Campaigns will not run properly until scheduled jobs are set up. Contact your administrator.';
            $this->assertTrue(strpos($content, $compareContent) !== false);
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testWhenJobsHaveRunTheFlashMessageDoesNotShowUp()
        {
            $jobLog                = new JobLog();
            $jobLog->type          = 'CampaignGenerateDueCampaignItems';
            $jobLog->startDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $jobLog->endDateTime   = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $jobLog->status        = JobLog::STATUS_COMPLETE_WITHOUT_ERROR;
            $jobLog->isProcessed   = false;
            $this->assertTrue($jobLog->save());

            $jobLog                = new JobLog();
            $jobLog->type          = 'CampaignMarkCompleted';
            $jobLog->startDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $jobLog->endDateTime   = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $jobLog->status        = JobLog::STATUS_COMPLETE_WITHOUT_ERROR;
            $jobLog->isProcessed   = false;
            $this->assertTrue($jobLog->save());

            $jobLog                = new JobLog();
            $jobLog->type          = 'CampaignQueueMessagesInOutbox';
            $jobLog->startDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $jobLog->endDateTime   = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $jobLog->status        = JobLog::STATUS_COMPLETE_WITHOUT_ERROR;
            $jobLog->isProcessed   = false;
            $this->assertTrue($jobLog->save());

            $jobLog                = new JobLog();
            $jobLog->type          = 'ProcessOutboundEmail';
            $jobLog->startDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $jobLog->endDateTime   = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $jobLog->status        = JobLog::STATUS_COMPLETE_WITHOUT_ERROR;
            $jobLog->isProcessed   = false;
            $this->assertTrue($jobLog->save());

            $content = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/create');
            $compareContent = 'Campaigns will not run properly until scheduled jobs are set up. Contact your administrator.';
            $this->assertTrue(strpos($content, $compareContent) === false);
        }

        /**
         * @depends testWhenJobsHaveRunTheFlashMessageDoesNotShowUp
         */
        public function testSuperUserListAction()
        {
            $content = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/list');
            $this->assertTrue(strpos($content, 'anyMixedAttributes') !== false);
            $this->assertTrue(strpos($content, 'campaign01') !== false);
            $this->assertTrue(strpos($content, 'campaign02') !== false);
            $this->assertTrue(strpos($content, 'Active') !== false);
            //Test the search or paging of the listview.
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $this->setGetArray(array('ajax' => 'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/list');
            $this->assertTrue(strpos($content, 'anyMixedAttributes') === false);
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testSuperUserListSearchAction()
        {
            StickyReportUtil::clearDataByKey('CampaignsSearchForm');
            $this->setGetArray(array(
                'CampaignsSearchForm' => array(
                    'anyMixedAttributesScope'    => array('All'),
                    'anyMixedAttributes'         => 'xyz',
                ) ,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/list');
            $this->assertTrue(strpos($content, 'No results found.') !== false);

            StickyReportUtil::clearDataByKey('CampaignsSearchForm');
            $this->setGetArray(array(
                'CampaignsSearchForm' => array(
                    'anyMixedAttributesScope'    => array('All'),
                    'anyMixedAttributes'         => 'camp',
                ) ,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/list');
            $this->assertTrue(strpos($content, '2 result(s)') !== false);
            $this->assertTrue(strpos($content, '<th id="list-view_c2">Status</th>') !== false);
            $this->assertEquals(1, substr_count($content, 'campaign01'));
            $this->assertEquals(1, substr_count($content, 'campaign02'));
            $this->assertEquals(2, substr_count($content, 'Active'));

            StickyReportUtil::clearDataByKey('CampaignsSearchForm');
            $this->setGetArray(array(
                'CampaignsSearchForm' => array(
                    'anyMixedAttributesScope'    => array('All'),
                    'anyMixedAttributes'         => 'campaign',
                    'selectedListAttributes'     => array('name', 'status', 'createdByUser', 'fromAddress', 'fromName'),
                ) ,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/list');
            $this->assertTrue(strpos($content, '2 result(s)') !== false);
            $this->assertTrue(strpos($content, '<th id="list-view_c2">Status</th>') !== false);
            $this->assertEquals(1, substr_count($content, 'campaign01'));
            $this->assertEquals(1, substr_count($content, 'campaign02'));
            $this->assertEquals(2, substr_count($content, 'Active'));
            $this->assertEquals(2, substr_count($content, 'Clark Kent'));
            $this->assertEquals(4, substr_count($content, '@zurmo.com'));
            $this->assertEquals(6, substr_count($content, 'fromCampaign'));
            $this->assertEquals(3, substr_count($content, 'fromCampaign2'));
            $this->assertEquals(2, substr_count($content, 'fromCampaign@zurmo.com'));
            $this->assertEquals(2, substr_count($content, 'fromCampaign2@zurmo.com'));

            StickyReportUtil::clearDataByKey('CampaignsSearchForm');
            $this->setGetArray(array(
                'clearingSearch'            =>  1,
                'CampaignsSearchForm'  => array(
                    'anyMixedAttributesScope'    => array('All'),
                    'anyMixedAttributes'         => '',
                    'selectedListAttributes'     => array('name', 'status', 'createdByUser', 'fromAddress', 'fromName'),
                    'dynamicClauses'             => array(array(
                        'attributeIndexOrDerivedType'   => 'fromAddress',
                        'structurePosition'             => 1,
                        'fromAddress'                   => 'fromCampaign2@zurmo.com',
                    )),
                    'dynamicStructure'          => '1',
                ) ,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/list');
            $this->assertTrue(strpos($content, '1 result(s)') !== false);
            $this->assertTrue(strpos($content, '<th id="list-view_c2">Status</th>') !== false);
            $this->assertEquals(1, substr_count($content, 'campaign02'));
            $this->assertEquals(1, substr_count($content, 'Active'));
            $this->assertEquals(1, substr_count($content, 'Clark Kent'));
            $this->assertEquals(2, substr_count($content, '@zurmo.com'));
            $this->assertEquals(7, substr_count($content, 'fromCampaign2'));
            $this->assertEquals(2, substr_count($content, 'fromCampaign2@zurmo.com'));

            StickyReportUtil::clearDataByKey('CampaignsSearchForm');
            $this->setGetArray(array(
                'clearingSearch'            =>  1,
                'CampaignsSearchForm'  =>  array(
                    'anyMixedAttributesScope'    => array('All'),
                    'anyMixedAttributes'         => '',
                    'selectedListAttributes'     => array('name', 'status', 'createdByUser', 'fromAddress', 'fromName'),
                    'dynamicClauses'             => array(array(
                        'attributeIndexOrDerivedType'   => 'fromName',
                        'structurePosition'             => 1,
                        'fromName'                   => 'fromCampaign2',
                    )),
                    'dynamicStructure'          => '1',
                ) ,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/list');
            $this->assertTrue(strpos($content, '1 result(s)') !== false);
            $this->assertTrue(strpos($content, '<th id="list-view_c2">Status</th>') !== false);
            $this->assertEquals(1, substr_count($content, 'campaign02'));
            $this->assertEquals(1, substr_count($content, 'Active'));
            $this->assertEquals(1, substr_count($content, 'Clark Kent'));
            $this->assertEquals(2, substr_count($content, '@zurmo.com'));
            $this->assertEquals(7, substr_count($content, 'fromCampaign2'));
            $this->assertEquals(2, substr_count($content, 'fromCampaign2@zurmo.com'));
        }

        /**
         * @depends testSuperUserListAction
         */
        public function testSuperUserCreateAction()
        {
            $content = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/create');
            $this->assertTrue(strpos($content, '<title>ZurmoCRM - Campaigns</title>') !== false);
            $this->assertTrue(strpos($content, '<div id="CampaignsPageView" class="ZurmoDefaultPageView ' .
                                                'ZurmoPageView PageView">') !== false);
            $this->assertTrue(strpos($content, '<div id="MarketingBreadCrumbView" class="BreadCrumbView">' .
                                                '<div class="breadcrumbs">') !== false);
            $this->assertTrue(strpos($content, '<div class="AppContent GridView">') !== false);
            $this->assertTrue(strpos($content, '/marketing/default/index">Marketing</a>') !== false);
            $this->assertTrue(strpos($content, '/campaigns/default/list">Campaigns</a>') !== false);
            $this->assertTrue(strpos($content, '<span>Create</span></div></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="CampaignEditView" class="SecuredEditView EditView DetailsView' .
                                                ' ModelView ConfigurableMetadataView MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<div class="wrapper">') !== false);
            $this->assertTrue(strpos($content, '<h1><span class="truncated-title"><span class="ellipsis-content">' .
                                                'Create Campaign</span></span></h1>') !== false);
            $this->assertTrue(strpos($content, '<div class="wide form">') !== false);
            $this->assertTrue(strpos($content, '<div class="attributesContainer">') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column"><div class="panel">') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label for="Campaign_name" class="required">Name '.
                                                '<span class="required">*</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="Campaign_name" name="Campaign[name]" ' .
                                                'type="text" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '</tr><tr><th><label for="Campaign_marketingList_id" class="required">' .
                                                'Marketing List <span class="required">*</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input name="Campaign[marketingList][id]" ' .
                                                'id="Campaign_marketingList_id" value="" type="hidden"') !== false);
            $this->assertTrue(strpos($content, '<div class="has-model-select">') !== false);
            $this->assertTrue(strpos($content, 'id="Campaign_marketingList_name" type="text" value="" '.
                                                'name="Campaign_marketingList_name"') !== false);
            $this->assertTrue(strpos($content, '<a id="Campaign_marketingList_SelectLink" href="#"><span class="' .
                                                'model-select-icon"></span><span class="z-spinner"></span></a>') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label for="Campaign_fromName" class="required">From Name ' .
                                                '<span class="required">*</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="Campaign_fromName" name="Campaign[fromName]" ' .
                                                'type="text" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label for="Campaign_fromAddress" class="required">From Address ' .
                                                '<span class="required">*</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="Campaign_fromAddress" ' .
                                                'name="Campaign[fromAddress]" type="text" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label for="Campaign_sendOnDateTime" class="required">Send On ' .
                                                '<span class="required">*</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><div class="has-date-select"><input ' .
                                                'id="Campaign_sendOnDateTime" name="Campaign[sendOnDateTime]" ' .
                                                'style="position:relative;z-index:10000;" type="text" ') !== false);
            $this->assertTrue(strpos($content, '</tr><tr><th><label for="Campaign_subject" class="required">Subject ' .
                                                '<span class="required">*</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="Campaign_subject" name="Campaign[subject]" ' .
                                                'type="text" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '<tr><th>Enable Tracking<span id="enable-tracking-tooltip" ' .
                                                'class="tooltip" title="Check to track when recipients open ' .
                                                'an email or click any links.">?</span></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="ytCampaign_enableTracking" type="hidden" ' .
                                                'value="0" name="Campaign[enableTracking]"') !== false);
            $this->assertTrue(strpos($content, '<label class="hasCheckBox c_on"><input id="Campaign_enableTracking" ' .
                                                'name="Campaign[enableTracking]" value="1" checked="checked" ' .
                                                'type="checkbox"') !== false);
            $this->assertTrue(strpos($content, '<tr><th>Support HTML<span id="support-rich-text-tooltip" ' .
                                                'class="tooltip" title="When checked, email will be sent in both text' .
                                                ' and HTML format.  Uncheck to only send text emails">' .
                                                '?</span></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="ytCampaign_supportsRichText" type="hidden"' .
                                                ' value="0" name="Campaign[supportsRichText]"') !== false);
            $this->assertTrue(strpos($content, '<label class="hasCheckBox c_on"><input id="Campaign_supportsRichText"' .
                                                ' name="Campaign[supportsRichText]" value="1" checked="checked"' .
                                                ' type="checkbox"') !== false);
            $this->assertTrue(strpos($content, '<tr><th></th><td colspan="1"><div class="hasDropDown">' .
                                                '<span class="select-arrow"></span>') !== false);
            $this->assertTrue(strpos($content, '<select name="Campaign[contactEmailTemplateNames]" ' .
                                                'id="Campaign_contactEmailTemplateNames_value">') !== false);
            $this->assertTrue(strpos($content, '<option value="">Select a template</option>') !== false);
            $this->assertTrue(strpos($content, '>EmailTemplate 01</option>') !== false);
            $this->assertTrue(strpos($content, '>EmailTemplate 02</option>') !== false);
            $this->assertTrue(strpos($content, '>EmailTemplate 03</option>') !== false);
            $this->assertTrue(strpos($content, '</select></div></td></tr>') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label>Attachments</label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><div id="fileUploadCampaign">' .
                                                '<div class="fileupload-buttonbar clearfix">') !== false);
            $this->assertTrue(strpos($content, '<div class="addfileinput-button"><span>Y</span>') !== false);
            $this->assertTrue(strpos($content, '<strong class="add-label">Add Files</strong>') !== false);
            $this->assertTrue(strpos($content, '<input id="Campaign_files" type="file" name="Campaign_files"') !== false);
            $this->assertTrue(strpos($content, '<div class="fileupload-content">') !== false);
            $this->assertTrue(strpos($content, '<table class="files">') !== false);
            $this->assertTrue(strpos($content, '<div class="right-column">') !== false);
            $this->assertTrue(strpos($content, '<div class="email-template-combined-content">') !== false);
            $this->assertTrue(strpos($content, '<div class="email-template-content"><div class="tabs-nav">') !== false);
            $this->assertTrue(strpos($content, '<a href="#tab1">Text Content</a>') !== false);
            $this->assertTrue(strpos($content, '<a class="active-tab" href="#tab2">Html Content</a>') !== false);
            $this->assertTrue(strpos($content, '<a id="mergetag-guide" class="simple-link" href="#">' .
                                                'MergeTag Guide</a></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="tab1" class=" tab email-template-textContent">') !== false);
            $this->assertTrue(strpos($content, '<th><label for="Campaign_textContent">Text Content</label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><textarea id="Campaign_textContent" ' .
                                                'name="Campaign[textContent]" rows="6" cols="50">' .
                                                '</textarea></td></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="tab2" class="active-tab tab email-template-htmlContent">' .
                                                '<label for="Campaign_htmlContent">Html Content</label>') !== false);
            $this->assertTrue(strpos($content, '<textarea id=\'Campaign_htmlContent\' name=\'Campaign[htmlContent]\'>' .
                                                '</textarea></div></div></td></div>') !== false);
            $this->assertTrue(strpos($content, '<div class="float-bar"><div class="view-toolbar-container ' .
                                                'clearfix dock"><div class="form-toolbar">') !== false);
            $this->assertTrue(strpos($content, '/campaigns/default"><span class="z-label">Cancel</span></a>') !== false);
            $this->assertTrue(strpos($content, '<span class="z-spinner"></span><span class="z-icon"></span>' .
                                                '<span class="z-label">Save and Schedule</span></a></div') !== false);
            $this->assertTrue(strpos($content, '<div id="modalContainer-edit-form">') !== false);

            $this->setPostArray(array('Campaign' => array(
                'name' => '',
                'marketingList' => array('id' => ''),
                'fromName' => '',
                'fromAddress' => '',
                'sendOnDateTime' => '',
                'subject' => '',
                'enableTracking' => '',
                'supportsRichText' => '',
                'textContent' => '',
                'htmlContent' => '',
            )));
            $content = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/create');
            $this->assertTrue(strpos($content, '<div class="errorSummary"><p>Please fix the following' .
                                                ' input errors:</p>') !== false);
            $this->assertTrue(strpos($content, '<li>Name cannot be blank.</li>') !== false);
            $this->assertEquals(1, substr_count($content, '<li>Name cannot be blank.</li>'));
            $this->assertTrue(strpos($content, '<li>Marketing List cannot be blank.</li>') !== false);
            $this->assertTrue(strpos($content, '<li>Supports HTML cannot be blank.</li>') !== false);
            $this->assertTrue(strpos($content, '<li>Send On cannot be blank.</li>') !== false);
            $this->assertTrue(strpos($content, '<li>From Name cannot be blank.</li>') !== false);
            $this->assertTrue(strpos($content, '<li>From Address cannot be blank.</li>') !== false);
            $this->assertTrue(strpos($content, '<li>Subject cannot be blank.</li>') !== false);
            $this->assertTrue(strpos($content, '<li>Please provide at least one of the contents field.</li>') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label class="error required" for="Campaign_name">Name ' .
                                                '<span class="required">*</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="Campaign_name" name="Campaign[name]" ' .
                                                'type="text" maxlength="64" value="" class="error"') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label class="error required" for="Campaign_marketingList_id">' .
                                                'Marketing List <span class="required">*</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label class="error required" for="Campaign_fromName">From Name' .
                                                ' <span class="required">*</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="Campaign_fromName" name="Campaign[fromName]"' .
                                                ' type="text" maxlength="64" value="" class="error"') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label class="error required" for="Campaign_fromAddress">From' .
                                                ' Address <span class="required">*</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="Campaign_fromAddress" name="Campaign' .
                                                '[fromAddress]" type="text" maxlength="64" ' .
                                                'value="" class="error"') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label class="error required" for="Campaign_sendOnDateTime">' .
                                                'Send On <span class="required">*</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label class="error required" for="Campaign_subject">Subject ' .
                                                '<span class="required">*</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="Campaign_subject" name="Campaign[subject]" '.
                                                'type="text" maxlength="64" value="" class="error"') !== false);
            $this->assertTrue(strpos($content, '<input id="Campaign_supportsRichText" name="Campaign[supportsRichText]"' .
                                                ' value="1" type="checkbox" class="error"') !== false);

            $marketingListId                = self::getModelIdByModelNameAndName('MarketingList', 'MarketingListName');
            $this->setPostArray(array('Campaign' => array(
                                        'name' => 'New Campaign using Create',
                                        'marketingList' => array('id' => $marketingListId),
                                        'fromName' => 'Zurmo Sales',
                                        'fromAddress' => 'sales@zurmo.com',
                                        'sendOnDateTime' => '6/13/13 10:54 AM',
                                        'subject' => 'New Campaign using Create Subject',
                                        'enableTracking' => '1',
                                        'supportsRichText' => '0',
                                        'textContent' => 'Text',
                                        'htmlContent' => 'Html',
            )));
            $redirectUrl    = $this->runControllerWithRedirectExceptionAndGetUrl('campaigns/default/create');
            $campaign = Campaign::getByName('New Campaign using Create');
            $this->assertEquals(1, count($campaign));
            $this->assertTrue  ($campaign[0]->id > 0);
            $this->assertEquals('sales@zurmo.com', $campaign[0]->fromAddress);
            $this->assertEquals('Zurmo Sales', $campaign[0]->fromName);
            $this->assertEquals('New Campaign using Create Subject', $campaign[0]->subject);
            $this->assertEquals('1', $campaign[0]->enableTracking);
            $this->assertEquals('0', $campaign[0]->supportsRichText);
            $this->assertEquals('Text', $campaign[0]->textContent);
            $this->assertEquals('Html', $campaign[0]->htmlContent);
            $this->assertEquals(DateTimeUtil::convertTimestampToDbFormatDateTime(strtotime('6/13/13 10:54 AM')),
                                                                                        $campaign[0]->sendOnDateTime);
            $this->assertEquals($marketingListId, $campaign[0]->marketingList->id);
            $this->assertTrue  ($campaign[0]->owner == $this->user);
            $compareRedirectUrl = Yii::app()->createUrl('campaigns/default/details', array('id' => $campaign[0]->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $campaigns = Campaign::getAll();
            $this->assertEquals(3, count($campaigns));
        }

        /**
         * @depends testSuperUserCreateAction
         */
        public function testSuperUserDetailsAction()
        {
            $campaignId = self::getModelIdByModelNameAndName ('Campaign', 'New Campaign using Create');
            $this->setGetArray(array('id' => $campaignId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/details');
            $this->assertTrue(strpos($content, '<title>ZurmoCRM - Campaigns</title>') !== false);
            $this->assertTrue(strpos($content, '<div id="MarketingStickyDetailsAndRelationsBreadCrumbView" ' .
                                                'class="StickyDetailsAndRelationsBreadCrumbView ' .
                                                'BreadCrumbView">') !== false);
            $this->assertTrue(strpos($content, '<div class="breadcrumbs">') !== false);
            $this->assertTrue(strpos($content, '/marketing/default/index">Marketing</a>') !== false);
            $this->assertTrue(strpos($content, '/campaigns/default/list">Campaigns</a>') !== false);
            $this->assertTrue(strpos($content, '<span>New Campaign using Create</span></div></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="CampaignDetailsAndRelationsView" class="single-column ' .
                                                'DetailsAndRelationsView ConfigurableMetadataView' .
                                                ' MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<div class="GridView">') !== false);
            $this->assertTrue(strpos($content, '<div id="CampaignDetailsView" class="SecuredDetailsView DetailsView ' .
                                                'ModelView ConfigurableMetadataView MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<h1><span class="truncated-title"><span class="ellipsis-content">New '.
                                                'Campaign using Create - Campaign</span></span></h1>') !== false);
            $this->assertTrue(strpos($content, '<div class="view-toolbar-container clearfix"><div class="view-toolbar">' .
                                                '<ul id="ListViewDetailsActionMenu" class="nav">') !== false);
            $this->assertTrue(strpos($content, '<li class="hasDetailsFlyout parent last"><a href="javascript:void(0);' .
                                                '"><span>Details</span></a>') !== false);
            $this->assertTrue(strpos($content, '<div id="CampaignDetailsOverlayView" class="SecuredDetailsView '.
                                                'DetailsView ModelView ConfigurableMetadataView '.
                                                'MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<div class="campaign-description"><b>Send On:</b> 6/13/13 ' .
                                                '10:54 AM</BR>') !== false);
            $this->assertTrue(strpos($content, '<b>Subject:</b> New Campaign using Create Subject</div>') !== false);
            $this->assertTrue(strpos($content, '<p class="after-form-details-content">') !== false);
            $this->assertTrue(strpos($content, 'ul id="ListViewOptionsActionMenu" class="nav">') !== false);
            $this->assertTrue(strpos($content, '<li class="parent last"><a href="javascript:void(0);"><span>' .
                                                'Options</span></a>') !== false);
            $this->assertTrue(strpos($content, '<span>Edit</span></a></li>') !== false);
            $this->assertTrue(strpos($content, '<span>Delete</span></a></li>') !== false);
            $this->assertTrue(strpos($content, '<div class="ModelRelationsSecuredPortletFrameView SecuredPortlet' .
                                                'FrameView PortletFrameView MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<div class="juiportlet-columns"> ') !== false);
            $this->assertTrue(strpos($content, '<ul class="juiportlet-columns-CampaignDetailsAndRelationsViewLeft' .
                                                'BottomView juiportlet-widget-column1 juiportlet-column juiportlet-column-no-split">') !== false);
            $this->assertTrue(strpos($content, '<li class="juiportlet-widget CampaignOverallMetricsView type-campaigns" id="Campaign' .
                                                'DetailsAndRelationsViewLeftBottomView') !== false);
            $this->assertTrue(strpos($content, '<div class="juiportlet-widget-head">') !== false);
            $this->assertTrue(strpos($content, '<h3>Campaign Dashboard</h3><ul class="options-menu '.
                                                'edit-portlet-menu nav">') !== false);
            $this->assertTrue(strpos($content, '<li class="parent last"><a href="javascript:void(0);">' .
                                                '<span></span></a>') !== false);
            $this->assertTrue(strpos($content, '<li class="last"><a class="edit" id="CampaignDetailsAndRelations' .
                                                'ViewLeftBottomView') !== false);
            $this->assertTrue(strpos($content, '" href="#"><span>Configure Portlet</span></a></li>') !== false);
            $this->assertTrue(strpos($content, '<div class="juiportlet-widget-content"  >') !== false);
            $this->assertTrue(strpos($content, '<div id="CampaignOverallMetricsView" class="MarketingMetricsView ' .
                                                'ConfigurableMetadataView MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column full-width metrics-details ' .
                                                'campaign-metrics-container">') !== false);
            $this->assertTrue(strpos($content, '<h3>What is going on with this campaign?</h3>') !== false);
            $this->assertTrue(strpos($content, '<form id="marketing-metrics-group-by-configuration-form-CampaignDetails' .
                                                'AndRelationsViewLeftBottomView') !== false);
            $this->assertTrue(strpos($content, 'action="/app/test/index.php/somewhereForTheTest" ' .
                                                'method="post">') !== false);
            $this->assertTrue(strpos($content, '<div id="marketing-metrics-group-by-configuration-form-CampaignDetails' .
                                                'AndRelationsViewLeftBottomView') !== false);
            $this->assertTrue(strpos($content, '<th><label for="ytMarketingOverallMetricsForm_groupBy">Group By' .
                                                '</label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input type="hidden" value="" name="marketingMetrics' .
                                                'GroupByNotUsedName" id="marketingMetricsGroupByNotUsedName"') !== false);
            $this->assertTrue(strpos($content, '<div class="pills"><a data-value="Day" class="marketing-metrics-group-' .
                                                'by-configuration-form-CampaignDetailsAnd' .
                                                'RelationsViewLeftBottomView') !== false);
            $this->assertTrue(strpos($content, '<a data-value="Week" class="marketing-metrics-group-by-configuration-'.
                                                'form-CampaignDetailsAndRelationsViewLeftBottomView') !== false);
            $this->assertTrue(strpos($content, 'marketingMetricsGroupByLink active" href="#">Week</a>') !== false);
            $this->assertTrue(strpos($content, '<a data-value="Month" class="marketing-metrics-group-by-configuration' .
                                                '-form-CampaignDetailsAndRelationsViewLeftBottomView') !== false);
            $this->assertTrue(strpos($content, 'marketingMetricsGroupByLink" href="#">Month</a></div>') !== false);
            $this->assertTrue(strpos($content, '<div class="graph-container clearfix">') !== false);
            $this->assertTrue(strpos($content, '<div class="half marketing-graph">') !== false);
            $this->assertTrue(strpos($content, '<h3>Overall Campaign Performance</h3>') !== false);
            $this->assertTrue(strpos($content, "<div id='chartContainerCampaignDetailsAndRelationsView" .
                                                "LeftBottomView") !== false);
            $this->assertTrue(strpos($content, "OverallListPerformance' style='width: 100%; " .
                                                "height: 400px;'></div>") !== false);
            $this->assertTrue(strpos($content, '<h3>Emails in this Campaign</h3>') !== false);
            $this->assertTrue(strpos($content, "<div id='chartContainerCampaignDetailsAndRelationsView" .
                                                "LeftBottomView") !== false);
            $this->assertTrue(strpos($content, "EmailsInThisList' style='width: 100%; " .
                                                "height: 400px;'></div>") !== false);
            $this->assertTrue(strpos($content, '<li class="juiportlet-widget CampaignItemsRelatedListView type-campaigns" ' .
                                                'id="CampaignDetailsAndRelationsViewLeftBottomView') !== false);
            $this->assertTrue(strpos($content, '<div class="juiportlet-widget-head">') !== false);
            $this->assertTrue(strpos($content, '<h3>Email Recipients</h3>') !== false);
            $this->assertTrue(strpos($content, '<div class="juiportlet-widget-content" ') !== false);
            $this->assertTrue(strpos($content, '<div class="CampaignItemsRelatedListView RelatedListView ListView ' .
                                                'ModelView ConfigurableMetadataView MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<div class="campaign-items-container">') !== false);
            $this->assertTrue(strpos($content, '<div class="cgrid-view type-campaigns" id="list-viewCampaignDetailsAndRelations' .
                                                'ViewLeftBottomView') !== false);
            $this->assertTrue(strpos($content, 'Email recipients will appear here once the campaign begins ' .
                                                'sending out') !== false);
        }

        /**
         * @depends testSuperUserCreateAction
         */
        public function testSuperUserEditAction()
        {
            $campaigns = Campaign::getAll();
            $this->assertEquals(3, count($campaigns));
            $marketingListId = self::getModelIdByModelNameAndName('MarketingList', 'MarketingListName');
            $campaignId = self::getModelIdByModelNameAndName ('Campaign', 'New Campaign using Create');
            $this->setGetArray(array('id' => $campaignId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/edit');
            $this->assertTrue(strpos($content, '<title>ZurmoCRM - Campaigns</title>') !== false);
            $this->assertTrue(strpos($content, '<div id="CampaignsPageView" class="ZurmoDefaultPageView ' .
                                                'ZurmoPageView PageView">') !== false);
            $this->assertTrue(strpos($content, '<div id="MarketingBreadCrumbView" class="BreadCrumbView">' .
                                                '<div class="breadcrumbs">') !== false);
            $this->assertTrue(strpos($content, '/marketing/default/index">Marketing</a>') !== false);
            $this->assertTrue(strpos($content, '/campaigns/default/list">Campaigns</a>') !== false);
            $this->assertTrue(strpos($content, '<span>New Campaign using Create</span></div></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="CampaignEditView" class="SecuredEditView EditView DetailsView' .
                                                ' ModelView ConfigurableMetadataView MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<h1><span class="truncated-title"><span class="ellipsis-content">New ' .
                                                'Campaign using Create</span></span></h1>') !== false);
            $this->assertTrue(strpos($content, '<div class="wide form">') !== false);
            $this->assertTrue(strpos($content, '<div class="attributesContainer">') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column"><div class="panel">') !== false);
            $this->assertTrue(strpos($content, '<th><label for="Campaign_name" class="required">Name <span class=' .
                                                '"required">*</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="Campaign_name" name="Campaign[name]" ' .
                                                'type="text" maxlength="64" value="New Campaign using Create"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="Campaign_marketingList_id" class="required">Marketing ' .
                                                'List <span class="required">*</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input name="Campaign[marketingList][id]" ' .
                                                'id="Campaign_marketingList_id" value="' . $marketingListId .
                                                '" type="hidden"') !== false);
            $this->assertTrue(strpos($content, '<a id="Campaign_marketingList_SelectLink" href="#"><span ' .
                                                'class="model-select-icon"></span>') !== false);
            $this->assertTrue(strpos($content, '<th><label for="Campaign_fromName" class="required">From Name ' .
                                                '<span class="required">*</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="Campaign_fromName" name="Campaign[fromName]" ' .
                                                'type="text" maxlength="64" value="Zurmo Sales"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="Campaign_fromAddress" class="required">From Address ' .
                                                '<span class="required">*</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="Campaign_fromAddress" ' .
                                                'name="Campaign[fromAddress]" type="text" maxlength="64" ' .
                                                'value="sales@zurmo.com"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="Campaign_sendOnDateTime" class="required">Send On ' .
                                                '<span class="required">*</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><div class="has-date-select"><input ' .
                                                'id="Campaign_sendOnDateTime" name="Campaign[sendOnDateTime]" ' .
                                                'style="position:relative;z-index:10000;" type="text" ' .
                                                'value="6/13/13 10:54 AM"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="Campaign_subject" class="required">Subject ' .
                                                '<span class="required">*</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="Campaign_subject" name="Campaign[subject]" ' .
                                                'type="text" maxlength="64" value="New Campaign ' .
                                                'using Create Subject"') !== false);
            $this->assertTrue(strpos($content, '<tr><th>Enable Tracking<span id="enable-tracking-tooltip" ' .
                                                'class="tooltip" title="Check to track when recipients open ' .
                                                'an email or click any links.">?</span></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="ytCampaign_enableTracking" type="hidden" ' .
                                                'value="0" name="Campaign[enableTracking]"') !== false);
            $this->assertTrue(strpos($content, '<label class="hasCheckBox c_on"><input id="Campaign_enableTracking" ' .
                                                'name="Campaign[enableTracking]" value="1" checked="checked" ' .
                                                'type="checkbox"') !== false);
            $this->assertTrue(strpos($content, '<th>Support HTML<span id="support-rich-text-tooltip" class="tooltip" '.
                                                'title="When checked, email will be sent in both text and HTML format. ' .
                                                ' Uncheck to only send text emails">?</span></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="ytCampaign_supportsRichText" type="hidden" ' .
                                                'value="0" name="Campaign[supportsRichText]"') !== false);
            $this->assertTrue(strpos($content, '<label class="hasCheckBox"><input id="Campaign_supportsRichText" ' .
                                                'name="Campaign[supportsRichText]" value="1" type="checkbox"') !== false);
            $this->assertTrue(strpos($content, '<th></th><td colspan="1"><div class="hasDropDown"><span class=' .
                                                '"select-arrow"></span><select name="Campaign[contactEmailTemplate' .
                                                'Names]" id="Campaign_contactEmailTemplateNames_value">') !== false);
            $this->assertTrue(strpos($content, '<option value="">Select a template</option>') !== false);
            $this->assertTrue(strpos($content, 'EmailTemplate 01</option>') !== false);
            $this->assertTrue(strpos($content, 'EmailTemplate 02</option>') !== false);
            $this->assertTrue(strpos($content, 'EmailTemplate 03</option>') !== false);
            $this->assertTrue(strpos($content, '<th><label>Attachments</label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><div id="fileUploadCampaign"><div class="fileupload' .
                                                '-buttonbar clearfix">') !== false);
            $this->assertTrue(strpos($content, '<div class="addfileinput-button"><span>Y</span>') !== false);
            $this->assertTrue(strpos($content, '<strong class="add-label">Add Files</strong>') !== false);
            $this->assertTrue(strpos($content, '<input id="Campaign_files" type="file" name="Campaign_files"') !== false);
            $this->assertTrue(strpos($content, '<div class="fileupload-content"><table class="files">') !== false);
            $this->assertTrue(strpos($content, '<div class="right-column">') !== false);
            $this->assertTrue(strpos($content, '<div class="email-template-combined-content">') !== false);
            $this->assertTrue(strpos($content, '<div class="email-template-content"><div class="tabs-nav">') !== false);
            $this->assertTrue(strpos($content, '<a class="active-tab" href="#tab1">Text Content</a>') !== false);
            $this->assertTrue(strpos($content, '<a href="#tab2">Html Content</a>') !== false);
            $this->assertTrue(strpos($content, '<a id="mergetag-guide" class="simple-link" href="#">' .
                                                'MergeTag Guide</a></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="tab1" class="active-tab tab email-template-' .
                                                'textContent"><th>') !== false);
            $this->assertTrue(strpos($content, '<label for="Campaign_textContent">Text Content</label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><textarea id="Campaign_textContent" ' .
                                                'name="Campaign[textContent]" rows="6" cols="50">Text' .
                                                '</textarea></td></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="tab2" class=" tab email-template-htmlContent">' .
                                                '<label for="Campaign_htmlContent">Html Content</label>') !== false);
            $this->assertTrue(strpos($content, '<textarea id=\'Campaign_htmlContent\' name=\'Campaign[htmlContent]\'>' .
                                                'Html</textarea></div>') !== false);

            $marketingList      = MarketingListTestHelper::createMarketingListByName('MarketingListName2',
                                                                                        'MarketingList Description',
                                                                                        'second',
                                                                                        'second@zurmo.com');
            $this->setPostArray(array('Campaign' => array(
                                            'name' => 'New Campaign',
                                            'marketingList' => array('id' => $marketingList->id),
                                            'fromName' => 'Zurmo Support',
                                            'fromAddress' => 'support@zurmo.com',
                                            'sendOnDateTime' => '5/14/13 10:54 AM',
                                            'subject' => 'New Campaign Subject',
                                            'enableTracking' => '0',
                                            'supportsRichText' => '1',
                                            'textContent' => 'Text Content',
                                            'htmlContent' => 'Html Content',
                                        )));
            $redirectUrl    = $this->runControllerWithRedirectExceptionAndGetUrl('campaigns/default/edit');
            $campaign = Campaign::getByName('New Campaign');
            $this->assertEquals(1, count($campaign));
            $this->assertTrue  ($campaign[0]->id > 0);
            $this->assertEquals('support@zurmo.com', $campaign[0]->fromAddress);
            $this->assertEquals('Zurmo Support', $campaign[0]->fromName);
            $this->assertEquals('New Campaign Subject', $campaign[0]->subject);
            $this->assertEquals('0', $campaign[0]->enableTracking);
            $this->assertEquals('1', $campaign[0]->supportsRichText);
            $this->assertEquals('Text Content', $campaign[0]->textContent);
            $this->assertEquals('Html Content', $campaign[0]->htmlContent);
            $this->assertEquals(DateTimeUtil::convertTimestampToDbFormatDateTime(strtotime('5/14/13 10:54 AM')),
                                                                                        $campaign[0]->sendOnDateTime);
            $this->assertEquals($marketingList->id, $campaign[0]->marketingList->id);
            $this->assertEquals($campaign[0]->owner, $this->user);
            $compareRedirectUrl = Yii::app()->createUrl('campaigns/default/details', array('id' => $campaign[0]->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $campaign = Campaign::getAll();
            $this->assertEquals(3, count($campaign));
        }

        public function testCampaignDashboardGroupByActions()
        {
            $this->setGetArray(array('id' => $this->campaign->id));
            $this->runControllerWithNoExceptionsAndGetContent('campaigns/default/details');

            $portlets = Portlet::getAll();
            foreach ($portlets as $portlet)
            {
                if ($portlet->viewType = 'MarketingListOverallMetrics')
                {
                    $marketingListPortlet = $portlet;
                }
            }
            $marketingLists = MarketingList::getAll();

            $this->setGetArray(array(
                'portletId'         => $portlet->id,
                'uniqueLayoutId'    => 'MarketingListDetailsAndRelationsViewLeftBottomView',
                'portletParams'     => array('relationModelId'  => $marketingLists[0]->id,
                    'relationModuleId' => 'marketingLists',
                ),
            ));
            $this->setPostArray(array(
                'MarketingOverallMetricsForm' => array('groupBy' => MarketingOverallMetricsForm::GROUPING_TYPE_DAY)
            ));
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/modalConfigSave');
            $this->setPostArray(array(
                'MarketingOverallMetricsForm' => array('groupBy' => MarketingOverallMetricsForm::GROUPING_TYPE_MONTH)
            ));
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/modalConfigSave');
            $this->setPostArray(array(
                'MarketingOverallMetricsForm' => array('groupBy' => MarketingOverallMetricsForm::GROUPING_TYPE_WEEK)
            ));
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/modalConfigSave');
        }

        /**
         * @depends testSuperUserEditAction
         */
        public function testSuperUserDeleteAction()
        {
            $campaigns = Campaign::getAll();
            $this->assertEquals(3, count($campaigns));
            $this->setGetArray(array('id' => $campaigns[0]->id));
            $this->resetPostArray();
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('campaigns/default/delete');
            $compareRedirectUrl = Yii::app()->createUrl('campaigns/default/index');
            $this->assertEquals($redirectUrl, $compareRedirectUrl);
            $campaigns = Campaign::getAll();
            $this->assertEquals(2, count($campaigns));
        }

        /**
         * @depends testSuperUserDeleteAction
         */
        public function testSuperUserCreateFromRelationAction()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $campaigns      = Campaign::getAll();
            $this->assertEquals(2, count($campaigns));
            $marketingList  = MarketingListTestHelper::createMarketingListByName('my list');
            //Create a new campaign from a related marketing list.
            $this->setGetArray(array(   'relationAttributeName' => 'marketingList',
                'relationModelId'       => $marketingList->id,
                'relationModuleId'      => 'marketingLists',
                'redirectUrl'           => 'someRedirect'));
            $this->setPostArray(array('Campaign' => array(
                'name'           => 'New Campaign using Create',
                'fromName'       => 'Zurmo Sales',
                'fromAddress'    => 'sales@zurmo.com',
                'sendOnDateTime' => '6/13/13 10:54 AM',
                'subject' => 'New Campaign using Create Subject',
                'enableTracking' => '1',
                'supportsRichText' => '0',
                'textContent'    => 'Text',
                'htmlContent'    => 'Html',
            )));
            $this->runControllerWithRedirectExceptionAndGetContent('campaigns/default/createFromRelation');
            $campaigns = Campaign::getByName('New Campaign using Create');
            $this->assertEquals(1, count($campaigns));
            $this->assertTrue($campaigns[0]->id > 0);
            $this->assertTrue($campaigns[0]->owner   == $super);
            $this->assertTrue($campaigns[0]->marketingList->id == $marketingList->id);
            $campaigns = Campaign::getAll();
            $this->assertEquals(3, count($campaigns));
        }
    }
?>