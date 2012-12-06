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

    class AmChartMaker
    {
        public  $type                   = null;

        public  $data                   = null;

        public  $height                 = 300;

        public  $valueField             = 'value';

        public  $categoryField          = 'displayLabel';

        public  $chartIs3d              = false;

        public  $chartIsPie             = false;

        public  $xAxisName              = null;

        public  $yAxisName              = null;

        private $serial                 = array();
        private $chartProperties        = array();

        private $graphProperties        = array();

        private $valueAxisProperties    = array();

        private $categoryAxisProperties = array();

        /**
         * Returns the type of chart to be used in AmChart
         */
        private function addChartPropertiesByType()
        {
            $colorTheme = array(
                            1 => '["#262877", "#6625A7", "#BC9DDA", "#817149", "#A77425"]',
                            2 => '["#262877", "#7BB730"]',
                            3 => '["#262877", "#3E44C3", "#585A8E", "#777AC1", "#151741", "#7BB730"]',
                            4 => '["#2a7a8c", "#176273", "#063540", "#e5d9cf", "#403d3a", "#262877", "#3e42c3", "#58598e",
                                   "#797bc3", "#161744", "#00261c", "#044c29", "#167f39", "#45bf55", "#96ed89", "#007828",
                                   "#075220", "#1d9e48", "#375d3b", "#183128", "#012426", "#027353", "#1c2640", "#263357",
                                   "#384c80", "#4e6ab2", "#5979cd"]',
                            5 => '["#262877", "#5979cd"]',
                );
            $this->addChartProperties('fontFamily',                 '"Arial"');
            $this->addChartProperties('color',                      "'#545454'");
            $this->addChartProperties('lineColor',                  '"#545454"');
            $this->addValueAxisProperties('axisColor',              '"#545454"');
            $this->addValueAxisProperties('gridColor',              '"#545454"');
            $this->addChartProperties('colors', $colorTheme[4]);
            if ($this->type === "Column2D")
            {
                $currencySymbol = Yii::app()->locale->getCurrencySymbol(Yii::app()->currencyHelper->getCodeForCurrentUserForDisplay());
                //Chart
                $this->addChartProperties('usePrefixes',            true);
                $this->addChartProperties('plotAreaBorderColor',    "'#000000'");
                $this->addChartProperties('plotAreaBorderAlpha',    0);
                //Graph
                $this->addGraphProperties('fillAlphas',             1);
                $this->addGraphProperties('cornerRadiusTop',        0);
                $this->addGraphProperties('cornerRadiusBottom',     0);
                $this->addGraphProperties('lineAlpha',              0);
                $this->addGraphProperties('fillColors',             $colorTheme[5]);
                //Axis
                $this->addCategoryAxisProperties('title',           "'{$this->xAxisName}'");
                $this->addCategoryAxisProperties('inside',          0);
                $this->addCategoryAxisProperties('fillColors',      $colorTheme[5]);
                //ValueAxis
                $this->addValueAxisProperties('title',              "'$this->yAxisName'");
                $this->addValueAxisProperties('minimum',            0);
                $this->addValueAxisProperties('dashLength',         2);
                $this->addValueAxisProperties('usePrefixes',        1);
                $this->addValueAxisProperties('unitPosition',       "'left'");
                $this->addValueAxisProperties('unit',               "'{$currencySymbol}'");
            }
            elseif ($this->type === "Column3D")
            {
                $this->addGraphProperties('balloonText',            "'[[category]]:[[value]]'");
                $this->addGraphProperties('lineAlpha',              0.5);
                $this->addGraphProperties('fillAlphas',             1);
                $this->addGraphProperties('fillColors',             $colorTheme[5]);
                $this->makeChart3d();
            }
            elseif ($this->type === "Bar2D")
            {
                $this->addChartProperties('rotate',                 true);
                $this->addChartProperties('usePrefixes',            true);
                $this->addGraphProperties('plotAreaBorderAlpha',    0);
                $this->addGraphProperties('lineAlpha',              0);
                $this->addGraphProperties('fillAlphas',             1);
                $this->addGraphProperties('fillColors',             $colorTheme[5]);
                $this->addGraphProperties('gradientOrientation',    "'vertical'");
                $this->addGraphProperties('labelPosition',          "'right'");
                $this->addGraphProperties('labelText',              "'[[category]]: [[value]]'");
                $this->addGraphProperties('balloonText',            "'[[category]]: [[value]]'");
            }
            elseif ($this->type === "Donut2D")
            {
                $this->addChartProperties('color',                  "'#A39595'");
                $this->addChartProperties('sequencedAnimation',     true);
                $this->addChartProperties('startEffect',            "'elastic'");
                $this->addChartProperties('innerRadius',            "'30%'");
                $this->addChartProperties('startDuration',          2);
                $this->addChartProperties('labelRadius',            15);
                $this->addChartProperties('usePrefixes',            true);
                $this->addChartProperties('radius',                 "'45%'");
                $this->addChartProperties('labelRadius',            -55);
                $this->addChartProperties('labelText',              "'[[title]]<br>[[percents]]%'");
                $this->addChartProperties('pullOutRadius',          "'0%'");
                $this->addChartProperties('startDuration',          0);
                $this->chartIsPie = true;
            }
            elseif ($this->type === "Pie2D")
            {
                $this->addChartProperties('color',                  "'#A39595'");
                $this->addChartProperties('outlineColor',           "'#FFFFFF'");
                $this->addChartProperties('outlineAlpha',           0.8);
                $this->addChartProperties('outlineThickness',       2);
                $this->addChartProperties('usePrefixes',            true);
                $this->addChartProperties('radius',                 "'45%'");
                $this->addChartProperties('labelRadius',            -55);
                $this->addChartProperties('labelText',              "'[[title]]<br>[[percents]]%'");
                $this->addChartProperties('labelTickColor',         "'#000000'");
                $this->addChartProperties('pullOutRadius',          "'0%'");
                $this->addChartProperties('startDuration',          0);
                $this->chartIsPie = true;
            }
            elseif ($this->type === "Pie3D")
            {
                $this->addChartProperties('color',                  "'#A39595'");
                $this->addChartProperties('outlineColor',           "'#FFFFFF'");
                $this->addChartProperties('outlineAlpha',           0.8);
                $this->addChartProperties('outlineThickness',       2);
                $this->addChartProperties('usePrefixes',            true);
                $this->addChartProperties('radius',                 "'45%'");
                $this->addChartProperties('labelRadius',            -55);
                $this->addChartProperties('labelText',              "'[[title]]<br>[[percents]]%'");
                $this->addChartProperties('labelTickColor',         "'#FFFFFF'");
                $this->addChartProperties('pullOutRadius',          "'0%'");
                $this->addChartProperties('startDuration',          0);
                $this->makeChart3d();
                $this->chartIsPie = true;
            }
            else
            {
                //Default graph
            }
        }

        private function convertDataArrayToJavascriptArray()
        {
            return CJavaScript::encode($this->data);
        }

        public function makeChart3d()
        {
            $this->addChartProperties('depth3D',                    15);
            $this->addChartProperties('angle',                      30);
            $this->chartIs3d = true;
        }

        /**
         * Add Serial Graph to SerialChart
         * $valuefield: string
         * $type: string (column, line)
         */
        public function addSerialGraph($valueField, $type, $options = array())
        {
            array_push($this->serial, array(
                                        'valueField'    =>  $valueField,
                                        'type'          =>  $type,
                                        'options'       =>  $options));
        }

        /**
         *  Add properties to chart
         *  Info on http://www.amcharts.com/docs/v.2/javascript_reference/amchart
         */
        public function addChartProperties($tag, $value)
        {
            $this->chartProperties[$tag]        = $value;
        }

        /**
         * Add properties to valueAxis
         * Info on info on http://www.amcharts.com/docs/v.2/javascript_reference/axisbase
         */
        public function addValueAxisProperties($tag, $value)
        {
            $this->valueAxisProperties[$tag]    = $value;
        }

        /**
         * Add properties to categoryAxis
         * Info on http://www.amcharts.com/docs/v.2/javascript_reference/axisbase
         */
        public function addCategoryAxisProperties($tag, $value)
        {
            $this->categoryAxisProperties[$tag] = $value;
        }

        /**
         * Add properties to Serial Graph - column or bar properties
         * Info on http://www.amcharts.com/docs/v.2/javascript_reference/amgraph
         */
        public function addGraphProperties($tag, $value)
        {
           $this->graphProperties[$tag]         = $value;
        }

        public function javascriptChart()
        {
            //Init AmCharts
            $this->addChartPropertiesByType();
            $javascript  = "var chartData_{$this->id} = ". $this->convertDataArrayToJavascriptArray() . ";";
            $javascript .=" $(document).ready(function () {     ";
            //Make chart Pie or Serial
            if ($this->chartIsPie)
            {
                $javascript .="
                   var chart          = new AmCharts.AmPieChart();
                   chart.dataProvider = chartData_{$this->id};
                   chart.titleField   = '{$this->categoryField}';
                   chart.valueField   = '". $this->valueField . "';";
            }
            else
            {
                //Init the AmSerialGraph
                $javascript .="
                        var chart           = new AmCharts.AmSerialChart();
                        chart.dataProvider  = chartData_{$this->id};
                        chart.categoryField = '{$this->categoryField}';
                ";
            }
            //Add chart properties
            foreach ($this->chartProperties as $tag => $chartProperty)
            {
                $javascript .= "chart." . $tag . " = " . $chartProperty . ";";
            }

            if (!$this->chartIsPie)
            {
                //Add serial as graph
                foreach ($this->serial as $key => $serial)
                {
                    $javascript  .= "var graph{$key}        = new AmCharts.AmGraph();
                                     window.g1              = graph{$key};
                                     graph{$key}.valueField = '". $serial['valueField'] ."';
                                     graph{$key}.type       = '" . $serial['type'] .  "';";
                    if(count($serial['options']) == 0)
                    {
                        //Add graph properties from GraphType
                        foreach($this->graphProperties as $graphTag => $graphOption)
                        {
                            $javascript .= "graph{$key}." . $graphTag . " = " . $graphOption . ";";
                        }
                    }
                    else
                    {
                        //Add graph properties from option passed
                        foreach($serial['options'] as $graphTag => $graphOption)
                        {
                            $javascript .= "graph{$key}." . $graphTag . " = " . $graphOption . ";";
                        }
                    }
                    $javascript .= "chart.addGraph(graph{$key});";
                }
                //Add categoryAxis properties from GraphType
                $javascript .= "var categoryAxis = chart.categoryAxis;";
                foreach($this->categoryAxisProperties as $tag => $option)
                {
                    $javascript .= "categoryAxis." . $tag . " = " . $option . ";";
                }
                //Add valueAxis properties from GraphType
                $javascript .= "var valueAxis = new AmCharts.ValueAxis();";
                foreach($this->valueAxisProperties as $tag => $option)
                {
                    $javascript .= "valueAxis." . $tag . " = " . $option . ";";
                }
                $javascript .= "chart.addValueAxis(valueAxis);";
            }
            //Write chart
            $javascript .= "chart.write('chartContainer{$this->id}');
                     });";
            return $javascript;
        }
    }
?>