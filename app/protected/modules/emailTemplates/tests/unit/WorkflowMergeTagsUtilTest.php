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
    class WorkflowMergeTagsUtilTest extends ZurmoBaseTest
    {
        protected static $emailTemplate;

        protected static $super;

        protected static $compareContent;

        protected static $content;

        public static $freeze = false;

        protected $invalidTags;

        protected $mergeTagsUtil;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            // We need to unfreeze here as we are working with custom field values
            self::$freeze                                         = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                self::$freeze                                     = true;
            }
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
            self::$super = User::getByUsername('super');
            Yii::app()->user->userModel = self::$super;

            $currencies                                         = Currency::getAll();
            $currencyValue1                                     = new CurrencyValue();
            $currencyValue1->value                              = 100;
            $currencyValue1->currency                           = $currencies[0];

            $multiDropDownCustomFieldData1                      = new CustomFieldData();
            $multiDropDownCustomFieldData1->name                = 'multiDropDown1';
            $multiDropDownCustomFieldData1->serializedData      = serialize(array('Ten', 11, 'XII'));
            $saved                                              = $multiDropDownCustomFieldData1->save();
            assert('$saved'); // Not Coding Standard

            $multiDropDownCustomFieldValue1                     = new CustomFieldValue();
            $multiDropDownCustomFieldValue1->value              = 'Ten';
            $multiDropDownCustomFieldValue2                     = new CustomFieldValue();
            $multiDropDownCustomFieldValue2->value              = 11;
            $multiDropDownCustomFieldValue3                     = new CustomFieldValue();
            $multiDropDownCustomFieldValue3->value              = 'XII';

            $tagCustomFieldData1                                = new CustomFieldData();
            $tagCustomFieldData1->name                          = 'tagCloud1';
            $tagCustomFieldData1->serializedData                = serialize(array('Apache', 'PHP'));
            $saved                                              = $tagCustomFieldData1->save();
            assert('$saved'); // Not Coding Standard

            $tagCustomFieldValue1                               = new CustomFieldValue();
            $tagCustomFieldValue1->value                        = 'PHP';
            $tagCustomFieldValue2                               = new CustomFieldValue();
            $tagCustomFieldValue2->value                        = 'Apache';

            $primaryEmail1                                      = new Email();
            $primaryEmail1->emailAddress                        = "info@zurmo.com";
            $primaryEmail1->isInvalid                           = true;
            $primaryEmail1->optOut                              = false;

            $secondaryEmail1                                    = new Email();
            $secondaryEmail1->emailAddress                      = "jake@zurmo.com";
            $secondaryEmail1->isInvalid                         = false;
            $secondaryEmail1->optOut                            = true;

            $address1                                           = new Address();
            $address1->street1                                  = "SomeStreet1";
            $address1->street2                                  = "SomeStreet2";
            $address1->city                                     = "SomeCity";
            $address1->state                                    = "SomeState";
            $address1->postalCode                               = 1111;
            $address1->country                                  = "SomeCountry";

            $likeContactState1                                  = new ContactState();
            $likeContactState1->name                            = 'Customer';
            $likeContactState1->order                           = 0;

            $users                                              = User::getAll();
            $user1                                              = new User();
            $user1->lastName                                    = 'Kevin';
            $user1->hash                                        = 'rieWoy3aijohP6chaigaokohs1oovohf';
            $user1->language                                    = 'es';
            $user1->timeZone                                    = 'America/Chicago';
            $user1->username                                    = 'dave';
            $user1->currency                                    = $currencies[0];
            $user1->manager                                     = $users[0];

            $model                                              = new EmailTemplateModelTestItem();
            $model->string                                      = 'abc';
            $model->firstName                                   = 'James';
            $model->lastName                                    = 'Jackson';
            $model->phone                                       = 1122334455;
            $model->boolean                                     = true;
            $model->date                                        = '2008-12-31';
            $model->dateTime                                    = '2008-12-31 07:48:04';
            $model->textArea                                    = 'Multiple Lines\nOf Text';
            $model->url                                         = 'http://www.zurmo.com/';
            $model->integer                                     = 999;
            $model->float                                       = 999.999;
            $model->currencyValue                               = $currencyValue1;
            $model->dropDown->value                             = "DropdownSelectedValue";
            $model->radioDropDown->value                        = "RadioDropdownSelectedValue";
            $model->primaryEmail                                = $primaryEmail1;
            $model->secondaryEmail                              = $secondaryEmail1;
            $model->primaryAddress                              = $address1;
            $model->likeContactState                            = $likeContactState1;
            $model->user                                        = $user1;
            $model->multiDropDown->data                         = $multiDropDownCustomFieldData1;
            $model->tagCloud->data                              = $tagCustomFieldData1;
            $model->multiDropDown->values->add($multiDropDownCustomFieldValue1);
            $model->multiDropDown->values->add($multiDropDownCustomFieldValue2);
            $model->multiDropDown->values->add($multiDropDownCustomFieldValue3);
            $model->tagCloud->values->add($tagCustomFieldValue1);
            $model->tagCloud->values->add($tagCustomFieldValue2);
            $saved                                              = $model->save();
            assert('$saved'); // Not Coding Standard
            self::$emailTemplate                                = $model;

            // Update all values but do not save the model.
            $multiDropDownCustomFieldData2                      = new CustomFieldData();
            $multiDropDownCustomFieldData2->name                = 'multiDropDown2';
            $multiDropDownCustomFieldData2->serializedData      = serialize(array('Thirteen', 14, 'XV'));
            $saved                                              = $multiDropDownCustomFieldData2->save();
            assert('$saved'); // Not Coding Standard

            $multiDropDownCustomFieldValue4                     = new CustomFieldValue();
            $multiDropDownCustomFieldValue4->value              = 'Thirteen';
            $multiDropDownCustomFieldValue5                     = new CustomFieldValue();
            $multiDropDownCustomFieldValue5->value              = 14;
            $multiDropDownCustomFieldValue6                     = new CustomFieldValue();
            $multiDropDownCustomFieldValue6->value              = 'XV';

            $tagCustomFieldData2                                = new CustomFieldData();
            $tagCustomFieldData2->name                          = 'tagCloud2';
            $tagCustomFieldData2->serializedData                = serialize(array('Nginx', 'Python'));
            $saved                                              = $tagCustomFieldData2->save();
            assert('$saved'); // Not Coding Standard

            $tagCustomFieldValue3                               = new CustomFieldValue();
            $tagCustomFieldValue3->value                        = 'Python';
            $tagCustomFieldValue4                               = new CustomFieldValue();
            $tagCustomFieldValue4->value                        = 'Nginx';

            self::$emailTemplate->string                        = 'def';
            self::$emailTemplate->firstName                     = 'Jane';
            self::$emailTemplate->lastName                      = 'Bond';
            self::$emailTemplate->phone                         = 66778899;
            self::$emailTemplate->boolean                       = false;
            self::$emailTemplate->date                          = '2009-12-31';
            self::$emailTemplate->dateTime                      = '2009-12-31 07:48:04';
            self::$emailTemplate->textArea                      = 'Multiple Lines\nOf\nText';
            self::$emailTemplate->url                           = 'http://www.zurmo.org/';
            self::$emailTemplate->integer                       = 888;
            self::$emailTemplate->float                         = 888.888;
            self::$emailTemplate->currencyValue->value          = 99;
            self::$emailTemplate->dropDown->value               = "DropdownSelectedVal";
            self::$emailTemplate->radioDropDown->value          = "RadioDropdownSelectedVal";
            self::$emailTemplate->primaryEmail->emailAddress    = "info@zurmo.org";
            self::$emailTemplate->primaryEmail->isInvalid       = false;
            self::$emailTemplate->primaryEmail->optOut          = true;
            self::$emailTemplate->secondaryEmail->emailAddress  = "jake@zurmo.org";
            self::$emailTemplate->secondaryEmail->isInvalid     = true;
            self::$emailTemplate->secondaryEmail->optOut        = false;
            self::$emailTemplate->primaryAddress->street1       = "SomeOtherStreet1";
            self::$emailTemplate->primaryAddress->street2       = "SomeOtherStreet2";
            self::$emailTemplate->primaryAddress->city          = "SomeOtherCity";
            self::$emailTemplate->primaryAddress->state         = "SomeOtherState";
            self::$emailTemplate->primaryAddress->postalCode    = 2222;
            self::$emailTemplate->primaryAddress->country       = "SomeOtherCountry";
            self::$emailTemplate->likeContactState->name        = 'New';
            self::$emailTemplate->likeContactState->order       = 1;
            self::$emailTemplate->user->lastName                = 'Dean';
            self::$emailTemplate->user->hash                    = 'teo8eghaipaC5ahngahleiyaebofu6oo';
            self::$emailTemplate->user->language                = 'en';
            self::$emailTemplate->user->timeZone                = 'America/Denver';
            self::$emailTemplate->user->username                = 'deandavis';

            self::$emailTemplate->multiDropDown->data           = $multiDropDownCustomFieldData2;
            self::$emailTemplate->multiDropDown->values->remove($multiDropDownCustomFieldValue1);
            self::$emailTemplate->multiDropDown->values->remove($multiDropDownCustomFieldValue2);
            self::$emailTemplate->multiDropDown->values->remove($multiDropDownCustomFieldValue3);
            self::$emailTemplate->multiDropDown->values->add($multiDropDownCustomFieldValue4);
            self::$emailTemplate->multiDropDown->values->add($multiDropDownCustomFieldValue5);
            self::$emailTemplate->multiDropDown->values->add($multiDropDownCustomFieldValue6);

            self::$emailTemplate->tagCloud->data                = $tagCustomFieldData2;
            self::$emailTemplate->tagCloud->values->remove($tagCustomFieldValue1);
            self::$emailTemplate->tagCloud->values->remove($tagCustomFieldValue2);
            self::$emailTemplate->tagCloud->values->add($tagCustomFieldValue3);
            self::$emailTemplate->tagCloud->values->add($tagCustomFieldValue4);

            self::$content                                      = 'Current: [[STRING]] [[FIRST^NAME]] [[LAST^NAME]] ' .
                '[[PHONE]] Old: [[WAS%STRING]] [[WAS%FIRST^NAME]] ' .
                '[[WAS%LAST^NAME]] [[WAS%PHONE]]';
            self::$compareContent                               = 'Current: def Jane Bond 66778899 Old: abc James ' .
                'Jackson 1122334455';
        }

        public static function tearDownAfterClass()
        {
            if (self::$freeze)
            {
                RedBeanDatabase::freeze();
            }
            parent::tearDownAfterClass();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel                     = self::$super;
            $this->mergeTagsUtil                            = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW,
                null, self::$content);
            $this->invalidTags                              = array();
        }

        public function testCanInstantiateContactMergeTags()
        {
            $this->assertTrue($this->mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($this->mergeTagsUtil instanceof WorkflowMergeTagsUtil);
        }

        /**
         * @depends testCanInstantiateContactMergeTags
         */
        public function testMergeFieldsArePopulatedCorrectlyWithCustomLanguage()
        {
            $resolvedContent = $this->mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags, 'fr');
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, self::$content);
            $this->assertEquals($resolvedContent, self::$compareContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testCanInstantiateContactMergeTags
         */
        public function testMergeFieldsArePopulatedCorrectlyWithNoLanguage()
        {
            $resolvedContent = $this->mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags, null);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, self::$content);
            $this->assertEquals($resolvedContent, self::$compareContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testCanInstantiateContactMergeTags
         */
        public function testMergeFieldsArePopulatedCorrectlyWithDefaultLanguage()
        {
            $resolvedContent = $this->mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, self::$content);
            $this->assertEquals($resolvedContent, self::$compareContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testMergeFieldsArePopulatedCorrectlyWithDefaultLanguage
         */
        public function testSucceedsWhenDataHasNoMergeTags()
        {
            $content = "This is some text that doesn't contain any merge tags";
            $mergeTagsUtil = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags, null);
            $this->assertTrue($resolvedContent !== false);
            $this->assertEquals($resolvedContent, $content);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testSucceedsWhenDataHasNoMergeTags
         */
        public function testFailsOnInvalidMergeTags()
        {
            $content = "This is some text that has [[INVALID]] [[IN^VALID]] [[PHONE__NO]] merge tags";
            $mergeTagsUtil = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags, null);
            $this->assertFalse($resolvedContent);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertNotEmpty($this->invalidTags);
            $this->assertEquals(3, count($this->invalidTags));
            $this->assertTrue($this->invalidTags[0] == 'INVALID');
            $this->assertTrue($this->invalidTags[1] == 'IN^VALID');
            $this->assertTrue($this->invalidTags[2] == 'PHONE__NO');
        }

        /**
         * @depends testFailsOnInvalidMergeTags
         */
        public function testFailsOnFirstInvalidMergeTag()
        {
            $content = "This is some text that has [[INVALID]] [[IN^VALID]] [[PHONE__NO]] merge tags";
            $mergeTagsUtil = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags, null, true);
            $this->assertFalse($resolvedContent);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEmpty($this->invalidTags);
        }

        // TODO: @Shoaibi/@Jason: Low: All of the tests below would have to be duplicated for different languages.

        /**
         * @depends testFailsOnInvalidMergeTags
         */
        public function testStringMergeTag()
        {
            $content                = 'string: Current: [[STRING]] Old: [[WAS%STRING]]';
            $compareContent         = 'string: Current: def Old: abc';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent        = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testStringMergeTag
         */
        public function testFirstNameMergeTag()
        {
            $content                = 'firstName: Current: [[FIRST^NAME]] Old: [[WAS%FIRST^NAME]]';
            $compareContent         = 'firstName: Current: Jane Old: James';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent        = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testFirstNameMergeTag
         */
        public function testLastNameMergeTag()
        {
            $content                = 'lastName: Current: [[LAST^NAME]] Old: [[WAS%LAST^NAME]]';
            $compareContent         = 'lastName: Current: Bond Old: Jackson';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent        = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testLastNameMergeTag
         */
        public function testPhoneMergeTag()
        {
            $content                = 'phone: Current: [[PHONE]] Old: [[WAS%PHONE]]';
            $compareContent         = 'phone: Current: 66778899 Old: 1122334455';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent        = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testPhoneMergeTag
         */
        public function testBooleanMergeTag()
        {
            $content                = 'boolean: Current: [[BOOLEAN]] Old: [[WAS%BOOLEAN]]';
            $compareContent         = 'boolean: Current: 0 Old: 1';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent        = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testBooleanMergeTag
         */
        public function testDateMergeTag()
        {
            $content                = 'date: Current: [[DATE]] Old: [[WAS%DATE]]';
            $compareContent         = 'date: Current: 2009-12-31 Old: 2008-12-31';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent        = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testDateMergeTag
         */
        public function testDateTimeMergeTag()
        {
            $content                = 'dateTime: Current: [[DATE^TIME]] Old: [[WAS%DATE^TIME]]';
            $compareContent         = 'dateTime: Current: 2009-12-31 07:48:04 Old: 2008-12-31 07:48:04';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent        = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testDateTimeMergeTag
         */
        public function testTextAreaMergeTag()
        {
            $content                = 'textArea: Current: [[TEXT^AREA]] Old: [[WAS%TEXT^AREA]]';
            $compareContent         = 'textArea: Current: Multiple Lines\nOf\nText Old: Multiple Lines\nOf Text';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent        = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testTextAreaMergeTag
         */
        public function testUrlMergeTag()
        {
            $content                = 'url: Current: [[URL]] Old: [[WAS%URL]]';
            $compareContent         = 'url: Current: http://www.zurmo.org/ Old: http://www.zurmo.com/';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent        = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testUrlMergeTag
         */
        public function testIntegerMergeTag()
        {
            $content                = 'integer: Current: [[INTEGER]] Old: [[WAS%INTEGER]]';
            $compareContent         = 'integer: Current: 888 Old: 999';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent        = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testIntegerMergeTag
         */
        public function testFloatMergeTag()
        {
            $content                = 'float: Current: [[FLOAT]] Old: [[WAS%FLOAT]]';
            $compareContent         = 'float: Current: 888.888 Old: 999.999';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent        = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testFloatMergeTag
         */
        public function testCurrencyValueMergeTag()
        {
            $content                    = 'currencyValue: Current: [[CURRENCY^VALUE]] [[CURRENCY^VALUE__VALUE]] ' .
                                            '[[CURRENCY^VALUE__CURRENCY__CODE]] [[CURRENCY^VALUE__CURRENCY__ACTIVE]] ' .
                                            'Old: [[WAS%CURRENCY^VALUE__VALUE]]';
            $compareContent             = 'currencyValue: Current: 99 99 USD 1 Old: 100';
            $mergeTagsUtil              = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent            = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testCurrencyValueMergeTag
         */
        public function testDropDownMergeTag()
        {
            $content                    = 'dropDown: Current: [[DROP^DOWN]] [[DROP^DOWN__VALUE]] ' .
                                            'Old: [[WAS%DROP^DOWN__VALUE]]';
            $compareContent             = 'dropDown: Current: DropdownSelectedVal DropdownSelectedVal '.
                                            'Old: DropdownSelectedValue';
            $mergeTagsUtil              = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent            = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testDropDownMergeTag
         */
        public function testRadioDropDownMergeTag()
        {
            $content                        = 'radioDropDown: Current: [[RADIO^DROP^DOWN]] [[RADIO^DROP^DOWN__VALUE]] ' .
                                                'Old: [[WAS%RADIO^DROP^DOWN__VALUE]]';
            $compareContent                 = 'radioDropDown: Current: RadioDropdownSelectedVal RadioDropdownSelectedVal ' .
                                                'Old: RadioDropdownSelectedValue';
            $mergeTagsUtil                  = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent                = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testRadioDropDownMergeTag
         */
        public function testMultiDropDownMergeTag()
        {
            // TODO: @Shoaibi/@Jason: Low: We can't do WAS% with multiDropDown at all.
            //  WAS%MULTI^DROP^DOWN
            // WAS%MULTI^DROP^DOWN__VALUES
            // WAS%MULTI^DROP^DOWN__DATA
            // All of these are ending on a relation tag. How do we use WAS% with multiDropDown? which data can we change which isn't HAS_MANY and direct property.
            $content                            = 'multiDropDown: Current: [[MULTI^DROP^DOWN]] [[MULTI^DROP^DOWN__VALUES]] ' .
                                                    'Old:';
            $compareContent                     = 'multiDropDown: Current: Thirteen, 14, XV 3 records.' .
                                                    ' Old:';
            $mergeTagsUtil                      = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent                    = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testMultiDropDownMergeTag
         */
        public function testTagCloudMergeTag()
        {
            // TODO: @Shoaibi/Jason: Low: How do we do "WAS%" with this? Same explanation as above.
            $content                            = 'tagCloud: Current: [[TAG^CLOUD]] [[TAG^CLOUD__VALUES]] ' .
                                                    'Old:';
            $compareContent                     = 'tagCloud: Current: Python, Nginx 2 records. '.
                                                    'Old:';
            $mergeTagsUtil                      = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent                    = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testTagCloudMergeTag
         */
        public function testPrimaryEmailMergeTag()
        {
            $content                        = 'primaryEmail: Current: [[PRIMARY^EMAIL]] [[PRIMARY^EMAIL__EMAIL^ADDRESS]] ' .
                                                '[[PRIMARY^EMAIL__IS^INVALID]] [[PRIMARY^EMAIL__OPT^OUT]] ' .
                                                'Old: [[WAS%PRIMARY^EMAIL__EMAIL^ADDRESS]] ' .
                                                '[[WAS%PRIMARY^EMAIL__IS^INVALID]] [[WAS%PRIMARY^EMAIL__OPT^OUT]]';
            $compareContent                 = 'primaryEmail: Current: info@zurmo.org info@zurmo.org 0 1 ' .
                                                'Old: info@zurmo.com 1 0';
            $mergeTagsUtil                  = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent                = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testPrimaryEmailMergeTag
         */
        public function testSecondaryEmailMergeTag()
        {
            $content                        = 'secondaryEmail: Current: [[SECONDARY^EMAIL]] [[SECONDARY^EMAIL__EMAIL^ADDRESS]] ' .
                                                '[[SECONDARY^EMAIL__IS^INVALID]] [[SECONDARY^EMAIL__OPT^OUT]] ' .
                                                'Old: [[WAS%SECONDARY^EMAIL__EMAIL^ADDRESS]] ' .
                                                '[[WAS%SECONDARY^EMAIL__IS^INVALID]] [[WAS%SECONDARY^EMAIL__OPT^OUT]]';
            $compareContent                 = 'secondaryEmail: Current: jake@zurmo.org jake@zurmo.org 1 0 '.
                                                'Old: jake@zurmo.com 0 1';
            $mergeTagsUtil                  = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent                = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testSecondaryEmailMergeTag
         */
        public function testAddressMergeTag()
        {
            $content                        = 'address: Current: [[PRIMARY^ADDRESS]] [[PRIMARY^ADDRESS__STREET1]] ' .
                                                '[[PRIMARY^ADDRESS__STREET2]] [[PRIMARY^ADDRESS__CITY]] ' .
                                                '[[PRIMARY^ADDRESS__STATE]] [[PRIMARY^ADDRESS__POSTAL^CODE]] ' .
                                                '[[PRIMARY^ADDRESS__COUNTRY]] ' .
                                                'Old: [[WAS%PRIMARY^ADDRESS__STREET1]] ' .
                                                '[[WAS%PRIMARY^ADDRESS__STREET2]] [[WAS%PRIMARY^ADDRESS__CITY]] ' .
                                                '[[WAS%PRIMARY^ADDRESS__STATE]] [[WAS%PRIMARY^ADDRESS__POSTAL^CODE]] ' .
                                                '[[WAS%PRIMARY^ADDRESS__COUNTRY]]';
            $compareContent                 = 'address: Current: SomeOtherStreet1, SomeOtherStreet2, SomeOtherCity,'. // Not Coding Standard
                                                ' SomeOtherState, 2222, SomeOtherCountry SomeOtherStreet1 ' .
                                                'SomeOtherStreet2 SomeOtherCity SomeOtherState 2222 SomeOtherCountry' .
                                                ' Old: SomeStreet1 SomeStreet2 SomeCity SomeState 1111 SomeCountry';
            $mergeTagsUtil                  = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent                = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testAddressMergeTag
         */
        public function testLikeContactStateMergeTag()
        {
            // ContactState does not support originalAttributeValues
            $content                        = 'likeContactState: Current: [[LIKE^CONTACT^STATE]] [[LIKE^CONTACT^STATE__NAME]] ' .
                                                '[[LIKE^CONTACT^STATE__ORDER]][[LIKE^CONTACT^STATE__SERIALIZED^LABELS]] ' .
                                                'Old: [[WAS%LIKE^CONTACT^STATE]] [[WAS%LIKE^CONTACT^STATE__NAME]] ' .
                                                '[[WAS%LIKE^CONTACT^STATE__ORDER]][[WAS%LIKE^CONTACT^STATE__SERIALIZED^LABELS]]';
            $mergeTagsUtil                  = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent                = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertFalse($resolvedContent);
            $this->assertNotEmpty($this->invalidTags);
            $this->assertCount(4, $this->invalidTags);
            $this->assertEquals('WAS%LIKE^CONTACT^STATE', $this->invalidTags[0]);
            $this->assertEquals('WAS%LIKE^CONTACT^STATE__NAME', $this->invalidTags[1]);
            $this->assertEquals('WAS%LIKE^CONTACT^STATE__ORDER', $this->invalidTags[2]);
            $this->assertEquals('WAS%LIKE^CONTACT^STATE__SERIALIZED^LABELS', $this->invalidTags[3]);
        }

        /**
         * @depends testLikeContactStateMergeTag
         */
        public function testUserMergeTag()
        {
            // Currency does not support originalAttributeValues
            $content                        = 'user: Current: [[USER__HASH]] [[USER__LAST^NAME]] [[USER__LANGUAGE]] [[USER__TIME^ZONE]]' .
                                                ' [[USER__USERNAME]] [[USER__CURRENCY]] [[USER__CURRENCY__CODE]] ' .
                                                'Old: [[WAS%USER__HASH]] [[WAS%USER__LAST^NAME]] [[WAS%USER__LANGUAGE]]' .
                                                ' [[WAS%USER__TIME^ZONE]] [[WAS%USER__USERNAME]]';
            $compareContent                 = 'user: Current: teo8eghaipaC5ahngahleiyaebofu6oo Dean en America/Denver'.
                                                ' deandavis USD USD Old: rieWoy3aijohP6chaigaokohs1oovohf Kevin es '.
                                                'America/Chicago dave';
            $mergeTagsUtil                  = MergeTagsUtilFactory::make(EmailTemplate::TYPE_WORKFLOW, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof WorkflowMergeTagsUtil);
            $resolvedContent                = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }
    }
?>