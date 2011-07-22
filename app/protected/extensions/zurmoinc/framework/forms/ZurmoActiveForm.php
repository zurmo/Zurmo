<?php
    class ZurmoActiveForm extends CActiveForm
    {
        /**
         * Override to handle relation model error summary information.  This information needs to be parsed properly
         * otherwise it will show up as 'Array' for the error text.
         * @see CActiveForm::errorSummary()
         */
        public function errorSummary($models, $header = null, $footer = null, $htmlOptions = array())
        {
            if(!$this->enableAjaxValidation && !$this->enableClientValidation)
            {
                return ZurmoHtml::errorSummary($models, $header, $footer, $htmlOptions);
            }
            if(!isset($htmlOptions['id']))
            {
                $htmlOptions['id'] = $this->id . '_es_';
            }
            $html = ZurmoHtml::errorSummary($models, $header, $footer, $htmlOptions);
            if($html === '')
            {
                if($header === null)
                {
                    $header = '<p>' . Yii::t('yii','Please fix the following input errors:') . '</p>';
                }
                if(!isset($htmlOptions['class']))
                {
                    $htmlOptions['class'] = CHtml::$errorSummaryCss;
                }
                if(isset($htmlOptions['style']))
                {
                    $htmlOptions['style'] = rtrim($htmlOptions['style'], ';') . ';display:none';
                }
                else
                {
                    $htmlOptions['style'] = 'display:none';
                }
                $html = CHtml::tag('div', $htmlOptions, $header . "\n<ul><li>dummy</li></ul>" . $footer);
            }

            $this->summaryID = $htmlOptions['id'];
            return $html;
        }


    }
?>