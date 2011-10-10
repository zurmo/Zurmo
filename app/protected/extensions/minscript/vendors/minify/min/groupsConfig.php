<?php
    $basePath = Yii::app()->getBasePath();
    require_once($basePath . '/extensions/zurmoinc/framework/adapters/ZurmoExtScriptToMinifyVendorAdapter.php');
    return ZurmoExtScriptToMinifyVendorAdapter::getConfig();
?>