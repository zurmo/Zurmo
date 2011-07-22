<?php
    /**
     * Override for any functions that need special handling for the zurmo application.
     */
    class ZurmoHtml extends CHtml
    {
        /**
         * Override to handle relation model error summary information.  This information needs to be parsed properly
         * otherwise it will show up as 'Array' for the error text.
         * @see CHtml::errorSummary()
         */
        public static function errorSummary($model, $header = null, $footer = null, $htmlOptions = array())
        {
            $content = '';
            if (!is_array($model))
            {
                $model = array($model);
            }
            if (isset($htmlOptions['firstError']))
            {
                $firstError = $htmlOptions['firstError'];
                unset($htmlOptions['firstError']);
            }
            else
            {
                $firstError=false;
            }
            foreach ($model as $m)
            {
                foreach ($m->getErrors() as $errors)
                {
                    foreach ($errors as $errorOrRelatedError)
                    {
                        if(is_array($errorOrRelatedError))
                        {
                            foreach ($errorOrRelatedError as $relatedError)
                            {
                                if($relatedError != '')
                                {
                                    $content .= "<li>$relatedError</li>\n";
                                }
                            }
                        }
                        elseif($errorOrRelatedError != '')
                        {
                            $content .= "<li>$errorOrRelatedError</li>\n";
                        }
                        if($firstError)
                        {
                            break;
                        }
                    }
                }
            }
            if ($content!=='')
            {
                if ($header===null)
                {
                    $header='<p>' . Yii::t('yii','Please fix the following input errors:') . '</p>';
                }
                if (!isset($htmlOptions['class']))
                {
                    $htmlOptions['class'] = CHtml::$errorSummaryCss;
                }
                return CHtml::tag('div', $htmlOptions, $header."\n<ul>\n$content</ul>" . $footer);
            }
            else
            {
                return '';
            }
        }
    }

?>