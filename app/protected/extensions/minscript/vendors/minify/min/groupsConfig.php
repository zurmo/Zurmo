<?php
    $basePath = Yii::app()->getBasePath();
    require_once($basePath . '/core/adapters/ZurmoExtScriptToMinifyVendorAdapter.php');
    return ZurmoExtScriptToMinifyVendorAdapter::getConfig();
?>