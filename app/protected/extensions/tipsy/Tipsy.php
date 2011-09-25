<?php
/*
 * Tipsy jQuery Extension - jQuery plugin
 * @yiiVersion 1.1.6
 */

/**
 * Description of Tipsy
 * Per the http://onehackoranother.com/projects/jquery/tipsy/
 * @author Kamarul Ariffin Ismail <kamarul.ismail@gmail.com>
 * @version 1.1
 */

class Tipsy extends CWidget
{
  public $items       = array();
  public $htmlOptions = array();

  public $delayIn;
  public $delayOut;
  public $fade;
  public $fallback;
  public $gravity;
  public $html;
  public $offset;
  public $opacity;
  public $title;
  public $trigger;

  private $_baseUrl;

  public function init()
  {
    // GET RESOURCE PATH
        $resources = dirname(__FILE__).DIRECTORY_SEPARATOR.'assets';

        // PUBLISH FILES
    $this->_baseUrl = Yii::app()->assetManager->publish($resources);

  }

  public function run()
  {
    // REGISTER JS SCRIPT
    $cs = Yii::app()->clientScript;
    $cs->registerScriptFile($this->_baseUrl.'/jquery.tipsy.js');

    // REGISTER CSS
    $cs->registerCssFile($this->_baseUrl.'/css/tipsy.css');

    // LOOP THROUGH ITEMS
    $items      = $this->items;
    $scriptList = array();
    foreach($items as $item){
      $params      = array();
      $htmlOptions = (isset($item['htmlOptions'])) ? $item['htmlOptions'] : array();

      if(is_array($item['id']))
      {
        $model     = $item['id']['model'];
        $attribute = $item['id']['attribute'];
        CHtml::resolveNameID($model, $attribute, $htmlOptions);
        $tipsyID = '[name="'.$htmlOptions['name'].'"]';
      }
      else
      {
        $tipsyID = $item['id'];
      }

      // OPTION: delayIn
      if(isset($this->delayIn))
      {
        $params['delayIn'] = $this->delayIn;
      }
      else
      {
        $params['delayIn'] = 50; //DEFAULT
      }

      if(isset($item['delayIn']))
      {
        $params['delayIn'] = $item['delayIn'];
      }

      // OPTION: delayOut
      if(isset($this->delayOut))
      {
        $params['delayOut'] = $this->delayOut;
      }
      else
      {
        $params['delayOut'] = 50; //DEFAULT
      }

      if(isset($item['delayOut']))
      {
        $params['delayOut'] = $item['delayOut'];
      }

      // OPTION: fade
      if(isset($this->fade))
      {
        $params['fade'] = $this->fade;
      }

      if(isset($item['fade']))
      {
        $params['fade'] = $item['fade'];
      }

      // OPTION: fallback
      if(isset($this->fallback))
      {
        $params['fallback'] = $this->fallback;
      }

      if(isset($item['fallback']))
      {
        $params['fallback'] = $item['fallback'];
      }

      // OPTION: gravity
      if(isset($this->gravity))
      {
        $params['gravity'] = $this->gravity;
      }

      if(isset($item['gravity']))
      {
        $params['gravity'] = $item['gravity'];
      }

      // OPTION: html
      if(isset($this->html))
      {
        $params['html'] = $this->html;
      }

      if(isset($item['html']))
      {
        $params['html'] = $item['html'];
      }

      // OPTION: offset
      if(isset($this->offset))
      {
        $params['offset'] = $this->offset;
      }

      if(isset($item['offset']))
      {
        $params['offset'] = $item['offset'];
      }

      // OPTION: opacity
      if(isset($this->opacity))
      {
        $params['opacity'] = $this->opacity;
      }
      else
      {
        $params['opacity'] = '0.8'; //DEFAULT
      }

      if(isset($item['opacity']))
      {
        $params['opacity'] = $item['opacity'];
      }

      // OPTION: title
      if(isset($this->title))
      {
        $params['title'] = $this->title;
      }

      if(isset($item['title']))
      {
        $params['title'] = $item['title'];
      }

      // OPTION: trigger
      if(isset($this->trigger))
      {
        $params['trigger'] = $this->trigger;
      }
      else
      {
        $params['trigger'] = 'hover'; //DEFAULT
      }

      if(isset($item['trigger']))
      {
        $params['trigger'] = $item['trigger'];
      }

      //GENERATE JS CODE
      if(!empty($tipsyID))
      {
        $jsCode = "\$('".$tipsyID."').tipsy(".CJavaScript::encode($params).");";
        $scriptList[] = $jsCode;
      }
    } //END foreach($items as $item)

    if(!empty($scriptList))
    {
      $tipsyID = $this->getId();

      // GENERATE INIT FUNCTION
      $jsCode = "\nfunction initTipsy(){\n".
                "$(\".tipsy-inner, .tipsy\").remove(); \n".
                implode('', $scriptList).
                "\n}\n";
      $cs->registerScript(__CLASS__.'#'.$tipsyID, $jsCode, CClientScript::POS_END);

      // RUN INIT FUNCTION
      $cs->registerScript(__CLASS__.'#'.$tipsyID, "initTipsy();", CClientScript::POS_READY);
    }

  }
}
?>