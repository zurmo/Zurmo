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
     * Mass edit progress view.
     */
    class MassEditProgressView extends ProgressView
    {
        /**
         * Integer of how many records were skipped
         * during the mass edit process.
         */
        protected $skipCount;

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
            return Yii::t('Default', 'Updating') . "&#160;" . $this->start . "-" . $this->getEndSize() . "&#160;" . Yii::t('Default', 'of') . "&#160;" .
            $this->totalRecordCount . "&#160;" . Yii::t('Default', 'total') . "&#160;" .
            Yii::t('Default', LabelUtil::getUncapitalizedRecordLabelByCount($this->totalRecordCount));
        }

        protected function getCompleteMessage()
        {
            $successfulCount = MassEditInsufficientPermissionSkipSavingUtil::resolveSuccessfulCountAgainstSkipCount(
                            $this->totalRecordCount, $this->skipCount);
            $content =  $successfulCount . "&#160;" .
            LabelUtil::getUncapitalizedRecordLabelByCount($successfulCount)
            . "&#160;" . Yii::t('Default', 'updated successfully.');
            if ($this->skipCount > 0)
            {
                $content .= '<br/>' .
                            MassEditInsufficientPermissionSkipSavingUtil::getSkipCountMessageContentByModelClassName(
                                            $this->skipCount, get_class($this->model));
            }
            return $content;
        }

        protected function renderFormLinks()
        {
            $listButton = CHtml::link(Yii::t('Default', 'Return to List'), Yii::app()->createUrl($this->moduleId));
            $content = '<div id="' . $this->progressBarId . '-links" style="display:none;">';
            $content .= $listButton;
            $content .= '</div>';
            return $content;
        }

        protected function onProgressComplete()
        {
            MassEditInsufficientPermissionSkipSavingUtil::clear(get_class($this->model));
        }
    }
?>