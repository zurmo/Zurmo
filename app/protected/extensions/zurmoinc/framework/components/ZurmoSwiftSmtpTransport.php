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
     * Adding support for responseLog.  This will allow further troubleshooting when there is a problem
     * sending mail.
     */
    class ZurmoSwiftSmtpTransport extends Swift_SmtpTransport
    {
        /**
         * Stores send response log from server as email is sending.
         * @var array
         */
        protected $responseLog = array();

        public static function newInstance($host = 'localhost', $port = 25, $security = null)
        {
            return new self($host, $port, $security);
        }

        /**
         * (non-PHPdoc)
         * @see Swift_Transport_EsmtpTransport::_doRcptToCommand()
         */
        protected function _doRcptToCommand($address)
        {
            try
            {
                $this->executeCommand(sprintf("RCPT TO: <%s>\r\n", $address), array(250, 251, 252));
            }
            catch (Swift_TransportException $e)
            {
               throw new Swift_TransportException($e->getCode(), $e->getMessage(), $e->getPrevious());
            }
        }

        /**
         * Override to add the response to the log.
         * (non-PHPdoc)
         * @see Swift_Transport_AbstractSmtpTransport::_assertResponseCode()
         */
        protected function _assertResponseCode($response, $wanted)
        {
            $this->responseLog[] = $response;
            parent::_assertResponseCode($response, $wanted);
        }

        /**
         * @return array of data.
         */
        public function getResponseLog()
        {
            return $this->responseLog;
        }
    }
?>
