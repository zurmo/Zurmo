<?php

/**
 * Tooltip behavior. Use static method :
 * <code>
 * // all html items corresponding to the selector (input elements inside
 * // elements of class .row and containing title attribute)
 * // will have their tite attribute used to create tooltip content.
 * QTip::qtip('.row input[title]');
 * </code>
 *
 * Or instance method. For example, create a qtip and add it to a widget :
 * $qtip = new QTip();
 * $qtip->addQTip($widget);
 *
 * @author parcouss
 */

class QTip extends CComponent {
    /**
     * @brief retrieve the script file name
     * @param minify bool true to get the minified version
     */
    protected static function scriptName($minify) {
        $ext = $minify ? '.min.js' : '.js';
        return 'jquery.qtip-1.0.0-rc3'.$ext;
    }

    /**
     * @brief register core and qtip js needed files
     * @param scriptName string the qtip file name
     */
    protected static function registerScript($scriptName) {
        $cs = Yii::app()->clientScript;
        $cs->registerCoreScript('jquery');
        $assets = Yii::app()->extensionPath. DIRECTORY_SEPARATOR.'qtip'.DIRECTORY_SEPARATOR.'assets';
        $aUrl = Yii::app()->getAssetManager()->publish($assets);
        $cs->registerScriptFile($aUrl.'/'.$scriptName);
    }

    /**
     * @brief register the qtip js code needed to apply tooltip
     * @param jsSelector string the selector jquery to select html element(s) to apply tooltips
     * @param options array) the qtip js options
     * @param minify bool true to select the minified js script
     */
    public static function qtip2($jsSelector, $options = array(), $minify = true) {
        if (! empty($minify)) self::registerScript(self::scriptName($minify));

        Yii::app()->clientScript->registerScript(__CLASS__.$jsSelector, '$("'.$jsSelector.'").qtip('.CJavaScript::encode($options).');');
    }

    public $minify = true; // true to select the minified js script

    public $options = array(); // array general qtip js options

    public function __construct($params = array()) {
        foreach ($params as $p => $val) $this->$p = $val;
    }

    /**
     * @brief instance method to apply qtip on a widget or on any html item. can override general options.
     * @param widgetOrSelector mixed  a widget instance or a jquery selector
     * @param specific_opts specific options to pass to qtip javascript code
     */
    public function addQTip($widgetOrSelector, $specific_opts = array()) {
        $jsSelector = is_string($widgetOrSelector) ?  $widgetOrSelector : '#'.$widgetOrSelector->id;
        self::qtip2($jsSelector, array_merge($this->options, $specific_opts), $this->minify);
    }
}
