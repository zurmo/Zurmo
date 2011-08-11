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
     * Base class for attribute data analyzers.  Data analyzers can inspect a value and based on rules, decide whether
     * that value is valid or invalid.  This information is then utilized to help users during the import process
     * ensure their import data is correct before finalizing the import.
     */
    class AttributeValueDataAnalyzer
    {
        /**
         * Indicates an invalid value.
         * @var string
         */
        const INVALID       = 'Invalid';

        /**
         * Model class name for the attribute or attribute names passed into the analyzer.
         * @var string
         */
        protected $modelClassName;

        /**
         * Typically this is just an array of one attribute name, however with derived types, it is possible that more
         * than one attribute name will be pased through.
         * @var array
         */
        protected $attributeNameOrNames;

        /**
         * Array of various counts of data. An example is a sub-element of the array storing a count of how many
         * invalid values.
         * @var array
         */
        protected $messageCountData;

        /**
         * Array of messages.
         * @var array
         */
        private   $messages;

        /**
         * Array of instructional data that is generated after the data is analyzed
         * @var array
         */
        private   $instructionsData;

        public function __construct($modelClassName, $attributeNameOrNames)
        {
            assert('is_string($modelClassName)');
            assert('is_array($attributeNameOrNames) || is_string($attributeNameOrNames)');
            $this->modelClassName       = $modelClassName;
            $this->attributeNameOrNames = $attributeNameOrNames;
            $this->messageCountData[static::INVALID] = 0;
        }

        /**
         * @return true/false if the analyzer supports offering additional result information. Some analyzers will
         * offer additional options after analysis.  An example is a drop down, where a user can decide whether to map
         * all the missing dropdowns or do something else, like merge some together with existing ones.
         */
        public static function supportsAdditionalResultInformation()
        {
            return false;
        }

        /**
         * Add a message.
         * @param string $message
         */
        protected function addMessage($message)
        {
            $this->messages[] = $message;
        }

        /**
         * @return array of messages if available. Otherwise null is returned.
         */
        public function getMessages()
        {
            return $this->messages;
        }

        /**
         * Set the instructional data. If the data is already set for this analyzer an exception is thrown because
         * each analyzer can only have one set of instructional data.
         * @param array $instructionsData
         */
        public function setInstructionsData($instructionsData)
        {
            assert('$instructionsData != null');
            if($this->instructionsData != null)
            {
                throw new NotSupportedException();
            }
            $this->instructionsData = $instructionsData;
        }

        /**
         * @return array of instructional data if available. Otherwise null is returned.
         */
        public function getInstructionsData()
        {
            return $this->instructionsData;
        }
    }
?>