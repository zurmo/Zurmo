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
    class AutoresponderItemActivityUtilTest extends ZurmoBaseTest
    {
        protected $user;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user                 = User::getByUsername('super');
            Yii::app()->user->userModel = $this->user;
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testResolveQueryStringFromUrlAndCreateOrUpdateActivityThrowsExceptionWhenNoIdInQueryString()
        {
            AutoresponderItemActivityUtil::resolveQueryStringFromUrlAndCreateOrUpdateActivity();
        }

        /**
         * @depends testResolveQueryStringFromUrlAndCreateOrUpdateActivityThrowsExceptionWhenNoIdInQueryString
         */
        public function testResolveQueryStringFromUrlAndCreateOrUpdateActivityForEmailOpenTrackingWithoutExceptions()
        {
            // setup pre-req data
            $contact            = ContactTestHelper::createContactByNameForOwner('contact 01', $this->user);
            $marketingList      = MarketingListTestHelper::createMarketingListByName('marketingList 01',
                                                                                        'description 01',
                                                                                        'fromName 01',
                                                                                        'fromAddress01@domain.com');
            $autoresponder      = AutoresponderTestHelper::createAutoresponder('subject 01',
                                                                                'textContent 01',
                                                                                'htmlContent 01',
                                                                                10,
                                                                                Autoresponder::OPERATION_SUBSCRIBE,
                                                                                1,
                                                                                $marketingList);
            $processed          = 0;
            $processDateTime    = DateTimeUtil::convertTimestampToDbFormatDateTime(time()-100);
            $autoresponderItem  = AutoresponderItemTestHelper::createAutoresponderItem($processed,
                                                                                        $processDateTime,
                                                                                        $autoresponder,
                                                                                        $contact);
            $modelId            = $autoresponderItem->id;
            $modelType          = get_class($autoresponderItem);
            $personId           = $contact->getClassId('Person');
            $this->assertNotNull($personId);

            // get the modelClassName to use for activity object tests
            $className                                  = 'AutoresponderItemActivityUtil';
            $resolveModelClassNameByModelTypeFunction   = static::getProtectedMethod($className,
                                                                                    'resolveModelClassNameByModelType');
            $modelClassName                             = $resolveModelClassNameByModelTypeFunction->invokeArgs(null,
                                                                                                    array($modelType));
            $type                                       = $modelClassName::TYPE_OPEN;
            $existingActivities                         = $modelClassName::getByType($type);
            $this->assertCount(0, $existingActivities);

            // get base query string
            $resolveBaseQueryStringArrayFunction        = static::getProtectedMethod($className,
                                                                                        'resolveBaseQueryStringArray');
            $queryStringArray                           = $resolveBaseQueryStringArrayFunction->invokeArgs(null, array(
                                                                                                            $modelId,
                                                                                                            $modelType,
                                                                                                            $personId));
            $this->assertNotEmpty($queryStringArray);
            $this->assertCount(3, $queryStringArray);
            $this->assertArrayHasKey('modelId', $queryStringArray);
            $this->assertArrayHasKey('modelType', $queryStringArray);
            $this->assertArrayHasKey('personId', $queryStringArray);

            // get hash for query string and ensure its what we expect it to be.
            $resolveHashForQueryStringArrayFunction = static::getProtectedMethod($className,
                                                                                    'resolveHashForQueryStringArray');
            $queryStringArrayHash                   = $resolveHashForQueryStringArrayFunction->invokeArgs(null,
                                                                                            array($queryStringArray));
            $queryStringArrayDecoded                = $className::resolveQueryStringArrayForHash($queryStringArrayHash);
            $this->assertNotEmpty($queryStringArrayDecoded);
            $this->assertCount(5,                               $queryStringArrayDecoded);
            $this->assertArrayHasKey('modelId',                 $queryStringArrayDecoded);
            $this->assertArrayHasKey('modelType',               $queryStringArrayDecoded);
            $this->assertArrayHasKey('personId',                $queryStringArrayDecoded);
            $this->assertArrayHasKey('url',                     $queryStringArrayDecoded);
            $this->assertArrayHasKey('type',                    $queryStringArrayDecoded);
            $this->assertEquals($queryStringArray['modelId'],   $queryStringArrayDecoded['modelId']);
            $this->assertEquals($queryStringArray['modelType'], $queryStringArrayDecoded['modelType']);
            $this->assertEquals($queryStringArray['personId'],  $queryStringArrayDecoded['personId']);
            $this->assertNull($queryStringArrayDecoded['url']);
            $this->assertNull($queryStringArrayDecoded['type']);

            // do the magic, confirm magic worked by checking return value.
            $_GET['id']                         = $queryStringArrayHash;
            $result                             = $className::resolveQueryStringFromUrlAndCreateOrUpdateActivity();
            $this->assertNotEmpty($result);
            $this->assertCount(2, $result);
            $this->assertArrayHasKey('redirect',        $result);
            $this->assertArrayHasKey('imagePath',       $result);
            $this->assertEquals(false,                  $result['redirect']);
            $this->assertEquals(Yii::app()->themeManager->basePath . $className::IMAGE_PATH, $result['imagePath']);

            // check activity object count to confirm we got a new activity
            $existingActivities                 = $modelClassName::getByType($type);
            $this->assertCount(1,   $existingActivities);
            // try fetching an object matching criteria of the one we just inserted.
            $activity                           = $modelClassName::getByTypeAndModelIdAndPersonIdAndUrl($type,
                                                                                                        $modelId,
                                                                                                        $personId);
            $this->assertNotEmpty($activity);
            $this->assertCount(1,   $activity);
            $this->assertEquals(1,  $activity[0]->quantity);

            // do the magic again, this time it should update quantity
            $result                             = $className::resolveQueryStringFromUrlAndCreateOrUpdateActivity();
            $this->assertNotEmpty($result);
            $this->assertCount(2, $result);
            $this->assertArrayHasKey('redirect',        $result);
            $this->assertArrayHasKey('imagePath',       $result);
            $this->assertEquals(false,                  $result['redirect']);
            $this->assertEquals(Yii::app()->themeManager->basePath . $className::IMAGE_PATH, $result['imagePath']);

            // check activity object count to confirm we got a new activity
            $existingActivities                 = $modelClassName::getByType($type);
            $this->assertCount(1, $existingActivities);
            // try fetching an object matching criteria of the one we just inserted.
            $type                               = $modelClassName::TYPE_OPEN;
            $activity                           = $modelClassName::getByTypeAndModelIdAndPersonIdAndUrl($type,
                                                                                                        $modelId,
                                                                                                        $personId);
            $this->assertNotEmpty($activity);
            $this->assertCount(1,   $activity);
            $this->assertEquals(2,  $activity[0]->quantity);
        }

        /**
         * @depends testResolveQueryStringFromUrlAndCreateOrUpdateActivityForEmailOpenTrackingWithoutExceptions
         * @expectedException NotFoundException
         */
        public function testResolveQueryStringFromUrlAndCreateOrUpdateActivityThrowsExceptionForInvalidAutoresponderItemIdForEmailOpenTracking()
        {
            // setup pre-req data
            $contact            = ContactTestHelper::createContactByNameForOwner('contact 02', $this->user);
            $marketingList      = MarketingListTestHelper::createMarketingListByName('marketingList 02',
                                                                                        'description 02',
                                                                                        'fromName 02',
                                                                                        'fromAddress02@domain.com');
            $autoresponder      = AutoresponderTestHelper::createAutoresponder('subject 02',
                                                                                'textContent 02',
                                                                                'htmlContent 02',
                                                                                10,
                                                                                Autoresponder::OPERATION_SUBSCRIBE,
                                                                                1,
                                                                                $marketingList);
            $processed          = 0;
            $processDateTime    = DateTimeUtil::convertTimestampToDbFormatDateTime(time()-100);
            $autoresponderItem  = AutoresponderItemTestHelper::createAutoresponderItem($processed,
                                                                                        $processDateTime,
                                                                                        $autoresponder,
                                                                                        $contact);
            $modelId            = $autoresponderItem->id;
            $modelId            += 200;
            $modelType          = get_class($autoresponderItem);
            $personId           = $contact->getClassId('Person');
            $this->assertNotNull($personId);

            // get the modelClassName to use for activity object tests
            $className                                  = 'AutoresponderItemActivityUtil';
            $resolveModelClassNameByModelTypeFunction   = static::getProtectedMethod($className,
                                                                                    'resolveModelClassNameByModelType');
            $modelClassName                             = $resolveModelClassNameByModelTypeFunction->invokeArgs(null,
                                                                                                    array($modelType));
            $type                                       = $modelClassName::TYPE_OPEN;
            $existingActivities                         = $modelClassName::getByType($type);
            $this->assertCount(1, $existingActivities);

            // get base query string
            $resolveBaseQueryStringArrayFunction    = static::getProtectedMethod($className,
                                                                                        'resolveBaseQueryStringArray');
            $queryStringArray                       = $resolveBaseQueryStringArrayFunction->invokeArgs(null, array(
                                                                                                            $modelId,
                                                                                                            $modelType,
                                                                                                            $personId));
            $this->assertNotEmpty($queryStringArray);
            $this->assertCount(3,                   $queryStringArray);
            $this->assertArrayHasKey('modelId',     $queryStringArray);
            $this->assertArrayHasKey('modelType',   $queryStringArray);
            $this->assertArrayHasKey('personId',    $queryStringArray);

            // get hash for query string and ensure its what we expect it to be.
            $resolveHashForQueryStringArrayFunction = static::getProtectedMethod($className,
                                                                                    'resolveHashForQueryStringArray');
            $queryStringArrayHash                   = $resolveHashForQueryStringArrayFunction->invokeArgs(null,
                                                                                            array($queryStringArray));
            $queryStringArrayDecoded                = $className::resolveQueryStringArrayForHash($queryStringArrayHash);
            $this->assertNotEmpty($queryStringArrayDecoded);
            $this->assertCount(5,                               $queryStringArrayDecoded);
            $this->assertArrayHasKey('modelId',                 $queryStringArrayDecoded);
            $this->assertArrayHasKey('modelType',               $queryStringArrayDecoded);
            $this->assertArrayHasKey('personId',                $queryStringArrayDecoded);
            $this->assertArrayHasKey('url',                     $queryStringArrayDecoded);
            $this->assertArrayHasKey('type',                    $queryStringArrayDecoded);
            $this->assertEquals($queryStringArray['modelId'],   $queryStringArrayDecoded['modelId']);
            $this->assertEquals($queryStringArray['modelType'], $queryStringArrayDecoded['modelType']);
            $this->assertEquals($queryStringArray['personId'],  $queryStringArrayDecoded['personId']);
            $this->assertNull($queryStringArrayDecoded['url']);
            $this->assertNull($queryStringArrayDecoded['type']);

            // do the magic, confirm magic worked by checking return value.
            $_GET['id']                         = $queryStringArrayHash;
            $result                             = $className::resolveQueryStringFromUrlAndCreateOrUpdateActivity();
        }

        /**
         * @depends testResolveQueryStringFromUrlAndCreateOrUpdateActivityThrowsExceptionForInvalidAutoresponderItemIdForEmailOpenTracking
         * @expectedException NotFoundException
         */
        public function testResolveQueryStringFromUrlAndCreateOrUpdateActivityThrowsExceptionForInvalidPersonIdForEmailOpenTracking()
        {
            // setup pre-req data
            $contact            = ContactTestHelper::createContactByNameForOwner('contact 03', $this->user);
            $marketingList      = MarketingListTestHelper::createMarketingListByName('marketingList 03',
                                                                                    'description 03',
                                                                                    'fromName 03',
                                                                                    'fromAddress03@domain.com');
            $autoresponder      = AutoresponderTestHelper::createAutoresponder('subject 03',
                                                                                'textContent 03',
                                                                                'htmlContent 03',
                                                                                10,
                                                                                Autoresponder::OPERATION_SUBSCRIBE,
                                                                                1,
                                                                                $marketingList);
            $processed          = 0;
            $processDateTime    = DateTimeUtil::convertTimestampToDbFormatDateTime(time()-100);
            $autoresponderItem  = AutoresponderItemTestHelper::createAutoresponderItem($processed,
                                                                                        $processDateTime,
                                                                                        $autoresponder,
                                                                                        $contact);
            $modelId            = $autoresponderItem->id;
            $modelType          = get_class($autoresponderItem);
            $personId           = $contact->getClassId('Person');
            $this->assertNotNull($personId);
            $personId           = $personId + 200;

            // get the modelClassName to use for activity object tests
            $className                                  = 'AutoresponderItemActivityUtil';
            $resolveModelClassNameByModelTypeFunction   = static::getProtectedMethod($className,
                                                                                    'resolveModelClassNameByModelType');
            $modelClassName                             = $resolveModelClassNameByModelTypeFunction->invokeArgs(null,
                                                                                                    array($modelType));
            $type                                       = $modelClassName::TYPE_OPEN;
            $existingActivities                         = $modelClassName::getByType($type);
            $this->assertCount(1, $existingActivities);

            // get base query string
            $resolveBaseQueryStringArrayFunction    = static::getProtectedMethod($className,
                                                                                        'resolveBaseQueryStringArray');
            $queryStringArray                       = $resolveBaseQueryStringArrayFunction->invokeArgs(null, array(
                                                                                                            $modelId,
                                                                                                            $modelType,
                                                                                                            $personId));
            $this->assertNotEmpty($queryStringArray);
            $this->assertCount(3,                   $queryStringArray);
            $this->assertArrayHasKey('modelId',     $queryStringArray);
            $this->assertArrayHasKey('modelType',   $queryStringArray);
            $this->assertArrayHasKey('personId',    $queryStringArray);

            // get hash for query string and ensure its what we expect it to be.
            $resolveHashForQueryStringArrayFunction = static::getProtectedMethod($className,
                                                                                    'resolveHashForQueryStringArray');
            $queryStringArrayHash                   = $resolveHashForQueryStringArrayFunction->invokeArgs(null,
                                                                                            array($queryStringArray));
            $queryStringArrayDecoded                = $className::resolveQueryStringArrayForHash($queryStringArrayHash);
            $this->assertNotEmpty($queryStringArrayDecoded);
            $this->assertCount(5,                               $queryStringArrayDecoded);
            $this->assertArrayHasKey('modelId',                 $queryStringArrayDecoded);
            $this->assertArrayHasKey('modelType',               $queryStringArrayDecoded);
            $this->assertArrayHasKey('personId',                $queryStringArrayDecoded);
            $this->assertArrayHasKey('url',                     $queryStringArrayDecoded);
            $this->assertArrayHasKey('type',                    $queryStringArrayDecoded);
            $this->assertEquals($queryStringArray['modelId'],   $queryStringArrayDecoded['modelId']);
            $this->assertEquals($queryStringArray['modelType'], $queryStringArrayDecoded['modelType']);
            $this->assertEquals($queryStringArray['personId'],  $queryStringArrayDecoded['personId']);
            $this->assertNull($queryStringArrayDecoded['url']);
            $this->assertNull($queryStringArrayDecoded['type']);

            // do the magic, confirm magic worked by checking return value.
            $_GET['id']                             = $queryStringArrayHash;
            $result                                 = $className::resolveQueryStringFromUrlAndCreateOrUpdateActivity();
        }

        /**
         * @depends testResolveQueryStringFromUrlAndCreateOrUpdateActivityThrowsExceptionForInvalidPersonIdForEmailOpenTracking
         * @expectedException NotSupportedException
         */
        public function testResolveQueryStringFromUrlAndCreateOrUpdateActivityThrowsExceptionForDuplicateActivityForEmailOpenTracking()
        {
            // setup pre-req data
            $autoresponderItemActivities                = AutoresponderItemActivity::getByType(
                                                                                AutoresponderItemActivity::TYPE_OPEN);
            $this->assertNotEmpty($autoresponderItemActivities);
            $autoresponderActivity                      = new AutoresponderItemActivity();
            $autoresponderActivity->quantity            = $autoresponderItemActivities[0]->quantity;
            $autoresponderActivity->type                = $autoresponderItemActivities[0]->type;
            $autoresponderActivity->autoresponderItem   = $autoresponderItemActivities[0]->autoresponderItem;
            $autoresponderActivity->person              = $autoresponderItemActivities[0]->person;
            $this->assertTrue($autoresponderActivity->save());

            $modelId            = $autoresponderItemActivities[0]->autoresponderItem->id;
            $modelType          = get_class($autoresponderItemActivities[0]->autoresponderItem);
            $personId           = $autoresponderItemActivities[0]->person->id;

            // get the modelClassName to use for activity object tests
            $className                                  = 'AutoresponderItemActivityUtil';
            $resolveModelClassNameByModelTypeFunction   = static::getProtectedMethod($className,
                                                                                    'resolveModelClassNameByModelType');
            $modelClassName                             = $resolveModelClassNameByModelTypeFunction->invokeArgs(null,
                                                                                                    array($modelType));
            $type                                       = $modelClassName::TYPE_OPEN;
            $existingActivities                         = $modelClassName::getByType($type);
            $this->assertCount(2, $existingActivities);

            // get base query string
            $resolveBaseQueryStringArrayFunction    = static::getProtectedMethod($className,
                                                                                        'resolveBaseQueryStringArray');
            $queryStringArray                       = $resolveBaseQueryStringArrayFunction->invokeArgs(null, array(
                                                                                                            $modelId,
                                                                                                            $modelType,
                                                                                                            $personId));
            $this->assertNotEmpty($queryStringArray);
            $this->assertCount(3,                   $queryStringArray);
            $this->assertArrayHasKey('modelId',     $queryStringArray);
            $this->assertArrayHasKey('modelType',   $queryStringArray);
            $this->assertArrayHasKey('personId',    $queryStringArray);

            // get hash for query string and ensure its what we expect it to be.
            $resolveHashForQueryStringArrayFunction = static::getProtectedMethod($className,
                                                                                    'resolveHashForQueryStringArray');
            $queryStringArrayHash                   = $resolveHashForQueryStringArrayFunction->invokeArgs(null,
                                                                                            array($queryStringArray));
            $queryStringArrayDecoded                = $className::resolveQueryStringArrayForHash($queryStringArrayHash);
            $this->assertNotEmpty($queryStringArrayDecoded);
            $this->assertCount(5,                               $queryStringArrayDecoded);
            $this->assertArrayHasKey('modelId',                 $queryStringArrayDecoded);
            $this->assertArrayHasKey('modelType',               $queryStringArrayDecoded);
            $this->assertArrayHasKey('personId',                $queryStringArrayDecoded);
            $this->assertArrayHasKey('url',                     $queryStringArrayDecoded);
            $this->assertArrayHasKey('type',                    $queryStringArrayDecoded);
            $this->assertEquals($queryStringArray['modelId'],   $queryStringArrayDecoded['modelId']);
            $this->assertEquals($queryStringArray['modelType'], $queryStringArrayDecoded['modelType']);
            $this->assertEquals($queryStringArray['personId'],  $queryStringArrayDecoded['personId']);
            $this->assertNull($queryStringArrayDecoded['url']);
            $this->assertNull($queryStringArrayDecoded['type']);

            // do the magic, confirm magic worked by checking return value.
            $_GET['id']                             = $queryStringArrayHash;
            $result                                 = $className::resolveQueryStringFromUrlAndCreateOrUpdateActivity();
        }

        // same tests but with url:
        /**
         * @depends testResolveQueryStringFromUrlAndCreateOrUpdateActivityThrowsExceptionWhenNoIdInQueryString
         */
        public function testResolveQueryStringFromUrlAndCreateOrUpdateActivityForUrlClickTrackingWithoutExceptions()
        {
            // setup pre-req data
            $contact            = ContactTestHelper::createContactByNameForOwner('contact 04', $this->user);
            $marketingList      = MarketingListTestHelper::createMarketingListByName('marketingList 04',
                                                                                        'description 04',
                                                                                        'fromName 04',
                                                                                        'fromAddress04@domain.com');
            $autoresponder      = AutoresponderTestHelper::createAutoresponder('subject 04',
                                                                                'textContent 04',
                                                                                'htmlContent 04',
                                                                                10,
                                                                                Autoresponder::OPERATION_SUBSCRIBE,
                                                                                1,
                                                                                $marketingList);
            $processed          = 0;
            $processDateTime    = DateTimeUtil::convertTimestampToDbFormatDateTime(time()-100);
            $autoresponderItem  = AutoresponderItemTestHelper::createAutoresponderItem($processed,
                                                                                $processDateTime,
                                                                                $autoresponder,
                                                                                $contact);
            $modelId            = $autoresponderItem->id;
            $modelType          = get_class($autoresponderItem);
            $personId           = $contact->getClassId('Person');
            $this->assertNotNull($personId);

            // get the modelClassName to use for activity object tests
            $className                                  = 'AutoresponderItemActivityUtil';
            $resolveModelClassNameByModelTypeFunction   = static::getProtectedMethod($className,
                                                                                    'resolveModelClassNameByModelType');
            $modelClassName                             = $resolveModelClassNameByModelTypeFunction->invokeArgs(null,
                                                                                                    array($modelType));
            $type                                       = $modelClassName::TYPE_CLICK;
            $existingActivities                         = $modelClassName::getByType($type);
            $this->assertCount(0, $existingActivities);

            // get base query string
            $resolveBaseQueryStringArrayFunction    = static::getProtectedMethod($className, 'resolveBaseQueryStringArray');
            $queryStringArray                       = $resolveBaseQueryStringArrayFunction->invokeArgs(null, array(
                                                                                                            $modelId,
                                                                                                            $modelType,
                                                                                                            $personId));
            $this->assertNotEmpty($queryStringArray);
            $this->assertCount(3,                   $queryStringArray);
            $this->assertArrayHasKey('modelId',     $queryStringArray);
            $this->assertArrayHasKey('modelType',   $queryStringArray);
            $this->assertArrayHasKey('personId',    $queryStringArray);
            $queryStringArray['url']    = 'http://www.zurmo.com';

            // get hash for query string and ensure its what we expect it to be.
            $resolveHashForQueryStringArrayFunction = static::getProtectedMethod($className,
                                                                                    'resolveHashForQueryStringArray');
            $queryStringArrayHash                   = $resolveHashForQueryStringArrayFunction->invokeArgs(null,
                                                                                            array($queryStringArray));
            $queryStringArrayDecoded                = $className::resolveQueryStringArrayForHash($queryStringArrayHash);
            $this->assertNotEmpty($queryStringArrayDecoded);
            $this->assertCount(5,                               $queryStringArrayDecoded);
            $this->assertArrayHasKey('modelId',                 $queryStringArrayDecoded);
            $this->assertArrayHasKey('modelType',               $queryStringArrayDecoded);
            $this->assertArrayHasKey('personId',                $queryStringArrayDecoded);
            $this->assertArrayHasKey('url',                     $queryStringArrayDecoded);
            $this->assertArrayHasKey('type',                     $queryStringArrayDecoded);
            $this->assertEquals($queryStringArray['modelId'],   $queryStringArrayDecoded['modelId']);
            $this->assertEquals($queryStringArray['modelType'], $queryStringArrayDecoded['modelType']);
            $this->assertEquals($queryStringArray['personId'],  $queryStringArrayDecoded['personId']);
            $this->assertEquals($queryStringArray['url'],       $queryStringArrayDecoded['url']);
            $this->assertNull($queryStringArrayDecoded['type']);

            // do the magic, confirm magic worked by checking return value.
            $_GET['id']                         = $queryStringArrayHash;
            $result                             = $className::resolveQueryStringFromUrlAndCreateOrUpdateActivity();
            $this->assertNotEmpty($result);
            $this->assertCount(2,                           $result);
            $this->assertArrayHasKey('redirect',            $result);
            $this->assertArrayHasKey('url',                 $result);
            $this->assertEquals(true,                       $result['redirect']);
            $this->assertEquals($queryStringArray['url'],   $result['url']);

            // check activity object count to confirm we got a new activity
            $existingActivities                 = $modelClassName::getByType($type);
            $this->assertCount(1,   $existingActivities);
            // try fetching an object matching criteria of the one we just inserted.
            $activity                           = $modelClassName::getByTypeAndModelIdAndPersonIdAndUrl($type,
                                                                                                        $modelId,
                                                                                                        $personId);
            $this->assertNotEmpty($activity);
            $this->assertCount(1,                           $activity);
            $this->assertEquals(1,                          $activity[0]->quantity);
            $this->assertEquals($queryStringArray['url'],   $activity[0]->emailMessageUrl->url);

            // do the magic again, this time it should update quantity
            $result                             = $className::resolveQueryStringFromUrlAndCreateOrUpdateActivity();
            $this->assertNotEmpty($result);
            $this->assertCount(2,                           $result);
            $this->assertArrayHasKey('redirect',            $result);
            $this->assertArrayHasKey('url',                 $result);
            $this->assertEquals(true,                       $result['redirect']);
            $this->assertEquals($queryStringArray['url'],   $result['url']);

            // check activity object count to confirm we got a new activity
            $existingActivities             = $modelClassName::getByType($type);
            $this->assertCount(1,   $existingActivities);
            // try fetching an object matching criteria of the one we just inserted.

            $activity                       = $modelClassName::getByTypeAndModelIdAndPersonIdAndUrl($type,
                                                                                                    $modelId,
                                                                                                    $personId);
            $this->assertNotEmpty($activity);
            $this->assertCount(1,   $activity);
            $this->assertEquals(2,  $activity[0]->quantity);
        }

        /**
         * @depends testResolveQueryStringFromUrlAndCreateOrUpdateActivityForUrlClickTrackingWithoutExceptions
         * @expectedException NotFoundException
         */
        public function testResolveQueryStringFromUrlAndCreateOrUpdateActivityThrowsExceptionForInvalidAutoresponderItemIdForUrlClickTracking()
        {
            // setup pre-req data
            $contact            = ContactTestHelper::createContactByNameForOwner('contact 05', $this->user);
            $marketingList      = MarketingListTestHelper::createMarketingListByName('marketingList 05',
                                                                                        'description 05',
                                                                                        'fromName 05',
                                                                                        'fromAddress05@domain.com');
            $autoresponder      = AutoresponderTestHelper::createAutoresponder('subject 05',
                                                                                'textContent 05',
                                                                                'htmlContent 05',
                                                                                10,
                                                                                Autoresponder::OPERATION_SUBSCRIBE,
                                                                                1,
                                                                                $marketingList);
            $processed          = 0;
            $processDateTime    = DateTimeUtil::convertTimestampToDbFormatDateTime(time()-100);
            $autoresponderItem  = AutoresponderItemTestHelper::createAutoresponderItem($processed,
                                                                                        $processDateTime,
                                                                                        $autoresponder,
                                                                                        $contact);
            $modelId            = $autoresponderItem->id;
            $modelId            += 200;
            $modelType          = get_class($autoresponderItem);
            $personId           = $contact->getClassId('Person');
            $this->assertNotNull($personId);

            // get the modelClassName to use for activity object tests
            $className                                  = 'AutoresponderItemActivityUtil';
            $resolveModelClassNameByModelTypeFunction   = static::getProtectedMethod($className,
                                                                                    'resolveModelClassNameByModelType');
            $modelClassName                             = $resolveModelClassNameByModelTypeFunction->invokeArgs(null,
                                                                                                    array($modelType));
            $type                                       = $modelClassName::TYPE_CLICK;
            $existingActivities                         = $modelClassName::getByType($type);
            $this->assertCount(1, $existingActivities);

            // get base query string
            $resolveBaseQueryStringArrayFunction    = static::getProtectedMethod($className,
                                                                                        'resolveBaseQueryStringArray');
            $queryStringArray                       = $resolveBaseQueryStringArrayFunction->invokeArgs(null, array(
                                                                                                            $modelId,
                                                                                                            $modelType,
                                                                                                            $personId));
            $this->assertNotEmpty($queryStringArray);
            $this->assertCount(3,                   $queryStringArray);
            $this->assertArrayHasKey('modelId',     $queryStringArray);
            $this->assertArrayHasKey('modelType',   $queryStringArray);
            $this->assertArrayHasKey('personId',    $queryStringArray);
            $queryStringArray['url']    = 'http://www.zurmo.com';

            // get hash for query string and ensure its what we expect it to be.
            $resolveHashForQueryStringArrayFunction = static::getProtectedMethod($className,
                                                                                        'resolveHashForQueryStringArray');
            $queryStringArrayHash                   = $resolveHashForQueryStringArrayFunction->invokeArgs(null,
                                                                                            array($queryStringArray));
            $queryStringArrayDecoded                = $className::resolveQueryStringArrayForHash($queryStringArrayHash);
            $this->assertNotEmpty($queryStringArrayDecoded);
            $this->assertCount(5,                               $queryStringArrayDecoded);
            $this->assertArrayHasKey('modelId',                 $queryStringArrayDecoded);
            $this->assertArrayHasKey('modelType',               $queryStringArrayDecoded);
            $this->assertArrayHasKey('personId',                $queryStringArrayDecoded);
            $this->assertArrayHasKey('url',                     $queryStringArrayDecoded);
            $this->assertArrayHasKey('type',                    $queryStringArrayDecoded);
            $this->assertEquals($queryStringArray['modelId'],   $queryStringArrayDecoded['modelId']);
            $this->assertEquals($queryStringArray['modelType'], $queryStringArrayDecoded['modelType']);
            $this->assertEquals($queryStringArray['personId'],  $queryStringArrayDecoded['personId']);
            $this->assertEquals($queryStringArray['url'],       $queryStringArrayDecoded['url']);
            $this->assertNull($queryStringArrayDecoded['type']);

            // do the magic, confirm magic worked by checking return value.
            $_GET['id']                         = $queryStringArrayHash;
            $result                             = $className::resolveQueryStringFromUrlAndCreateOrUpdateActivity();
        }

        /**
         * @depends testResolveQueryStringFromUrlAndCreateOrUpdateActivityThrowsExceptionForInvalidAutoresponderItemIdForUrlClickTracking
         * @expectedException NotFoundException
         */
        public function testResolveQueryStringFromUrlAndCreateOrUpdateActivityThrowsExceptionForInvalidPersonIdForUrlClickTracking()
        {
            // setup pre-req data
            $contact            = ContactTestHelper::createContactByNameForOwner('contact 06', $this->user);
            $marketingList      = MarketingListTestHelper::createMarketingListByName('marketingList 06',
                                                                                        'description 06',
                                                                                        'fromName 06',
                                                                                        'fromAddress06@domain.com');
            $autoresponder      = AutoresponderTestHelper::createAutoresponder('subject 06',
                                                                                'textContent 06',
                                                                                'htmlContent 06',
                                                                                10,
                                                                                Autoresponder::OPERATION_SUBSCRIBE,
                                                                                1,
                                                                                $marketingList);
            $processed          = 0;
            $processDateTime    = DateTimeUtil::convertTimestampToDbFormatDateTime(time()-100);
            $autoresponderItem  = AutoresponderItemTestHelper::createAutoresponderItem($processed,
                                                                                        $processDateTime,
                                                                                        $autoresponder,
                                                                                        $contact);
            $modelId            = $autoresponderItem->id;
            $modelType          = get_class($autoresponderItem);
            $personId           = $contact->getClassId('Person');
            $this->assertNotNull($personId);
            $personId           = $personId + 200;

            // get the modelClassName to use for activity object tests
            $className                                  = 'AutoresponderItemActivityUtil';
            $resolveModelClassNameByModelTypeFunction   = static::getProtectedMethod($className,
                                                                                    'resolveModelClassNameByModelType');
            $modelClassName                             = $resolveModelClassNameByModelTypeFunction->invokeArgs(null,
                                                                                                    array($modelType));
            $type                                       = $modelClassName::TYPE_CLICK;
            $existingActivities                         = $modelClassName::getByType($type);
            $this->assertCount(1,   $existingActivities);

            // get base query string
            $resolveBaseQueryStringArrayFunction    = static::getProtectedMethod($className,
                                                                                        'resolveBaseQueryStringArray');
            $queryStringArray                       = $resolveBaseQueryStringArrayFunction->invokeArgs(null, array(
                                                                                                            $modelId,
                                                                                                            $modelType,
                                                                                                            $personId));
            $this->assertNotEmpty($queryStringArray);
            $this->assertCount(3,                   $queryStringArray);
            $this->assertArrayHasKey('modelId',     $queryStringArray);
            $this->assertArrayHasKey('modelType',   $queryStringArray);
            $this->assertArrayHasKey('personId',    $queryStringArray);
            $queryStringArray['url']    = 'http://www.zurmo.com';

            // get hash for query string and ensure its what we expect it to be.
            $resolveHashForQueryStringArrayFunction = static::getProtectedMethod($className,
                                                                                    'resolveHashForQueryStringArray');
            $queryStringArrayHash                   = $resolveHashForQueryStringArrayFunction->invokeArgs(null,
                                                                                            array($queryStringArray));
            $queryStringArrayDecoded                = $className::resolveQueryStringArrayForHash($queryStringArrayHash);
            $this->assertNotEmpty($queryStringArrayDecoded);
            $this->assertCount(5,                               $queryStringArrayDecoded);
            $this->assertArrayHasKey('modelId',                 $queryStringArrayDecoded);
            $this->assertArrayHasKey('modelType',               $queryStringArrayDecoded);
            $this->assertArrayHasKey('personId',                $queryStringArrayDecoded);
            $this->assertArrayHasKey('url',                     $queryStringArrayDecoded);
            $this->assertArrayHasKey('type',                    $queryStringArrayDecoded);
            $this->assertEquals($queryStringArray['modelId'],   $queryStringArrayDecoded['modelId']);
            $this->assertEquals($queryStringArray['modelType'], $queryStringArrayDecoded['modelType']);
            $this->assertEquals($queryStringArray['personId'],  $queryStringArrayDecoded['personId']);
            $this->assertEquals($queryStringArray['url'],       $queryStringArrayDecoded['url']);
            $this->assertNull($queryStringArrayDecoded['type']);

            // do the magic, confirm magic worked by checking return value.
            $_GET['id']             = $queryStringArrayHash;
            $result                 = $className::resolveQueryStringFromUrlAndCreateOrUpdateActivity();
        }

        /**
         * @depends testResolveQueryStringFromUrlAndCreateOrUpdateActivityThrowsExceptionForInvalidPersonIdForUrlClickTracking
         * @expectedException NotSupportedException
         */
        public function testResolveQueryStringFromUrlAndCreateOrUpdateActivityThrowsExceptionForDuplicateActivityForUrlClickTracking()
        {
            // setup pre-req data
            $autoresponderItemActivities                = AutoresponderItemActivity::getByType(
                                                                                AutoresponderItemActivity::TYPE_CLICK);
            $this->assertNotEmpty($autoresponderItemActivities);
            $autoresponderActivity                      = new AutoresponderItemActivity();
            $autoresponderActivity->quantity            = $autoresponderItemActivities[0]->quantity;
            $autoresponderActivity->type                = $autoresponderItemActivities[0]->type;
            $autoresponderActivity->autoresponderItem   = $autoresponderItemActivities[0]->autoresponderItem;
            $autoresponderActivity->person              = $autoresponderItemActivities[0]->person;
            $autoresponderActivity->emailMessageUrl     = $autoresponderItemActivities[0]->emailMessageUrl;
            $this->assertTrue($autoresponderActivity->save());

            $modelId                = $autoresponderItemActivities[0]->autoresponderItem->id;
            $modelType              = get_class($autoresponderItemActivities[0]->autoresponderItem);
            $personId               = $autoresponderItemActivities[0]->person->id;

            // get the modelClassName to use for activity object tests
            $className              = 'AutoresponderItemActivityUtil';
            $resolveModelClassNameByModelTypeFunction = static::getProtectedMethod($className,
                                                                                    'resolveModelClassNameByModelType');
            $modelClassName         = $resolveModelClassNameByModelTypeFunction->invokeArgs(null, array($modelType));
            $type                   = $modelClassName::TYPE_CLICK;
            $existingActivities     = $modelClassName::getByType($type);
            $this->assertCount(2, $existingActivities);

            // get base query string
            $resolveBaseQueryStringArrayFunction    = static::getProtectedMethod($className,
                                                                                        'resolveBaseQueryStringArray');
            $queryStringArray                       = $resolveBaseQueryStringArrayFunction->invokeArgs(null, array(
                                                                                                            $modelId,
                                                                                                            $modelType,
                                                                                                            $personId));
            $this->assertNotEmpty($queryStringArray);
            $this->assertCount(3,                   $queryStringArray);
            $this->assertArrayHasKey('modelId',     $queryStringArray);
            $this->assertArrayHasKey('modelType',   $queryStringArray);
            $this->assertArrayHasKey('personId',    $queryStringArray);
            $queryStringArray['url']    = 'http://www.zurmo.com';

            // get hash for query string and ensure its what we expect it to be.
            $resolveHashForQueryStringArrayFunction = static::getProtectedMethod($className,
                                                                                    'resolveHashForQueryStringArray');
            $queryStringArrayHash                   = $resolveHashForQueryStringArrayFunction->invokeArgs(null,
                                                                                                array($queryStringArray));
            $queryStringArrayDecoded                = $className::resolveQueryStringArrayForHash($queryStringArrayHash);
            $this->assertNotEmpty($queryStringArrayDecoded);
            $this->assertCount(5,                               $queryStringArrayDecoded);
            $this->assertArrayHasKey('modelId',                 $queryStringArrayDecoded);
            $this->assertArrayHasKey('modelType',               $queryStringArrayDecoded);
            $this->assertArrayHasKey('personId',                $queryStringArrayDecoded);
            $this->assertArrayHasKey('url',                     $queryStringArrayDecoded);
            $this->assertArrayHasKey('type',                    $queryStringArrayDecoded);
            $this->assertEquals($queryStringArray['modelId'],   $queryStringArrayDecoded['modelId']);
            $this->assertEquals($queryStringArray['modelType'], $queryStringArrayDecoded['modelType']);
            $this->assertEquals($queryStringArray['personId'],  $queryStringArrayDecoded['personId']);
            $this->assertEquals($queryStringArray['url'],       $queryStringArrayDecoded['url']);
            $this->assertNull($queryStringArrayDecoded['type']);

            // do the magic, confirm magic worked by checking return value.
            $_GET['id']                         = $queryStringArrayHash;
            $result                             = $className::resolveQueryStringFromUrlAndCreateOrUpdateActivity();
        }
    }
?>