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
     * ApiResult
     */
    class ApiResult
    {
        /**
         * Result status.
         * @var string
         */
        public $status;

        /**
        * Data array.
        * @var array
        */
        public $data = array();

        /**
        * Response message.
        * @var string
        */
        public $message = null;

        /**
        * Array of errors that happen during request, for example list of all validation errors during model saving.
        * @var array
        */
        public $errors = null;

        /**
         * Constructor
         * @param string $status
         * @param array $data
         * @param string $message
         * @param array $errors
         */
        public function __construct($status, $data, $message = null, $errors = null)
        {
            $this->status   = $status;
            $this->data     = $data;
            $this->message  = $message;
            $this->errors   = $errors;
        }

        /**
         * Is result status sucessful or not.
         * @return boolean
         */
        public function isStatusSuccess()
        {
            if (isset($this->status) && $this->status == ApiResponse::STATUS_SUCCESS)
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        /**
         * Convert ApiResult object into array.
         * @return array
         */
        public function convertToArray()
        {
            $result = array(
                'status'  => $this->status,
                'data'    => $this->data,
                'message' => $this->message,
                'errors'  => $this->errors,
            );
            return $result;
        }
    }
?>
