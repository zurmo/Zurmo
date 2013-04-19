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
    class ContactMergeTagsUtilTest extends ZurmoBaseTest
    {
        public static $freeze = false;

        protected static $emailTemplate;

        protected static $super;

        protected static $compareContent;

        protected static $content;

        protected $invalidTags;

        protected $mergeTagsUtil;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            self::$freeze = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                self::$freeze = true;
            }
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
            self::$super = User::getByUsername('super');
            Yii::app()->user->userModel = self::$super;

            $currencies                                     = Currency::getAll();
            $currencyValue                                  = new CurrencyValue();
            $currencyValue->value                           = 100;
            $currencyValue->currency                        = $currencies[0];

            $multiDropDownCustomFieldData                   = new CustomFieldData();
            $multiDropDownCustomFieldData->name             = 'multiDropDown';
            $multiDropDownCustomFieldData->serializedData   = serialize(array('Ten', 11, 'XII'));
            $saved                                          = $multiDropDownCustomFieldData->save();
            assert('$saved'); // Not Coding Standard

            $multiDropDownCustomFieldValue1                 = new CustomFieldValue();
            $multiDropDownCustomFieldValue1->value          = 'Ten';
            $multiDropDownCustomFieldValue2                 = new CustomFieldValue();
            $multiDropDownCustomFieldValue2->value          = 11;
            $multiDropDownCustomFieldValue3                 = new CustomFieldValue();
            $multiDropDownCustomFieldValue3->value          = 'XII';

            $tagCustomFieldData                             = new CustomFieldData();
            $tagCustomFieldData->name                       = 'tagCloud';
            $tagCustomFieldData->serializedData             = serialize(array('Apache', 'PHP'));
            $saved                                          = $tagCustomFieldData->save();
            assert('$saved'); // Not Coding Standard

            $tagCustomFieldValue1                           = new CustomFieldValue();
            $tagCustomFieldValue1->value                    = 'PHP';
            $tagCustomFieldValue2                           = new CustomFieldValue();
            $tagCustomFieldValue2->value                    = 'Apache';

            $primaryEmail                                   = new Email();
            $primaryEmail->emailAddress                     = "info@zurmo.com";
            $primaryEmail->isInvalid                        = true;
            $primaryEmail->optOut                           = false;

            $secondaryEmail                                 = new Email();
            $secondaryEmail->emailAddress                   = "jake@zurmo.com";
            $secondaryEmail->isInvalid                      = false;
            $secondaryEmail->optOut                         = true;

            $address                                        = new Address();
            $address->street1                               = "SomeStreet1";
            $address->street2                               = "SomeStreet2";
            $address->city                                  = "SomeCity";
            $address->state                                 = "SomeState";
            $address->postalCode                            = 1111;
            $address->country                               = "SomeCountry";

            $likeContactState                               = new ContactState();
            $likeContactState->name                         = 'Customer';
            $likeContactState->order                        = 0;

            $users                                          = User::getAll();
            $user                                           = new User();
            $user->lastName                                 = 'Kevin';
            $user->hash                                     = 'rieWoy3aijohP6chaigaokohs1oovohf';
            $user->language                                 = 'es';
            $user->timeZone                                 = 'America/Chicago';
            $user->username                                 = 'kevinjones';
            $user->currency                                 = $currencies[0];
            $user->manager                                  = $users[0];

            $model                                          = new EmailTemplateModelTestItem();
            $model->string                                  = 'abc';
            $model->firstName                               = 'James';
            $model->lastName                                = 'Jackson';
            $model->phone                                   = 1122334455;
            $model->boolean                                 = true;
            $model->date                                    = '2008-12-31';
            $model->dateTime                                = '2008-12-31 07:48:04';
            $model->textArea                                = 'Multiple Lines\nOf Text';
            $model->url                                     = 'http://www.zurmo.com/';
            $model->integer                                 = 999;
            $model->float                                   = 999.999;
            $model->currencyValue                           = $currencyValue;
            $model->dropDown->value                         = "DropdownSelectedValue";
            $model->radioDropDown->value                    = "RadioDropdownSelectedValue";
            $model->primaryEmail                            = $primaryEmail;
            $model->secondaryEmail                          = $secondaryEmail;
            $model->primaryAddress                          = $address;
            $model->likeContactState                        = $likeContactState;
            $model->user                                    = $user;
            $model->multiDropDown->data                     = $multiDropDownCustomFieldData;
            $model->tagCloud->data                          = $tagCustomFieldData;
            $model->multiDropDown->values->add($multiDropDownCustomFieldValue1);
            $model->multiDropDown->values->add($multiDropDownCustomFieldValue2);
            $model->multiDropDown->values->add($multiDropDownCustomFieldValue3);
            $model->tagCloud->values->add($tagCustomFieldValue1);
            $model->tagCloud->values->add($tagCustomFieldValue2);
            $saved                                          = $model->save();
            assert('$saved'); // Not Coding Standard
            self::$emailTemplate                            = $model;
            self::$content                                  = '[[STRING]] [[FIRST^NAME]] [[LAST^NAME]] [[PHONE]]';
            self::$compareContent                           = 'abc James Jackson 1122334455';
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel     = self::$super;
            $this->mergeTagsUtil            = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, self::$content);
            $this->invalidTags              = array();
        }

        public static function tearDownAfterClass()
        {
            if (self::$freeze)
            {
                RedBeanDatabase::freeze();
            }
            parent::tearDownAfterClass();
        }

        public function testCanInstantiateContactMergeTags()
        {
            $this->assertTrue($this->mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($this->mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $mergeTagsUtil = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $mergeTagsUtil = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $mergeTagsUtil = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                = 'string: [[STRING]]';
            $compareContent         = 'string: abc';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                = 'firstName: [[FIRST^NAME]]';
            $compareContent         = 'firstName: James';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                = 'lastName: [[LAST^NAME]]';
            $compareContent         = 'lastName: Jackson';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                = 'phone: [[PHONE]]';
            $compareContent         = 'phone: 1122334455';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                = 'boolean: [[BOOLEAN]]';
            $compareContent         = 'boolean: 1';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                = 'date: [[DATE]]';
            $compareContent         = 'date: 2008-12-31';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                = 'dateTime: [[DATE^TIME]]';
            $compareContent         = 'dateTime: 2008-12-31 07:48:04';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                = 'textArea: [[TEXT^AREA]]';
            $compareContent         = 'textArea: Multiple Lines\nOf Text';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                = 'url: [[URL]]';
            $compareContent         = 'url: http://www.zurmo.com/';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                = 'integer: [[INTEGER]]';
            $compareContent         = 'integer: 999';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                = 'float: [[FLOAT]]';
            $compareContent         = 'float: 999.999';
            $mergeTagsUtil          = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                    = 'currencyValue: [[CURRENCY^VALUE]] [[CURRENCY^VALUE__VALUE]] ' .
                                            '[[CURRENCY^VALUE__CURRENCY__CODE]] [[CURRENCY^VALUE__CURRENCY__ACTIVE]]';
            $compareContent             = 'currencyValue: 100 100 USD 1';
            $mergeTagsUtil              = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                    = 'dropDown: [[DROP^DOWN]] [[DROP^DOWN__VALUE]]';
            $compareContent             = 'dropDown: DropdownSelectedValue DropdownSelectedValue';
            $mergeTagsUtil              = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                        = 'radioDropDown: [[RADIO^DROP^DOWN]] [[RADIO^DROP^DOWN__VALUE]]';
            $compareContent                 = 'radioDropDown: RadioDropdownSelectedValue RadioDropdownSelectedValue';
            $mergeTagsUtil                  = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                            = 'multiDropDown: [[MULTI^DROP^DOWN]] [[MULTI^DROP^DOWN__VALUES]]';
            $compareContent                     = 'multiDropDown: Ten, 11, XII 3 records.';
            $mergeTagsUtil                      = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                            = 'tagCloud: [[TAG^CLOUD]] [[TAG^CLOUD__VALUES]]';
            $compareContent                     = 'tagCloud: PHP, Apache 2 records.';
            $mergeTagsUtil                      = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                        = 'primaryEmail: [[PRIMARY^EMAIL]] [[PRIMARY^EMAIL__EMAIL^ADDRESS]] ' .
                                                '[[PRIMARY^EMAIL__IS^INVALID]] [[PRIMARY^EMAIL__OPT^OUT]]';
            $compareContent                 = 'primaryEmail: info@zurmo.com info@zurmo.com 1 0';
            $mergeTagsUtil                  = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                        = 'secondaryEmail: [[SECONDARY^EMAIL]] [[SECONDARY^EMAIL__EMAIL^ADDRESS]] ' .
                                                '[[SECONDARY^EMAIL__IS^INVALID]] [[SECONDARY^EMAIL__OPT^OUT]]';
            $compareContent                 = 'secondaryEmail: jake@zurmo.com jake@zurmo.com 0 1';
            $mergeTagsUtil                  = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                        = 'address: [[PRIMARY^ADDRESS]] [[PRIMARY^ADDRESS__STREET1]] ' .
                                                '[[PRIMARY^ADDRESS__STREET2]] [[PRIMARY^ADDRESS__CITY]] ' .
                                                '[[PRIMARY^ADDRESS__STATE]] [[PRIMARY^ADDRESS__POSTAL^CODE]] ' .
                                                '[[PRIMARY^ADDRESS__COUNTRY]]';
            $compareContent                 = 'address: SomeStreet1, SomeStreet2, SomeCity, SomeState, 1111, SomeCountry' .
                                            ' SomeStreet1 SomeStreet2 SomeCity SomeState 1111 SomeCountry';
            $mergeTagsUtil                  = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
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
            $content                        = 'likeContactState: [[LIKE^CONTACT^STATE]] [[LIKE^CONTACT^STATE__NAME]] ' .
                                                '[[LIKE^CONTACT^STATE__ORDER]][[LIKE^CONTACT^STATE__SERIALIZED^LABELS]]';
            $compareContent                 = 'likeContactState: Customer Customer 0';
            $mergeTagsUtil                  = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
            $resolvedContent                = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testLikeContactStateMergeTag
         */
        public function testUserMergeTag()
        {
            $content                        = 'user: [[USER__HASH]] [[USER__LAST^NAME]] [[USER__LANGUAGE]] [[USER__TIME^ZONE]]' .
                                                ' [[USER__USERNAME]] [[USER__CURRENCY]] [[USER__CURRENCY__CODE]]';
            $compareContent                 = 'user: rieWoy3aijohP6chaigaokohs1oovohf Kevin es America/Chicago kevinjones USD USD';
            $mergeTagsUtil                  = MergeTagsUtilFactory::make(EmailTemplate::TYPE_CONTACT, null, $content);
            $this->assertTrue($mergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($mergeTagsUtil instanceof ContactMergeTagsUtil);
            $resolvedContent                = $mergeTagsUtil->resolveMergeTags(self::$emailTemplate, $this->invalidTags);
            $this->assertTrue($resolvedContent !== false);
            $this->assertNotEquals($resolvedContent, $content);
            $this->assertEquals($compareContent, $resolvedContent);
            $this->assertEmpty($this->invalidTags);
        }
    }
?>