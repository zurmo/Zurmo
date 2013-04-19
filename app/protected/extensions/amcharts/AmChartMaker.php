<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class AmChartMaker
    {
        public  $type                   = null;

        public  $data                   = null;

        public  $height                 = 300;

        public  $categoryField          = 'displayLabel';

        public  $chartIs3d              = false;

        public  $chartIsPie             = false;

        public  $xAxisName              = null;

        public  $xAxisUnitContent       = null;

        public  $yAxisName              = null;

        public  $yAxisUnitContent       = null;

        private $valueField             = 'value';

        private $serial                 = array();

        private $chartProperties        = array();

        private $graphProperties        = array();

        private $valueAxisProperties    = array();

        private $categoryAxisProperties = array();

        private $legendProperties       = array();

        private $barColorTheme          = array();

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
                            6 => '["#6C8092", "#933140", "#447799", "#44BBCC", "#4A3970", "#91A1DC",
                                   "#ABBC42", "#C70151", "#8C1C03", "#A67417", "#BDBF7E", "#FFAA07",
                                   "#274F73", "#D92949", "#29A649", "#46201C", "#D92525", "#7AA61B",
                                   "#F28B0C", "#8F6181", "#605F53", "#65818C", "#E96151", "#366774",
                                   "#70995C", "#592519", "#33664D", "#142933", "#F2E530", "#D94625"]'
                );
            $this->addChartProperties('fontFamily',                 '"Arial"');
            $this->addChartProperties('color',                      "'#545454'");
            $this->addChartProperties('lineColor',                  '"#545454"');
            $this->addValueAxisProperties('axisColor',              '"#545454"');
            $this->addValueAxisProperties('gridColor',              '"#545454"');
            $this->addChartProperties('colors', $colorTheme[6]);
            if ($this->type == ChartRules::TYPE_COLUMN_2D)
            {
                //General properties
                $this->resolveColumnAndBarGeneralProperties();
                $this->makeBarColorThemeArray($colorTheme[6]);
            }
            elseif ($this->type == ChartRules::TYPE_COLUMN_3D)
            {
                $this->resolveColumnAndBarGeneralProperties();
                $this->makeChart3d();
                $this->makeBarColorThemeArray($colorTheme[6]);
            }
            elseif ($this->type == ChartRules::TYPE_BAR_2D)
            {
                $this->addChartProperties('rotate',                 true);
                $this->addGraphProperties('gradientOrientation',    "'vertical'");
                $this->addGraphProperties('labelPosition',          "'right'");
                //General properties
                $this->resolveColumnAndBarGeneralProperties();
                $this->makeBarColorThemeArray($colorTheme[6]);
            }
            elseif ($this->type == ChartRules::TYPE_BAR_3D)
            {
                $this->resolveColumnAndBarGeneralProperties();
                $this->addChartProperties('rotate',                 true);
                $this->addGraphProperties('gradientOrientation',    "'vertical'");
                //$this->addGraphProperties('labelText',              "''");
                //$this->makeChart3d();
                $this->addChartProperties('depth3D',                    10);
                $this->addChartProperties('angle',                      60);
                $this->chartIs3d = true;
                $this->makeBarColorThemeArray($colorTheme[6]);
            }
            elseif ($this->type == ChartRules::TYPE_DONUT_2D)
            {
                $this->addChartProperties('color',                  "'#FFFFFF'");
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
            elseif ($this->type == ChartRules::TYPE_DONUT_3D)
            {
                $this->addChartProperties('color',                  "'#FFFFFF'");
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
                $this->makeChart3d();
            }
            elseif ($this->type == ChartRules::TYPE_PIE_2D)
            {
                $this->addChartProperties('color',                  "'#FFFFFF'");
                $this->addChartProperties('outlineColor',           "'#FFFFFF'");
                $this->addChartProperties('outlineAlpha',           0.8);
                $this->addChartProperties('outlineThickness',       1);
                $this->addChartProperties('usePrefixes',            true);
                $this->addChartProperties('radius',                 "'45%'");
                $this->addChartProperties('labelRadius',            -55);
                $this->addChartProperties('labelText',              "'[[title]]<br>[[percents]]%'");
                $this->addChartProperties('labelTickColor',         "'#000000'");
                $this->addChartProperties('pullOutRadius',          "'0%'");
                $this->addChartProperties('startDuration',          0);
                $this->chartIsPie = true;
            }
            elseif ($this->type == ChartRules::TYPE_PIE_3D)
            {
                $this->addChartProperties('color',                  "'#FFFFFF'");
                $this->addChartProperties('outlineColor',           "'#FFFFFF'");
                $this->addChartProperties('outlineAlpha',           0.8);
                $this->addChartProperties('outlineThickness',       1);
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
            elseif ($this->type == ChartRules::TYPE_STACKED_AREA)
            {
                //Chart
                $this->addChartProperties('usePrefixes',            true);
                $this->addChartProperties('plotAreaBorderColor',    "'#000000'");
                $this->addChartProperties('plotAreaBorderAlpha',    0);
                //Graph
                $this->addGraphProperties('type',                   "'line'");
                $this->addGraphProperties('fillAlphas',             0.6);
                $this->addGraphProperties('cornerRadiusTop',        0);
                $this->addGraphProperties('cornerRadiusBottom',     0);
                $this->addGraphProperties('lineAlpha',              0);
                //Axis
                $this->addCategoryAxisProperties('inside',          0);
                //ValueAxis
                $this->addValueAxisProperties('minimum',            0);
                $this->addValueAxisProperties('dashLength',         2);
                $this->addValueAxisProperties('stackType',          "'regular'");
                //Legend
                $this->addLegendProperties('borderAlpha',           0.2);
                $this->addLegendProperties('valueWidth',            0);
                $this->addLegendProperties('horizontalGap',         10);
                //General properties
                $this->resolveColumnAndBarGeneralProperties();
            }
            elseif ($this->type == ChartRules::TYPE_STACKED_COLUMN_2D)
            {
                //Chart
                $this->addChartProperties('usePrefixes',            true);
                $this->addChartProperties('plotAreaBorderColor',    "'#000000'");
                $this->addChartProperties('plotAreaBorderAlpha',    0);
                //Graph
                $this->addGraphProperties('balloonText',            "'[[title]]:[[value]]'");
                $this->addGraphProperties('fillAlphas',             1);
                $this->addGraphProperties('cornerRadiusTop',        0);
                $this->addGraphProperties('cornerRadiusBottom',     0);
                $this->addGraphProperties('lineAlpha',              0);
                //Axis
                $this->addCategoryAxisProperties('inside',          0);
                //ValueAxis
                $this->addValueAxisProperties('minimum',            0);
                $this->addValueAxisProperties('dashLength',         2);
                $this->addValueAxisProperties('stackType',          "'regular'");
                //Legend
                $this->addLegendProperties('borderAlpha',           0.2);
                $this->addLegendProperties('valueWidth',            0);
                $this->addLegendProperties('horizontalGap',         10);
                //General properties
                $this->resolveColumnAndBarGeneralProperties();
            }
            elseif ($this->type == ChartRules::TYPE_STACKED_COLUMN_3D)
            {
                $this->addValueAxisProperties('stackType',          "'regular'");
                $this->addGraphProperties('balloonText',            "'[[title]]:[[value]]'");
                $this->addGraphProperties('lineAlpha',              0.5);
                $this->addGraphProperties('fillAlphas',             1);
                //Legend
                $this->addLegendProperties('borderAlpha',           0.2);
                $this->addLegendProperties('valueWidth',            0);
                $this->addLegendProperties('horizontalGap',         10);
                //General properties
                $this->resolveColumnAndBarGeneralProperties();
                $this->addChartProperties('depth3D',                40);
                $this->addChartProperties('angle',                  30);
                $this->chartIs3d = true;
            }
            elseif ($this->type == ChartRules::TYPE_STACKED_BAR_2D)
            {
                $this->addChartProperties('rotate',                 true);
                $this->addChartProperties('usePrefixes',            true);
                $this->addGraphProperties('plotAreaBorderAlpha',    0);
                $this->addGraphProperties('lineAlpha',              0);
                $this->addGraphProperties('fillAlphas',             1);
                $this->addGraphProperties('gradientOrientation',    "'vertical'");
                $this->addGraphProperties('labelPosition',          "'right'");
                $this->addGraphProperties('balloonText',            "'[[title]]: [[value]]'");
                //Legend
                $this->addLegendProperties('borderAlpha',           0.2);
                $this->addLegendProperties('valueWidth',            0);
                $this->addLegendProperties('horizontalGap',         10);
                //General properties
                $this->addValueAxisProperties('stackType',          "'regular'");
                $this->resolveColumnAndBarGeneralProperties();
            }
            elseif ($this->type == ChartRules::TYPE_STACKED_BAR_3D)
            {
                $this->addValueAxisProperties('stackType',          "'regular'");
                $this->addChartProperties('rotate',                 true);
                $this->addChartProperties('usePrefixes',            true);
                $this->addGraphProperties('plotAreaBorderAlpha',    0);
                $this->addGraphProperties('lineAlpha',              0);
                $this->addGraphProperties('fillAlphas',             1);
                $this->addGraphProperties('gradientOrientation',    "'vertical'");
                $this->addGraphProperties('labelPosition',          "'right'");
                $this->addGraphProperties('balloonText',            "'[[title]]: [[value]]'");
                //Legend
                $this->addLegendProperties('borderAlpha',           0.2);
                $this->addLegendProperties('valueWidth',            0);
                $this->addLegendProperties('horizontalGap',         10);
                //General properties
                $this->resolveColumnAndBarGeneralProperties();
                $this->addChartProperties('depth3D',                40);
                $this->addChartProperties('angle',                  30);
                $this->chartIs3d = true;
            }
            else
            {
                //Default graph
            }
        }

        private function makeBarColorThemeArray($colorTheme)
        {
            $colorTheme = str_replace(array('[', ']', ' ', '"'), '', $colorTheme);
            $this->barColorTheme = explode(',', $colorTheme);
        }

        private function convertDataArrayToJavascriptArray()
        {
            $dataArray = array();
            $count     = 0;
            if (!empty($this->barColorTheme))
            {
                foreach($this->data as $data)
                {
                    $data['color'] = $this->barColorTheme[$count++];
                    $dataArray[] = $data;
                }
            }
            else
            {
                $dataArray = $this->data;
            }
            return CJavaScript::encode($dataArray);
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

        /**
         * Add properties to legend
         * Info on http://docs.amcharts.com/javascriptcharts/AmLegend
         */
        public function addLegendProperties($tag, $value)
        {
           $this->legendProperties[$tag]         = $value;
        }

        public function javascriptChart()
        {
            //Init AmCharts
            if (empty($this->data))
            {
                return $this->renderOnEmptyDataMessage();
            }
            $this->addChartPropertiesByType();
            $javascript  = "var chartData_{$this->id} = ". $this->convertDataArrayToJavascriptArray() . ";";
            $javascript .=" $(document).ready(function () {     ";
            //Make chart Pie or Serial
            if ($this->chartIsPie)
            {
                $this->valueField = $this->serial[0]['valueField'];
                $javascript      .="
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
                    //Add graph properties from GraphType
                    foreach($this->graphProperties as $graphTag => $graphOption)
                    {
                        $javascript .= "graph{$key}." . $graphTag . " = " . $graphOption . ";";
                    }
                    if(count($serial['options']) > 0)
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
            //Add legend to graph
            if (count($this->legendProperties) > 0)
            {
                //Add legend properties from GraphType
                $javascript .= "var legend = new AmCharts.AmLegend();";
                foreach($this->legendProperties as $tag => $option)
                {
                    $javascript .= "legend." . $tag . " = " . $option . ";";
                }
                $javascript .= "chart.addLegend(legend);";
            }
            //Write chart
            $javascript .= "chart.write('chartContainer{$this->id}');
                     });";
            return $javascript;
        }

        protected function resolveColumnAndBarGeneralProperties()
        {
            $this->addCategoryAxisProperties('title',           "'{$this->xAxisName}'");
            $this->addCategoryAxisProperties('unitPosition',    "'left'");
            $this->addCategoryAxisProperties('unit',            "'{$this->xAxisUnitContent}'");
            $this->addCategoryAxisProperties('usePrefixes',     true);
            $this->addCategoryAxisProperties('inside',          0);
            $this->addCategoryAxisProperties('dashLength',      2);
            $this->addCategoryAxisProperties('gridAlpha',       0);
            $this->addCategoryAxisProperties('autoGridCount',   "'false'");

            $this->addValueAxisProperties('title',              "'$this->yAxisName'");
            $this->addValueAxisProperties('unitPosition',       "'left'");
            $this->addValueAxisProperties('unit',               "'{$this->yAxisUnitContent}'");
            $this->addValueAxisProperties('usePrefixes',        true);
            $this->addValueAxisProperties('minimum',            0);
            $this->addValueAxisProperties('dashLength',         2);
            $this->addGraphProperties('colorField',             "'color'");
            $this->addGraphProperties('labelText',              "'[[value]]'");
            $this->addGraphProperties('balloonText',            "'[[category]]: [[value]]'");
            $this->addChartProperties('usePrefixes',            true);
            $this->addGraphProperties('fillAlphas',             1);
            $this->addGraphProperties('cornerRadiusTop',        0);
            $this->addGraphProperties('cornerRadiusBottom',     0);
            $this->addGraphProperties('lineAlpha',              0);

        }

        private function renderOnEmptyDataMessage()
        {
            $errorMessage = Zurmo::t('Core', 'Not enough data to render chart');
            $content      = ZurmoHtml::tag('span', array('class' => 'empty missing-chart'), $errorMessage);
            $javascript   = "
                    $('#chartContainer{$this->id}').html('" . $content . "');
                ";
            return $javascript;
        }
    }
?>