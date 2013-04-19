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
     * Mass progress view.
     */
    abstract class MassProgressView extends ProgressView
    {
        /**
         * Integer of how many records were skipped
         * during the mass delete process.
         */
        protected $skipCount;

        protected $params;

        abstract protected function getMessagePrefix();

        abstract protected function getCompleteMessageSuffix();

        abstract protected function getInsufficientPermissionSkipSavingUtil();

        /**
         * Constructs a mass delete progress view specifying the controller as
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
                                    $title,
                                    $skipCount,
                                    $params = null)
        {
            assert('$skipCount == null || is_int($skipCount)');
            $this->skipCount    = $skipCount;
            $this->params       = $params;
            parent::__construct(
                                $controllerId,
                                $moduleId,
                                $model,
                                $totalRecordCount,
                                $start,
                                $pageSize,
                                $page,
                                $refreshActionId,
                                $title);
        }

        protected function getMessage()
        {
            return $this->getMessagePrefix() . "&#160;" . $this->start . "&#160;-&#160;" . $this->getEndSize() .
                        "&#160;" . Zurmo::t('Core', 'of') . "&#160;" . $this->totalRecordCount . "&#160;" .
                        Zurmo::t('Core', 'total') . "&#160;" .
                        Zurmo::t('Core', LabelUtil::getUncapitalizedRecordLabelByCount($this->totalRecordCount));
        }

        protected function getCompleteMessage()
        {
            $successfulCount    = $this->callInsufficientPermissionSkipSavingUtilFunction('resolveSuccessfulCountAgainstSkipCount',
                                                                array($this->totalRecordCount, $this->skipCount));
            $content            = $successfulCount . '&#160;' . LabelUtil::getUncapitalizedRecordLabelByCount($successfulCount)
                                                                . '&#160;' . $this->getCompleteMessageSuffix() . '.';
            if ($this->skipCount > 0)
            {
                $content        .= ZurmoHtml::tag('br') .
                                    $this->callInsufficientPermissionSkipSavingUtilFunction(
                                                                'getSkipCountMessageContentByModelClassName',
                                                                array($this->skipCount, get_class($this->model)));
            }
            return $content;
        }

        protected function onProgressComplete()
        {
            $this->callInsufficientPermissionSkipSavingUtilFunction('clear', array(get_class($this->model)));
        }

        protected function renderFormLinks()
        {
           return ZurmoHtml::tag('div',
                                        array('id' => $this->progressBarId . '-links',  'style' => 'display:none;'),
                                        $this->renderReturnLink()
                                );
        }

        protected function renderReturnLink()
        {
            return ZurmoHtml::link(ZurmoHtml::wrapLabel($this->renderReturnMessage()), $this->renderReturnUrl());
        }

        protected function renderReturnUrl()
        {
            $returnUrl = ArrayUtil::getArrayValue($this->params, 'returnUrl');
            $returnUrl = ($returnUrl)? $returnUrl : Yii::app()->createUrl($this->moduleId);
            return $returnUrl;
        }

        protected function renderReturnMessage()
        {
            $returnMessage = ArrayUtil::getArrayValue($this->params, 'returnMessage');
            $returnMessage = ($returnMessage) ? $returnMessage : Zurmo::t('Core', 'Return to List');
            return $returnMessage;
        }

        protected function getDefaultInsufficientPermissionSkipSavingUtil()
        {
            $util = ArrayUtil::getArrayValue($this->params, 'insufficientPermissionSkipSavingUtil');
            $util = ($util)? $util : $this->getInsufficientPermissionSkipSavingUtil();
            return $util;
        }

        protected function callInsufficientPermissionSkipSavingUtilFunction($function, $parameters = array())
        {
            $util               = $this->getDefaultInsufficientPermissionSkipSavingUtil();
            return call_user_func_array(array($util, $function), $parameters);
        }
    }
?>