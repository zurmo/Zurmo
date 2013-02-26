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
     * Mass delete progress view.
     */
    class MassDeleteProgressView extends ProgressView
    {
        /**
         * Integer of how many records were skipped
         * during the mass delete process.
         */
        protected $skipCount;

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
        $skipCount)
        {
            assert('$skipCount == null || is_int($skipCount)');
            $this->skipCount = $skipCount;
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
            return Zurmo::t('Core', 'Deleting') . " " . $this->start . " - " . $this->getEndSize() . " " . Zurmo::t('Core', 'of') . " " .
                $this->totalRecordCount . " " . Zurmo::t('Core', 'total') . " " .
                Zurmo::t('Core', LabelUtil::getUncapitalizedRecordLabelByCount($this->totalRecordCount));
        }

        protected function getCompleteMessage()
        {
            $successfulCount = MassDeleteInsufficientPermissionSkipSavingUtil::resolveSuccessfulCountAgainstSkipCount(
                               $this->totalRecordCount, $this->skipCount);
            $content =         $successfulCount . ' ' . LabelUtil::getUncapitalizedRecordLabelByCount($successfulCount)
                               . ' ' . Zurmo::t('Core', 'successfully deleted') . '.';
            if ($this->skipCount > 0)
            {
                $content .= '<br/>' .
                            MassDeleteInsufficientPermissionSkipSavingUtil::getSkipCountMessageContentByModelClassName(
                                            $this->skipCount, get_class($this->model));
            }
            return $content;
        }

        protected function renderFormLinks()
        {
            $listButton = ZurmoHtml::link(ZurmoHtml::wrapLabel(Zurmo::t('Core', 'Return to List')), Yii::app()->createUrl($this->moduleId));
            $content = '<div id="' . $this->progressBarId . '-links" style="display:none;">';
            $content .= $listButton;
            $content .= '</div>';
            return $content;
        }

        protected function onProgressComplete()
        {
            MassDeleteInsufficientPermissionSkipSavingUtil::clear(get_class($this->model));
        }

        protected function headerLabelPrefixContent()
        {
            return Zurmo::t('Core', 'Mass Update');
        }
    }
?>