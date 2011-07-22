<?php
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
            if($size == 0)
            {
                return '0';
            }
            if($size < 1048576)
            {
                return round($size / 1024, 2) . 'Kb';
            }
            elseif($size < 1073741824)
            {
                return round($size / 1048576, 2) . 'Mb';
            }
            else
            {
                return round($size / 1073741824, 2) . 'Gb';
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