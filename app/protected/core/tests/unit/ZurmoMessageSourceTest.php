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

    /**
     * Test class to test the ZurmoMessageSource class.
     */
    class ZurmoMessageSourceTest extends BaseTest
    {
        protected static $testLanguageCode = 'de';

        protected static $testCategory = 'test';

        protected static $testMessages = array(
                                    'message1-source' => 'message1-translation',
                                    'message2-source' => 'message2-translation',
                                    'message3-source' => 'message3-translation',
                                    'message4-source' => 'message4-translation',
                                    'message5-source' => 'message5-translation',
                                    'message6-source' => 'message6-translation'
        );

        public static function setupBeforeClass()
        {
            parent::setUpBeforeClass();

            foreach (self::$testMessages as $source => $translation)
            {
                $sourceModel = MessageSource::addNewSource(
                                                           self::$testCategory,
                                                           $source
                                                           );
                MessageTranslation::addNewTranslation(
                                                      self::$testLanguageCode,
                                                      $sourceModel,
                                                      $translation
                                                      );
            }
        }

        public function testLoadMessagesFromDb()
        {
            $messageSource = new ZurmoMessageSource();

            foreach (self::$testMessages as $source => $compareTranslation)
            {
                $translation = $messageSource->translate(
                                                         self::$testCategory,
                                                         $source,
                                                         self::$testLanguageCode
                                                         );
                $this->assertEquals($translation, $compareTranslation);
            }
        }
    }
?>
