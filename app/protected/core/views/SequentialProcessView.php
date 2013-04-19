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
            $content  = '<div class="process-container-view">' . "\n";
            $content .= '<h3>' . $this->message . '</h3>';
            $content .= '<span id="' . $this->getProgressBarId() . '-msg"></span>';
            $content .= '</div>';
            $this->registerAjaxScript();
            return $content;
        }

        protected function registerAjaxScript()
        {
            if ($this->nextParams != null)
            {
                $urlParams = array_merge(GetUtil::getData(), array('nextParams' => $this->nextParams));
            }
            else
            {
                $getString = GetUtil::getData();
                unset($getString['nextParams']);
                $urlParams = $getString;
            }
            $urlParams = array_merge($urlParams, array('step' => $this->nextStep));
            $url       = Yii::app()->createUrl($this->route, $urlParams);
            $script = ZurmoHtml::ajax(array(
                    'type' => 'GET',
                    'dataType' => 'html',
                    'url'  => $url,
                    'update' => '#' . $this->containerViewId,
            ));
            $script .= '$(\'#' . $this->getProgressBarId() . '\').progressbar({value: ' . $this->getProgressValue() . '});';
            $script .= '$("#progress-percent").html( Math.ceil(' . $this->getProgressValue() . ') + "&#37;");';
            Yii::app()->clientScript->registerScript('sequentialProcess', $script);
        }

        protected function getProgressValue()
        {
            return $this->completionPercentage;
        }
    }
?>