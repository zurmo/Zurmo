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
     * Progress view is an abstraction used for processes
     * that occur in phases. This includes import, export, and mass edit
     * as examples.  Allows for an automatic loop of server calls using Ajax
     * until a process is complete.
     */
    abstract class ProgressView extends View
    {
        /**
         * How many total records need to be processed in the batch
         */
        protected $totalRecordCount;

        /**
         * How many records to process per ajax call.
         */
        protected $pageSize;

        /**
         * Unique identifier for the progress bar widget that is displayed
         */
        protected $progressBarId;

        /**
         * Constructs a mass edit progress view specifying the controller as
         * well as the model that will have its mass edit displayed.
         */
        public function __construct(
        $controllerId,
        $moduleId,
        $model,
        $totalRecordCount,
        $start,
        $pageSize,
        $page,
        $refreshActionId,
        $title)
        {
            $this->controllerId        = $controllerId;
            $this->moduleId            = $moduleId;
            $this->model               = $model;
            $this->totalRecordCount    = $totalRecordCount;
            $this->start               = $start;
            $this->pageSize            = $pageSize;
            $this->page                = $page;
            $this->refreshActionId     = $refreshActionId;
            $this->progressBarId       = 'progressBar';
            $this->title               = $title;
        }

        /**
         * Returns JSON encoded script utilized on AJAX
         * call by ProgressView
         * Return has three elements in the array
         * callback, value, and message
         */
        public function renderRefreshJSONScript()
        {
            return CJSON::encode($this->renderRefresScript());
        }

        protected function renderRefresScript()
        {
            $value = $this->getProgressValue();
            if ($value < 100)
            {
                $callback = $this->getCreateProgressBarAjax($this->progressBarId);
                $message  = $this->getMessage();
            }
            else
            {
                $callback = null;
                $this->onProgressComplete();
                $message  = $this->getCompleteMessage();
            }

            $data = array(

                'callback' => $callback,
                'value'    => $value,
                'message'  => $message,
            );
            return $data;
        }

        protected function renderContent()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ProgressBar");
            $cClipWidget->widget('zii.widgets.jui.CJuiProgressBar', array(
                'id'         => $this->progressBarId,
                'value'      => $this->getProgressValue(),
                'options'    => array(
                    'create' => 'js:function(event, ui)
                    {
                        ' . $this->getCreateProgressBarAjax($this->progressBarId) . '
                    }',
                    'complete' => 'js:function(event, ui)
                    {
                        $(\'#' . $this->progressBarId . '\').hide();
                        $(\'#' . $this->progressBarId . '-links\').show();
                    }',
                ),
                'htmlOptions' => array(
                    'style'   => 'height:20px;'
                ),
            ));
            $cClipWidget->endClip();
            $progressBarContent =  $cClipWidget->getController()->clips['ProgressBar'];
            $content = "<h1>" . Yii::t('Default', 'Mass Update') . '&#160;' . $this->title . '</h1>';
            $content .= '<div class="view-toolbar-container clearfix"><div class="view-toolbar">' . "\n";
            $content .= "<h2><span id='" . $this->progressBarId . "-msg'>" . $this->getMessage() . "</span></h2>";
            $content .= $progressBarContent;
            $content .= '</div></div>';
            $content .= $this->renderFormLinks();
            return $content;
        }

        protected function getCreateProgressBarAjax($progressBarId)
        {
            return CHtml::ajax(array(
                    'type' => 'POST',
                    'dataType' => 'json',
                    'data' => yii::app()->getUrlManager()->createPathInfo($_POST, '=', '&'),
                    'url'  => yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/' . $this->refreshActionId,
                        array_merge($_GET, array( get_class($this->model) . '_page' => ($this->page + 1)))
                    ),
                    'success' => 'function(data)
                    {
                        $(\'#' . $progressBarId . '-msg\').html(data.message);
                        $(\'#' . $progressBarId . '\').progressbar({value: data.value});
                        eval(data.callback);
                    }',
                ));
        }

        protected function getProgressValue()
        {
            $value = ($this->getEndSize() / $this->totalRecordCount) * 100;
            if ($value >= 100)
            {
                return 100;
            }
            else
            {
                return $value;
            }
        }

        protected function getEndSize()
        {
            $end = $this->start + $this->pageSize - 1;
            if ($end > $this->totalRecordCount)
            {
                return $this->totalRecordCount;
            }
            return $end;
        }

        /**
         * Override if you have a specific action to perform
         * when the progress is completed.
         */
        protected function onProgressComplete()
        {
        }
    }
?>