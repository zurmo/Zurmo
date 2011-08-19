<?php
// This is the configuration for zurmoc console application.
// Any writable CConsoleApplication properties can be configured here.
    $common_config = CMap::mergeArray(
        require('main.php'),
        array(
            'basePath' => dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
            'name' => 'Zurmo Console Application',
        )
    );
    //Utilize a custom begin request behavior class.
    $common_config['behaviors']['onBeginRequest'] = array(
        'class' => 'application.modules.zurmo.components.CommandBeginRequestBehavior'
    );
    //Not applicable for console applications.
    unset($common_config['defaultController']);
    //Not applicable for console applications.
    unset($common_config['theme']);
    return $common_config;
?>