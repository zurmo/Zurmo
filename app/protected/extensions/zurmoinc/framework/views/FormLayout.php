<?php
    /**
     * Helper class for rendering form layouts for views.
     */
    abstract class FormLayout
    {
        /**
         * Display all panels at once for a form.
         * @var integer
         */
        const PANELS_DISPLAY_TYPE_ALL    = 1;

        /**
         * Display the first panel, hide all subsequent panels. Add a show more link, that when clicked will show
         * the hidden panels.
         * @var integer
         */
        const PANELS_DISPLAY_TYPE_FIRST  = 2;

        /**
         * Display all panels tabbed.
         * @see http://jqueryui.com/demos/tabs/
         * @var integer
         */
        const PANELS_DISPLAY_TYPE_TABBED = 3;

        /**
         * View metadata with rendered element content instead of element information.
         * @var array
         */
        protected $metadata;

        /**
         * Unique id to be used as a prefix for form elements that require uniqueness across the entire page.
         * @var string
         */
        protected $uniqueId;

        /**
         * Maximum allowed cells per row.
         * @var integer
         */
        protected $maxCellsPerRow;

        /**
         * Error summary content to display if panels are tabbed and there is more than one panel.
         * @var string
         */
        protected $errorSummaryContent;

        /**
         * @param array $metadata
         * @param integer $maxCellsPerRow
         */
        public function __construct($metadata, $maxCellsPerRow, $errorSummaryContent)
        {
            assert('is_array($metadata)');
            assert('is_int($maxCellsPerRow)');
            assert('is_string($errorSummaryContent) || $errorSummaryContent == null');
            $this->metadata            = $metadata;
            $this->maxCellsPerRow      = $maxCellsPerRow;
            $this->errorSummaryContent = $errorSummaryContent;
            $this->uniqueId       = $this->makeUniqueId();
        }

        /**
         * Make a id that will be unique across all rendered content.
         * @return string
         */
        protected static function makeUniqueId()
        {
            return CHtml::ID_PREFIX . CHtml::$count++;
        }

        /**
         * Render a form layout. Override to build form layout content.
         */
        abstract public function render();

        /**
         * Get the maximum column count across all panels from the metadata.
         * @return integer column count.
         */
        protected function getMaximumColumnCountForAllPanels()
        {
            $columnCount = 0;
            foreach ($this->metadata['global']['panels'] as $panel)
            {
                foreach ($panel['rows'] as $row)
                {
                    $tempCount = 0;
                    foreach ($row['cells'] as $cell)
                    {
                        $tempCount++;
                    }
                    if ($tempCount > $columnCount)
                    {
                        $columnCount = $tempCount;
                    }
                }
            }
            return $columnCount;
        }
    }
?>