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
     * Helper class to help with user interface manipulation for FileModel related actions.
     */
    class FileModelDisplayUtil
    {
        /**
         * Given a file size in bytes, convert to a human readable form.
         * @param integer $size
         * @return string $content
         */
        public static function convertSizeToHumanReadableAndGet($size)
        {
            assert('is_numeric($size)');
            if ($size == 0)
            {
                return '0';
            }
            if ($size < 1048576)
            {
                return round($size / 1024, 2) . 'KB';
            }
            elseif ($size < 1073741824)
            {
                return round($size / 1048576, 2) . 'MB';
            }
            else
            {
                return round($size / 1073741824, 2) . 'GB';
            }
        }

        public static function renderDownloadLinkContentByRelationModelAndFileModel($model, $fileModel)
        {
            assert('$model instanceof RedBeanModel');
            assert('$fileModel instanceof FileModel');
            $content = null;
            $content .= '<span class="ui-icon ui-icon-document" style="display:inline-block;">';
            $content .= Yii::t('Default', 'Attachment') . '</span>';
            $content .= CHtml::link(
                    Yii::app()->format->text($fileModel->name),
                    Yii::app()->createUrl('zurmo/fileModel/download/',
                        array('modelId' => $model->id,
                              'modelClassName' => get_class($model),
                              'id' => $fileModel->id))
            );
            return $content;
        }
    }
?>