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

    /**
     * Test class to test the ZurmoMessageSourceUtil class.
     */
    class ZurmoMessageSourceUtilTest extends BaseTest
    {
        protected static $testLanguageCode = 'de';

        protected static $testCategory = 'UtilTest';

        protected static $testMessageSource = 'messageUtilOne-source';

        protected static $testMessageNewTranslation = 'messageUtilOne-translation';

        protected static $testMessageUpdatedTranslation = 'messageUtilOne-updatedTranslation';

        protected static $testMessagesNew = array(
                    'messageUtil1-source' => 'messageUtil1-translation',
                    'messageUtil2-source' => 'messageUtil2-translation',
                    'messageUtil3-source' => 'messageUtil3-translation',
                    'messageUtil4-source' => 'messageUtil4-translation',
                    'messageUtil5-source' => 'messageUtil5-translation',
                    'messageUtil6-source' => 'messageUtil6-translation'
        );

        protected static $testMessagesUpdated = array(
                    'messageUtil1-source' => 'messageUtil1-updatedTranslation',
                    'messageUtil2-source' => 'messageUtil2-updatedTranslation',
                    'messageUtil3-source' => 'messageUtil3-updatedTranslation',
                    'messageUtil4-source' => 'messageUtil4-updatedTranslation',
                    'messageUtil5-source' => 'messageUtil5-updatedTranslation',
                    'messageUtil6-source' => 'messageUtil6-updatedTranslation'
        );

        public function testImportOneMessageNew()
        {
            ZurmoMessageSourceUtil::importOneMessage(
                                                self::$testLanguageCode,
                                                self::$testCategory,
                                                self::$testMessageSource,
                                                self::$testMessageNewTranslation
            );

            $messageSource = new ZurmoMessageSource();

            $translation = $messageSource->translate(
                                                     self::$testCategory,
                                                     self::$testMessageSource,
                                                     self::$testLanguageCode
                                                     );

            $this->assertEquals($translation, self::$testMessageNewTranslation);
        }

        /**
         * @depends testImportOneMessageNew
         */
        public function testImportOneMessageUpdated()
        {
            ZurmoMessageSourceUtil::importOneMessage(
                                            self::$testLanguageCode,
                                            self::$testCategory,
                                            self::$testMessageSource,
                                            self::$testMessageUpdatedTranslation
            );

            $messageSource = new ZurmoMessageSource();

            $translation = $messageSource->translate(
                                                     self::$testCategory,
                                                     self::$testMessageSource,
                                                     self::$testLanguageCode
                                                     );

            $this->assertEquals($translation, self::$testMessageUpdatedTranslation);
        }

        /**
         * @depends testImportOneMessageUpdated
         */
        public function testImportMessagesArrayNew()
        {
            ZurmoMessageSourceUtil::importMessagesArray(
                                                        self::$testLanguageCode,
                                                        self::$testCategory,
                                                        self::$testMessagesNew
                                                        );

            $messageSource = new ZurmoMessageSource();

            foreach (self::$testMessagesNew as $source => $compareTranslation)
            {
                $translation = $messageSource->translate(
                                                         self::$testCategory,
                                                         $source,
                                                         self::$testLanguageCode
                                                         );
                $this->assertEquals($translation, $compareTranslation);
            }
        }

        /**
         * @depends testImportMessagesArrayNew
         */
        public function testImportMessagesArrayUpdated()
        {
            ZurmoMessageSourceUtil::importMessagesArray(
                                                        self::$testLanguageCode,
                                                        self::$testCategory,
                                                        self::$testMessagesUpdated
                                                        );

            $messageSource = new ZurmoMessageSource();

            foreach (self::$testMessagesUpdated as $source => $compareTranslation)
            {
                $translation = $messageSource->translate(
                                                         self::$testCategory,
                                                         $source,
                                                         self::$testLanguageCode
                                                         );
                $this->assertEquals($translation, $compareTranslation);
            }
        }

        public function testImportPoFile()
        {
            $testLanguageCode = 'po';

            $pathToFiles = Yii::getPathOfAlias('application.tests.unit.files');
            $filePath = $pathToFiles . DIRECTORY_SEPARATOR . 'messages-test.po';

            ZurmoMessageSourceUtil::importPoFile($testLanguageCode, $filePath);

            $file = new ZurmoGettextPoFile($filePath);
            $messages = $file->read($filePath);

            $messageSource = new ZurmoMessageSource();

            foreach ($messages as $message)
            {
                $translation = $messageSource->translate(
                    $message['msgctxt'],
                    $message['msgid'],
                    $testLanguageCode
                );

                $this->assertEquals($translation, $message['msgstr']);
            }
        }
    }
?>
