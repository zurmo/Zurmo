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
     * Ajax rendered view. Base view for rendering the content for each sequential process. Also registers ajax
     * script to process the next sequence.
     * @see SequenceProcessContainerView
     */
    class SequentialProcessView extends ProcessView
    {
        /**
         * Ajax route.
         * @var string
         */
        protected $route;

        /**
         * Next step to call via the ajax processing.
         * @var string
         */
        protected $nextStep;

        /**
         * Array of parameters to be passed to the next step in the sequence.
         * @var array
         */
        protected $nextParams;

        /**
         * Message to display.
         * @var string
         */
        protected $message;

        /**
         *
         * Value 0 to 100 of how far along in the process the sequence is.
         * @var integer
         */
        protected $completionPercentage;

        public function __construct($route, $nextStep, $nextParams, $message, $completionPercentage)
        {
            assert('is_string($route)');
            assert('is_string($nextStep)');
            assert('is_array($nextParams) || $nextParams == null');
            assert('is_string($message)');
            assert('is_int($completionPercentage)');
            $this->route                = $route;
            $this->nextStep             = $nextStep;
            $this->nextParams           = $nextParams;
            $this->message              = $message;
            $this->completionPercentage = $completionPercentage;
        }

        protected function renderContent()
        {
            $content  = '<div class="view-toolbar-container clearfix"><div class="view-toolbar">' . "\n";
            $content .= "<h2><span id='" . $this->getProgressBarId() . "-msg'>" . $this->message . "</span></h2>";
            $content .= '</div></div>';
            $this->registerAjaxScript();
            return $content;
        }

        protected function registerAjaxScript()
        {
            if ($this->nextParams != null)
            {
                $urlParams = array_merge($_GET, array('nextParams' => $this->nextParams));
            }
            else
            {
                $getString = $_GET;
                unset($getString['nextParams']);
                $urlParams = $getString;
            }
            $urlParams = array_merge($urlParams, array('step' => $this->nextStep));
            $url       = Yii::app()->createUrl($this->route, $urlParams);
            $script = CHtml::ajax(array(
                    'type' => 'GET',
                    'dataType' => 'html',
                    'url'  => $url,
                    'update' => '#' . $this->containerViewId,
            ));
            $script .= '$(\'#' . $this->getProgressBarId() . '\').progressbar({value: ' . $this->getProgressValue() . '});';
           Yii::app()->clientScript->registerScript('sequentialProcess', $script);
        }

        protected function getProgressValue()
        {
            return $this->completionPercentage;
        }
    }
?>