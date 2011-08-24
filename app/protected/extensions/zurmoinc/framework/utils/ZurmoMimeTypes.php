<?php
    /**
     * Extra MIME types.
     *
     * This file is an override of the Yii mime types file.  If more are needed, submit them on the zurmo forums and they
     * will be checked into source.  You can also change your apache or php magic.mime file if you have unique ones to add.
     * @see Yii.system.utils.mimeTypes
     */
    $extensions = require(Yii::getPathOfAlias('system.utils.mimeTypes') . '.php'); // Not Coding Standard
    $extensions = array_merge($extensions, array(
        'docx' => 'application/msword',
        'pptx' => 'application/vnd.ms-powerpoint',
        'xlsx' => 'application/vnd.ms-excel',
    ));
    return $extensions;
?>