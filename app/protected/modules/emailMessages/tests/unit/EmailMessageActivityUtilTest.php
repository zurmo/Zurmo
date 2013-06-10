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

    class EmailMessageActivityUtilTest extends ZurmoBaseTest
    {
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

        /**
         * @expectedException NotSupportedException
         */
        public function testResolveQueryStringFromUrlAndCreateNewActivityThrowsExceptionForNonHexadecimalHash()
        {
            $hash       = 'Bo9iemeigh6muath8chu2leThohn8Abimoh5rebaihei4aiM1uFoThaith9eng1sei8aisuHu1ugoophiewo3ohphie1ieloo';
            $result     = EmailMessageActivityUtil::resolveQueryStringArrayForHash($hash);
        }

        /**
         * @depends testResolveQueryStringFromUrlAndCreateNewActivityThrowsExceptionForNonHexadecimalHash
         * @expectedException NotSupportedException
         */
        public function testResolveQueryStringFromUrlAndCreateNewActivityThrowsExceptionForIndecipherableHexadecimalHash()
        {
            $hash       = 'DEDF8F6C80D20528130EBBFBD293E49C9E2F0CBFDE8995FFE4EEAD8EC8F00B70';
            $result     = EmailMessageActivityUtil::resolveQueryStringArrayForHash($hash);
        }

        /**
         * @depends testResolveQueryStringFromUrlAndCreateNewActivityThrowsExceptionForIndecipherableHexadecimalHash
         * @expectedException NotSupportedException
         */
        public function testResolveQueryStringFromUrlAndCreateNewActivityThrowsExceptionForDecipherableHexadecimalHashWithMissingParameters()
        {
            $queryStringArray = array(
                'keyOne'    => 'valueOne',
                'keyTwo'    => 'valueTwo',
                'keyThree'  => 'ValueThree',
            );
            $resolveHashForQueryStringArrayFunction = static::getProtectedMethod('EmailMessageActivityUtil',
                                                                                    'resolveHashForQueryStringArray');
            $hash       = $resolveHashForQueryStringArrayFunction->invokeArgs(null, array($queryStringArray));
            $result     = EmailMessageActivityUtil::resolveQueryStringArrayForHash($hash);
        }

        /**
         * @depends testResolveQueryStringFromUrlAndCreateNewActivityThrowsExceptionForDecipherableHexadecimalHashWithMissingParameters
         */
        public function testResolveQueryStringFromUrlAndCreateNewActivityDoesNotThrowsExceptionForMissingUrlParameter()
        {
            $queryStringArray = array(
                'modelId'   => 1,
                'modelType' => 'ModelClassName',
                'personId'  => 2,
            );
            $resolveHashForQueryStringArrayFunction = static::getProtectedMethod('EmailMessageActivityUtil',
                                                                                    'resolveHashForQueryStringArray');
            $hash       = $resolveHashForQueryStringArrayFunction->invokeArgs(null, array($queryStringArray));
            $result     = EmailMessageActivityUtil::resolveQueryStringArrayForHash($hash);
            $this->assertTrue(is_array($result));
            $this->assertCount(5, $result);
            $this->assertArrayHasKey('modelId', $result);
            $this->assertArrayHasKey('modelType', $result);
            $this->assertArrayHasKey('personId', $result);
            $this->assertArrayHasKey('url', $result);
            $this->assertArrayHasKey('type', $result);
            $this->assertEquals($queryStringArray['modelId'], $result['modelId']);
            $this->assertEquals($queryStringArray['modelType'], $result['modelType']);
            $this->assertEquals($queryStringArray['personId'], $result['personId']);
            $this->assertNull($result['url']);
            $this->assertNull($result['type']);
        }

        public function testReturnTrueWithNoTracking()
        {
            $content    = 'Sample Content with no links';
            $result     = static::resolveContent($content, false, false);
            $this->assertTrue($result);
            $this->assertNotEquals('Sample Content with no links', $content);
            $this->assertTrue(strpos($content, 'Sample Content with no links') !== false);
            $this->assertTrue(strpos($content, '/marketingLists/external/') !== false);
            $this->assertEquals(2, substr_count($content, '/marketingLists/external/'));
        }

        /**
         * @depends testReturnTrueWithNoTracking
         */
        public function testTextContentDoesNotChangeWhenNoLinksArePresent()
        {
            $content    = 'Sample Content with no links';
            $result     = static::resolveContent($content, true, false);
            $this->assertTrue($result);
            $this->assertTrue(strpos($content, 'Sample Content with no links') !== false);
            $this->assertTrue(strpos($content, '/marketingLists/external/') !== false);
            $this->assertEquals(2, substr_count($content, '/marketingLists/external/'));
        }

        /**
         * @depends testTextContentDoesNotChangeWhenNoLinksArePresent
         */
        public function testReturnsFalseWithFewTrackingUrlsInPlaceAlready()
        {
            $content    = '/tracking/default/track';
            $result     = static::resolveContent($content, true, false);
            $this->assertFalse($result);
        }

        /**
         * @depends testReturnsFalseWithFewTrackingUrlsInPlaceAlready
         */
        public function testTextContentLinksAreConvertedToTracked()
        {
            $content    = <<<LNK
Link: http://www.zurmo.com
Another: http://zurmo.org
www.yahoo.com
LNK;
            $result     = static::resolveContent($content, true, false);
            $this->assertTrue($result);
            $this->assertFalse(strpos($content, 'http://www.zurmo.com'));
            $this->assertFalse(strpos($content, 'http://www.zurmo.org'));
            $this->assertFalse(strpos($content, 'www.yahoo.com'));
            $this->assertTrue(strpos($content, '/tracking/default/track?id=') !== false);
            $this->assertEquals(3, substr_count($content, '/tracking/default/track?id='));
            $this->assertTrue(strpos($content, '/marketingLists/external/') !== false);
            $this->assertEquals(2, substr_count($content, '/marketingLists/external/'));
        }

        /**
         * @depends testTextContentLinksAreConvertedToTracked
         */
        public function testTextContentLinkConversionIgnoresHref()
        {
            $content    = 'Link: http://www.zurmo.com , <a href="http://www.zurmo.org">Zurmo</a>';
            $result     = static::resolveContent($content, true, false);
            $this->assertTrue($result);
            $this->assertFalse(strpos($content, 'http://www.zurmo.com'));
            $this->assertTrue(strpos($content, '<a href="http://www.zurmo.org">') !== false);
            $this->assertTrue(strpos($content, '/tracking/default/track?id=') !== false);
            $this->assertEquals(1, substr_count($content, '/tracking/default/track?id='));
            $this->assertTrue(strpos($content, '/marketingLists/external/') !== false);
            $this->assertEquals(2, substr_count($content, '/marketingLists/external/'));
        }

        /**
         * @depends testTextContentLinkConversionIgnoresHref
         */
        public function testHtmlContentWithoutAnyLinksAndNoDOMStructureStillGetsEmailOpenTracking()
        {
            $content    = 'Sample content';
            $result     = static::resolveContent($content);
            $this->assertTrue($result);
            $this->assertNotEquals('Sample content', $content);
            $this->assertTrue(strpos($content, 'Sample content') !== false);
            $this->assertTrue(strpos($content, '<img width="1" height="1" src="') !== false);
            $this->assertTrue(strpos($content, '/tracking/default/track?id=') !== false);
            $this->assertEquals(1, substr_count($content, '/tracking/default/track?id='));
            $this->assertTrue(strpos($content, '/marketingLists/external/') !== false);
            $this->assertEquals(2, substr_count($content, '/marketingLists/external/'));
        }

        /**
         * @depends testHtmlContentWithoutAnyLinksAndNoDOMStructureStillGetsEmailOpenTracking
         */
        public function testHtmlContentWithMultipleClosingBodyTagsGetOnlyOneEmailOpenTracking()
        {
            $content    = 'Sample content</body></body></body>';
            $result     = static::resolveContent($content);
            $this->assertTrue($result);
            $this->assertNotEquals('Sample content', $content);
            $this->assertTrue(strpos($content, 'Sample content') !== false);
            $this->assertTrue(strpos($content, '</body></body>') !== false);
            $this->assertTrue(strpos($content, '<img width="1" height="1" src="') !== false);
            $this->assertEquals(1, substr_count($content, '<img width="1" height="1" src="'));
            $this->assertTrue(strpos($content, '/tracking/default/track?id=') !== false);
            $this->assertEquals(1, substr_count($content, '/tracking/default/track?id='));
            $this->assertTrue(strpos($content, '/marketingLists/external/') !== false);
            $this->assertEquals(2, substr_count($content, '/marketingLists/external/'));
        }

        /**
         * @depends testHtmlContentWithMultipleClosingBodyTagsGetOnlyOneEmailOpenTracking
         */
        public function testHtmlContentWithoutAnyLinksAndSomeDOMStructureStillGetsEmailOpenTracking()
        {
            $content            = '<html><head><title>Page title</title></head><body><p>Sample Content</p></body></html>';
            $originalContent    = $content;
            $result             = static::resolveContent($content);
            $this->assertTrue($result);
            $this->assertNotEquals($originalContent, $content);
            $this->assertTrue(strpos($content, '<p>Sample Content</p>') !== false);
            $this->assertTrue(strpos($content, '<p>Sample Content</p><br /><img width="1" height="1" src="') !== false);
            $this->assertTrue(strpos($content, '/tracking/default/track?id=') !== false);
            $this->assertEquals(1, substr_count($content, '/tracking/default/track?id='));
            $this->assertTrue(strpos($content, '/marketingLists/external/') !== false);
            $this->assertEquals(2, substr_count($content, '/marketingLists/external/'));
        }

        /**
         * @depends testHtmlContentWithoutAnyLinksAndSomeDOMStructureStillGetsEmailOpenTracking
         */
        public function testHtmlContentWithPlainLinkGetsTracking()
        {
            $content    = <<<HTML
<html>
<head>
<title>
Page Title
</title>
</head>
<body>
<p>Sample Content With Links</p>
<p>Plain Link: http://www.zurmo.com</p>
</body>
</html>
HTML;
            $originalContent    = $content;
            $result             = static::resolveContent($content);
            $this->assertTrue($result);
            $this->assertNotEquals($originalContent, $content);
            $this->assertTrue(strpos($content, '<p>Sample Content With Links</p>') !== false);
            $this->assertTrue(strpos($content, '<p>Plain Link: http://www.zurmo.com</p>') === false);
            $this->assertTrue(strpos($content, '<img width="1" height="1" src="') !== false);
            $this->assertTrue(strpos($content, '/tracking/default/track?id=') !== false);
            $this->assertEquals(2, substr_count($content, '/tracking/default/track?id='));
            $this->assertTrue(strpos($content, '/marketingLists/external/') !== false);
            $this->assertEquals(2, substr_count($content, '/marketingLists/external/'));
        }

        /**
         * @depends testHtmlContentWithPlainLinkGetsTracking
         */
        public function testHtmlContentWithValidHrefAndPlainLinkGetsTracking()
        {
            $content    = <<<HTML
<html>
<head>
<title>
Page Title
</title>
</head>
<body>
<p>Sample Content With Links</p>
<p>Plain Link1: http://www.zurmo1.com</p>
<p>Plain Link2:http://www.zurmo2.com</p>
<p>Plain Link3: http://www.zurmo3.com </p>
<p>Plain Link4:
 http://www.zurmo4.com</p>
<p>Plain Link5:
http://www.zurmo5.com</p>
<p>Plain Link6:
http://www.zurmo6.com </p>
<p>Plain Link7:
http://www.zurmo7.com </p>
<p>Link1: <a href="http://www.zurmo.org">Zurmo</a></p>
<p>Link2: <a href='http://www.sourceforge1.org'>SourceForge</a></p>
<p>Link3: <a href='http://www.sourceforge2.org'>http://www.sourceforge2.org</a></p>
<p>Link4: <a href='http://www.sourceforge3.org'> http://www.sourceforge3.org</a></p>
<p>Link5: <a href='http://www.sourceforge4.org'>http://www.sourceforge4.org </a></p>
<p>Link6: <a href='http://www.sourceforge5.org'> http://www.sourceforge5.org </a></p>
<p>Link7: <a target='_blank' href='http://www.sourceforge6.org' style='color:red;'> http://www.sourceforge6.org </a></p>
<p>Link8: http://www.sourceforge8.org</a></p>
<p>Link9: http://www.sourceforge9.org </a></p>
<p>Link10:
<a href="http://www.sourceforge10.org">http://www.sourceforge10.org</a></p>
<p>Link11: <a
 href='http://www.sourceforge11.org'>http://www.sourceforge11.org</a></p>
<p>Link12: <a href='http://www.sourceforge12.org'>
 http://www.sourceforge12.org</a></p>
<p>Link13: <a href='http://www.sourceforge13.org'>
  http://www.sourceforge13.org</a></p>
<p>Link14: <a href='http://www.sourceforge14.org'>
  http://www.sourceforge14.org </a></p>
<p>Link15: <a href='#localanchor'>New</a></p>
<p>Link16: <a href='http://www.sourceforge16.org/projects#promoted'>Promoted Projects</a></p>
<img src='http://zurmo.org/wp-content/themes/Zurmo/images/Zurmo-logo.png' alt='Zurmo Logo' />
<link rel="apple-touch-icon" sizes="144x144" href="http://www.zurmo.com/icon.png">
<link rel="stylesheet" type="text/css" href="http://www.zurmo.com/css/keyframes.css">
<link rel="stylesheet" type="text/css" href="http://www.zurmo.com/zurmo/app/index.php/min/serve/g/css/lm/1366956624">
<script type="text/javascript" src="http://www.zurmo.com/zurmo/app/index.php/min/serve/g/js/lm/1366697759"></script>
<script type="text/javascript" src="http://www.zurmo.com/zurmo/app/index.php/min/serve/g/js/lm/1366697751.js"></script>
</body>
</html>
HTML;
            $originalContent    = $content;
            $result             = static::resolveContent($content);
            $this->assertTrue($result);
            $this->assertNotEquals($originalContent, $content);
            $this->assertEquals(19, substr_count($content, '/tracking/default/track?id='));
            $this->assertTrue(strpos($content, '<p>Sample Content With Links</p>') !== false);
            $this->assertTrue(strpos($content, 'http://www.zurmo1.com') === false);
            $this->assertTrue(strpos($content, 'http://www.zurmo2.com') !== false);
            $this->assertEquals(1, substr_count($content, 'http://www.zurmo2.com'));
            $this->assertTrue(strpos($content, 'http://www.zurmo3.com') === false);
            $this->assertTrue(strpos($content, 'http://www.zurmo4.com') === false);
            $this->assertTrue(strpos($content, 'http://www.zurmo5.com') === false);
            $this->assertTrue(strpos($content, 'http://www.zurmo6.com') === false);
            $this->assertTrue(strpos($content, 'http://www.zurmo7.com') === false);
            $this->assertTrue(strpos($content, 'http://www.zurmo.org') === false);
            $this->assertTrue(strpos($content, "SourceForge") !== false);
            $this->assertTrue(strpos($content, " href='http://www.sourceforge1.org'") === false);
            $this->assertTrue(strpos($content, " href='http://www.sourceforge2.org'") === false);
            $this->assertTrue(strpos($content, " href='http://www.sourceforge3.org'") === false);
            $this->assertTrue(strpos($content, " href='http://www.sourceforge4.org'") === false);
            $this->assertTrue(strpos($content, " href='http://www.sourceforge5.org'") === false);
            $this->assertTrue(strpos($content, " href='http://www.sourceforge6.org'") === false);
            $this->assertTrue(strpos($content, "http://www.sourceforge2.org") !== false);
            $this->assertTrue(strpos($content, "http://www.sourceforge3.org") !== false);
            $this->assertTrue(strpos($content, "http://www.sourceforge4.org") !== false);
            $this->assertTrue(strpos($content, "http://www.sourceforge5.org") !== false);
            $this->assertTrue(strpos($content, "http://www.sourceforge6.org") !== false);
            $this->assertEquals(1, substr_count($content, 'http://www.sourceforge2.org'));
            $this->assertEquals(1, substr_count($content, 'http://www.sourceforge3.org'));
            $this->assertEquals(1, substr_count($content, 'http://www.sourceforge4.org'));
            $this->assertEquals(1, substr_count($content, 'http://www.sourceforge5.org'));
            $this->assertEquals(1, substr_count($content, 'http://www.sourceforge6.org'));
            $this->assertTrue(strpos($content, "http://www.sourceforge8.org") !== false);
            $this->assertTrue(strpos($content, "http://www.sourceforge9.org") !== false);
            $this->assertEquals(1, substr_count($content, 'http://www.sourceforge8.org'));
            $this->assertEquals(1, substr_count($content, 'http://www.sourceforge9.org'));
            $this->assertTrue(strpos($content, " href='http://www.sourceforge1.org'") === false);
            $this->assertTrue(strpos($content, "http://www.sourceforge10.org") !== false);
            $this->assertEquals(1, substr_count($content, 'http://www.sourceforge10.org'));
            $this->assertTrue(strpos($content, 'Link10:' . "\n" . '<a href="') !== false);
            $this->assertEquals(1, substr_count($content, 'Link10:' . "\n" . '<a href="'));
            $this->assertTrue(strpos($content, "Link7: <a target='_blank' ") !== false);
            $this->assertEquals(1, substr_count($content, "Link7: <a target='_blank' "));
            $this->assertTrue(strpos($content, " style='color:red;'> ") !== false);
            $this->assertEquals(1, substr_count($content, " style='color:red;'> "));
            $this->assertTrue(strpos($content, "http://www.sourceforge11.org") !== false);
            $this->assertEquals(2, substr_count($content, 'http://www.sourceforge11.org'));
            $this->assertTrue(strpos($content, " href='http://www.sourceforge12.org'") === false);
            $this->assertTrue(strpos($content, " href='http://www.sourceforge13.org'") === false);
            $this->assertTrue(strpos($content, " href='http://www.sourceforge14.org'") === false);
            $this->assertTrue(strpos($content, "http://www.sourceforge12.org") !== false);
            $this->assertTrue(strpos($content, "http://www.sourceforge13.org") !== false);
            $this->assertTrue(strpos($content, "http://www.sourceforge14.org") !== false);
            $this->assertTrue(strpos($content, "<p>Link15: <a href='#localanchor'>New</a></p>") !== false);
            $this->assertTrue(strpos($content, "http://www.sourceforge16.org/projects#promoted") === false);
            $this->assertTrue(strpos($content,
                                        "http://zurmo.org/wp-content/themes/Zurmo/images/Zurmo-logo.png") !== false);
            $this->assertEquals(1, substr_count($content,
                                                    'http://zurmo.org/wp-content/themes/Zurmo/images/Zurmo-logo.png'));
            $this->assertTrue(strpos($content, "http://www.zurmo.com/icon.png") !== false);
            $this->assertEquals(1, substr_count($content, 'http://www.zurmo.com/icon.png'));
            $this->assertTrue(strpos($content, "http://www.zurmo.com/css/keyframes.css") !== false);
            $this->assertEquals(1, substr_count($content, 'http://www.zurmo.com/css/keyframes.css'));
            $this->assertTrue(strpos($content,
                                "http://www.zurmo.com/zurmo/app/index.php/min/serve/g/css/lm/1366956624") !== false);
            $this->assertEquals(1, substr_count($content,
                                            'http://www.zurmo.com/zurmo/app/index.php/min/serve/g/css/lm/1366956624'));
            $this->assertTrue(strpos($content,
                                    "http://www.zurmo.com/zurmo/app/index.php/min/serve/g/js/lm/1366697759") !== false);
            $this->assertEquals(1,
                    substr_count($content, 'http://www.zurmo.com/zurmo/app/index.php/min/serve/g/js/lm/1366697759'));
            $this->assertTrue(strpos($content,
                                "http://www.zurmo.com/zurmo/app/index.php/min/serve/g/js/lm/1366697751.js") !== false);
            $this->assertEquals(1,
                    substr_count($content, 'http://www.zurmo.com/zurmo/app/index.php/min/serve/g/js/lm/1366697751.js'));
            $this->assertTrue(strpos($content, '<img width="1" height="1" src=') !== false);
            $this->assertEquals(1, substr_count($content, '<img width="1" height="1" src='));
            $this->assertTrue(strpos($content, '/marketingLists/external/') !== false);
            $this->assertEquals(2, substr_count($content, '/marketingLists/external/'));
        }

        public function testResolveQueryStringArrayForHashWithAndWithoutUrlInQueryString()
        {
            // test without url
            $modelId                    = 1;
            $modelType                  = 'AutoresponderItem';
            $personId                   = 10;

            $className                  = 'EmailMessageActivityUtil';
            $resolveBaseQueryStringArrayFunction = static::getProtectedMethod($className, 'resolveBaseQueryStringArray');
            $withoutUrlQueryStringArray = $resolveBaseQueryStringArrayFunction->invokeArgs(null,
                                                                                            array($modelId,
                                                                                                $modelType,
                                                                                                $personId));
            $this->assertNotEmpty($withoutUrlQueryStringArray);
            $this->assertCount(3, $withoutUrlQueryStringArray);
            $this->assertArrayHasKey('modelId', $withoutUrlQueryStringArray);
            $this->assertArrayHasKey('modelType', $withoutUrlQueryStringArray);
            $this->assertArrayHasKey('personId', $withoutUrlQueryStringArray);
            $resolveHashForQueryStringArrayFunction = static::getProtectedMethod($className, 'resolveHashForQueryStringArray');
            $withoutUrlQueryStringArrayHash = $resolveHashForQueryStringArrayFunction->invokeArgs(null,
                                                                                    array($withoutUrlQueryStringArray));
            $withoutUrlQueryStringArrayDecoded = $className::resolveQueryStringArrayForHash($withoutUrlQueryStringArrayHash);
            $this->assertTrue(is_array($withoutUrlQueryStringArrayDecoded));
            $this->assertCount(5, $withoutUrlQueryStringArrayDecoded);
            $this->assertArrayHasKey('modelId', $withoutUrlQueryStringArrayDecoded);
            $this->assertArrayHasKey('modelType', $withoutUrlQueryStringArrayDecoded);
            $this->assertArrayHasKey('personId', $withoutUrlQueryStringArrayDecoded);
            $this->assertArrayHasKey('url', $withoutUrlQueryStringArrayDecoded);
            $this->assertArrayHasKey('type', $withoutUrlQueryStringArrayDecoded);
            $this->assertEquals($withoutUrlQueryStringArray['modelId'], $withoutUrlQueryStringArrayDecoded['modelId']);
            $this->assertEquals($withoutUrlQueryStringArray['modelType'], $withoutUrlQueryStringArrayDecoded['modelType']);
            $this->assertEquals($withoutUrlQueryStringArray['personId'], $withoutUrlQueryStringArrayDecoded['personId']);
            $this->assertNull($withoutUrlQueryStringArrayDecoded['url']);
            $this->assertNull($withoutUrlQueryStringArrayDecoded['type']);

            // try same thing with url in the query string array.
            $withUrlQueryStringArray = CMap::mergeArray($withoutUrlQueryStringArray,
                                                                    array('url'     => 'http://www.zurmo.com',
                                                                            'type'  => null));
            $withUrlQueryStringArrayHash = $resolveHashForQueryStringArrayFunction->invokeArgs(null,
                                                                                    array($withUrlQueryStringArray));
            $withUrlQueryStringArrayDecoded = $className::resolveQueryStringArrayForHash($withUrlQueryStringArrayHash);
            $this->assertEquals($withUrlQueryStringArray, $withUrlQueryStringArrayDecoded);
        }

        public function testTextContentGetsCustomFooterAppended()
        {
            AutoresponderOrCampaignMailFooterContentUtil::setContentByType('PlainTextFooter', false);
            $content    = 'This is some text content';
            $result     = static::resolveContent($content, true, false);
            $this->assertTrue($result);
            $this->assertTrue(strpos($content, 'This is some text content') !== false);
            $this->assertTrue(strpos($content, 'PlainTextFooter') !== false);
            $this->assertTrue(strpos($content, '/marketingLists/external/') === false);
        }

        /**
         * @depends testTextContentGetsCustomFooterAppended
         */
        public function testHtmlContentGetsCustomFooterAppended()
        {
            AutoresponderOrCampaignMailFooterContentUtil::setContentByType('RichTextFooter', true);
            $content    = 'This is some html content';
            $result     = static::resolveContent($content, true, true);
            $this->assertTrue($result);
            $this->assertTrue(strpos($content, 'This is some html content') !== false);
            $this->assertTrue(strpos($content, 'RichTextFooter') !== false);
            $this->assertTrue(strpos($content, '/marketingLists/external/') === false);
        }

        protected static function resolveContent(& $content, $tracking = true, $isHtmlContent = true)
        {
            return EmailMessageActivityUtil::resolveContentForTrackingAndFooter($tracking, $content, 1, 'AutoresponderItem',
                                                                                                    1, 1, $isHtmlContent);
        }
    }
?>