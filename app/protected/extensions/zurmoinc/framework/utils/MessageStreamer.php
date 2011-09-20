<?php
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

        public function setEmptyTemplate()
        {
            $this->template = "";
        }
    }
?>