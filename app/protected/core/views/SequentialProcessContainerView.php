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
     * A sequential process needs a container view to start with.  The container view renders the progress bar
     * and a message that displays through the entire sequence.  The sequence process view gets rendered via
     * the ajax calls and updates the div in the process view with new content.
     * @see SequenceProcessView.
     */
    class SequentialProcessContainerView extends ProcessView
    {
        /**
         * This is the intial SequenceProcessView that will be rendered.
         * @var object SequenceProcessView
         */
        protected $containedView;

        /**
         * A message that will be displayed throughout the entire sequence.
         * @var string
         */
        protected $allStepsMessage;

        protected $title;

        public function __construct($containedView, $allStepsMessage, $title = null)
        {
            assert('$containedView instanceof SequentialProcessView');
            assert('is_string($allStepsMessage)');
            assert('is_string($title) || $title == null');
            $this->containedView   = $containedView;
            $this->allStepsMessage = $allStepsMessage;
            $this->title           = $title;
        }

        protected function renderContent()
        {
            $content  = '<div>';
            $content .= $this->renderTitleContent();
            $content .= '<div class="process-container-view">';
            $content .= "<h3>" . $this->allStepsMessage . '</h3>';
            $content .= '<div class="progressbar-wrapper"><span id="progress-percent">0&#37;</span>' .
                        $this->renderProgressBarContent() . '</div>';
            $content .= '</div>';
            $content .= '<div id="' . $this->containerViewId . '" class="process-container-view">';
            $content .= $this->containedView->render();
            $content .= '</div></div>';
            return $content;
        }

        protected function renderProgressBarContent()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ProgressBar");
            $cClipWidget->widget('zii.widgets.jui.CJuiProgressBar', array(
                'id'         => $this->getProgressBarId(),
                'value'      => 0,
            ));
            $cClipWidget->endClip();
            return  $cClipWidget->getController()->clips['ProgressBar'];
        }
    }
?>