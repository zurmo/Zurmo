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

    Yii::import('zii.widgets.jui.CJuiWidget');

    /**
     * Class to render a jquery UI MultiSelect widget.
     * @see http://www.erichynds.com/jquery/jquery-ui-multiselect-widget/
     */
    class JuiMultiSelect extends CJuiWidget
    {
        /**
         * Array of the value as the key, and the label as the value in the pairing.
         * @var array
         */
        public $dataAndLabels = array();

        /**
         * Which value if any is already selected.
         * @var string
         */
        public $selectedValue;

        /**
         * Array of options to pass to the multiselect upon initialization.
         * @var array
         */
        public $options       = array();

        /**
         * The multiple select input name
         * @var unknown_type
         */
        public $inputName;

        /**
         * The multiple select input id
         * @var string
         */
        public $inputId;

        /**
         * Base Url of application.  Utilized to help determine the path to the theme folder.
         * @var string
         */
        private $baseUrl;

            /**
        * Initialize the widget.
        */
        public function init()
        {
            $this->themeUrl = Yii::app()->baseUrl . '/themes';
            $this->theme    = Yii::app()->theme->name;
            if ($this->inputId == null)
            {
                $this->inputId = $this->getId() . 'inputId';
            }
            if ($this->inputName == null)
            {
                $this->inputId = $this->getId() . 'inputName';
            }
            if (count($this->dataAndLabels) == 0)
            {
                throw new NotSupportedException();
            }
        }

        /**
         * Run the widget.  Renders the widget content and echos it out.
         */
        public function run()
        {
            $this->registerClientScripts();
            $this->registerCssFile();
            $htmlOptions = array_merge($this->htmlOptions,
                array('id'       => $this->inputId,
                      'multiple' => true,
                      'style'    => 'display:none;'));
            echo CHtml::listBox($this->inputName, $this->selectedValue, $this->dataAndLabels, $htmlOptions);
            if (empty($this->options))
            {
                $options = null;
            }
            else
            {
                 $options = CJavaScript::encode($this->options);
            }
            Yii::app()->getClientScript()->registerScript($this->getId(),
                                                          $this->renderJavaScript($options),
                                                          CClientScript::POS_READY);
        }

        protected function registerClientScripts()
        {
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.widgets.assets')) . '/juiMultiSelect/jquery.multiselect.js');
        }

        protected function registerCssFile()
        {
            Yii::app()->getClientScript()->registerCssFile($this->themeUrl . '/' . $this->theme . '/css/jquery-multiselect.css');
        }

        protected function renderJavaScript($options)
        {
            assert('$options == null || is_string($options)');
            $content = "$('#{$this->inputId}').multiselect({$options}); " .
                       "setupCheckboxStyling($('#{$this->inputId}').parent())";
            return $content;
        }
    }
?>
