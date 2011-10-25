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
     * Helper class for streaming output to the browser prior to the completion of a page request.
     */
    class MessageStreamer
    {
        /**
         * The wrapping template for the flushed message.  Can replace this with javascript if you want the message
         * to populate somewhere else on the page.
         * @var string
         */
        protected $template = "{message}";

        /**
         * Browsers have different requirements for how much must be flushed before it will display it in the browser.
         * @var int in bytes.
         */
        protected $extraRenderBytes = 1024;

        public function __construct($template = null)
        {
            assert('is_string($template) || $template == null');
            if ($template != null)
            {
                $this->template = $template;
            }
        }

        public function setExtraRenderBytes($extraRenderBytes)
        {
            assert('is_int($extraRenderBytes) && $extraRenderBytes >= 0');
            $this->extraRenderBytes = $extraRenderBytes;
        }

        /**
         * Add a message to be streamed.
         * @param string $message
         */
        public function add($message)
        {
            assert('is_string($message) && $message !=""');
            echo strtr($this->template, array('{message}' => $message));
            echo str_repeat(' ', $this->extraRenderBytes);
            flush();
        }

        /**
         * Given a message, output the message to the message stream ignoring the template. Used to output a . for example
         * if you want a stream of dots to indicate progress.
         * @param string $message
         */
        public function addIgnoringTemplate($message)
        {
            assert('is_string($message) && $message !=""');
            echo $message;
            echo str_repeat(' ', $this->extraRenderBytes);
            flush();
        }

        public function setEmptyTemplate()
        {
            $this->template = "";
        }
    }
?>