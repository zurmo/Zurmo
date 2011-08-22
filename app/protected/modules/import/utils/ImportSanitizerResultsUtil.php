<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * Helper class for working resulting messages from sanitizing a values in a row.
     */
    class ImportSanitizerResultsUtil
    {
        /**
         * Messages generated through sanitizing the row data.
         * @var unknown_type
         */
        private $messages;

        /**
         * Some sanitization routines, if they run into an error, means the entire row should be skipped for
         * making or updating a model.  Some sanitization does not require the entire row to be skipped, just the value.
         * If the row is required to be skipped, this value should be set to false @see setModelShouldNotBeSaved()
         * @var boolean
         */
        private $saveModel = true;

        /**
         * @see $saveModel
         */
        public function setModelShouldNotBeSaved()
        {
            $this->saveModel = false;
        }

        /**
         * Given a message, add it to the messages collection.
         * @param string $message
         */
        public function addMessage($message)
        {
            assert('is_string($message');
            $this->messages[] = $message;

        }

        /**
         * @return An array of messages.
         */
        public function getMessages()
        {
            return $this->messages;
        }

        /**
         * @return true/false if the model should be saved or skipped.
         */
        public function shouldSaveModel()
        {
            return $this->saveModel;
        }
    }
?>