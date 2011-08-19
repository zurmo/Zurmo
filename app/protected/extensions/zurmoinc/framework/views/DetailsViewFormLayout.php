<?php
    class DetailsViewFormLayout extends FormLayout
    {
        /**
         * Used by the render of the form layout when the panels are to be displayed in a tabbed format.
         * @see FormLayout::PANELS_DISPLAY_TYPE_TABBED
         */
        protected $tabsContent;

        /**
         * Label to used for the link to show more panels.
         * @see FormLayout::PANELS_DISPLAY_TYPE_FIRST
         * @var string
         */
        protected $morePanelsLinkLabel;

        /**
         * Render a form layout.
         *  Gets appropriate meta data and loops through it. Builds form content
         *  as it loops through. For each element in the form it calls the appropriate
         *  Element class.
         * @return A string containing the element's content.
         */
        public function render()
        {
            $content        = '';
            if ($this->shouldRenderTabbedPanels())
            {
                $content .= $this->errorSummaryContent;
            }
            $tabsContent    = '';
            foreach ($this->metadata['global']['panels'] as $panelNumber => $panel)
            {
                $content .= $this->renderPanelHeaderByPanelNumberAndPanel($panelNumber, $panel);
                $content .= '<table>';
                $content .= TableUtil::getColGroupContent($this->getMaximumColumnCountForAllPanels());
                $content .= $this->renderTBodyTagByPanelNumber($panelNumber);
                foreach ($panel['rows'] as $row)
                {
                    $cellsContent = null;
                    foreach ($row['cells'] as $cell)
                    {
                        if (is_array($cell['elements']))
                        {
                            foreach ($cell['elements'] as $renderedElement)
                            {
                                $cellsContent .= $renderedElement;
                            }
                        }
                    }
                    if (!empty($cellsContent))
                    {
                        $content .= '<tr>';
                        $content .= $cellsContent;
                        $content .= '</tr>';
                    }
                }
                $content .= $this->renderLastPanelRowsByPanelNumber($panelNumber);
                $content .= '</tbody>';
                $content .= '</table>';
                if ($this->shouldRenderTabbedPanels())
                {
                    $content .= '</div>';
                }
            }
            $this->renderScripts();
            return $this->resolveFormLayoutContent($content);
        }

        protected function renderPanelHeaderByPanelNumberAndPanel($panelNumber, $panel)
        {
            if ($this->shouldRenderTabbedPanels())
            {
                $tabId = $this->uniqueId . '-panel-tab-' . $panelNumber;
                $content = '<div id="' . $tabId . '">';
                if (!empty($panel['title']))
                {
                    $tabTitle = $panel['title'];
                }
                else
                {
                    $tabTitle = Yii::t('Default', 'Tab'). ' ' . ($panelNumber + 1);
                }
               $this->addTabsContent('<li><a href="#' . $tabId . '">' . $tabTitle . '</a></li>');
               return $content;
            }
            else
            {
                if (!empty($panel['title']))
                {
                    return '<div class="panelTitle">' . $panel['title'] . '</div>';
                }
            }
        }

        protected function renderTBodyTagByPanelNumber($panelNumber)
        {
            if ($panelNumber > 0 && $this->shouldHidePanelsAfterFirstPanel())
            {
                return '<tbody class="view-panel-' . $this->uniqueId . '" style="display:none;">';
            }
            else
            {
                return '<tbody>';
            }
        }

        protected function renderLastPanelRowsByPanelNumber($panelNumber)
        {
            $content = null;
            if ($panelNumber == 0 && $this->shouldHidePanelsAfterFirstPanel())
            {
                $content .= '<tr id="show-more-panels-link-row-' . $this->uniqueId . '">';
                $content .= '<td  colspan = "' . $this->maxCellsPerRow . '">';
                $content .= CHtml::link($this->getMorePanelsLinkLabel(),
                                        $this->uniqueId, array('id' => 'show-more-panels-link-' . $this->uniqueId . ''));
                $content .= '</td>';
                $content .= '</tr>';
            }
            return $content;
        }

        protected function renderScripts()
        {
            if ($this->shouldHidePanelsAfterFirstPanel())
            {
            Yii::app()->clientScript->registerScript('showMorePanels', "
                $('#show-more-panels-link-". $this->uniqueId ."').click( function()
                    {
                        $('.view-panel-' + $(this).attr('href')).show();
                        $('#show-more-panels-link-row-' + $(this).attr('href')).hide();
                        return false;
                    }
                );");
            }
        }

        protected function resolveFormLayoutContent($content)
        {
            if ($this->shouldRenderTabbedPanels())
            {
                $content = '<div id="' . $this->uniqueId . '-panel-tabs"><ul>' . $this->getTabsContent() . '</ul>' . $content . '</div>';
                Yii::app()->clientScript->registerScript('initializeTabs' . $this->uniqueId, "
                    $(function() {
                        $( '#" . $this->uniqueId . "-panel-tabs' ).tabs({selected: 0});
                    });");
            }
            return $content;
        }

        protected function shouldHidePanelsAfterFirstPanel()
        {
            if (isset($this->metadata['global']['panelsDisplayType']) &&
            $this->metadata['global']['panelsDisplayType'] == FormLayout::PANELS_DISPLAY_TYPE_FIRST)
            {
                return true;
            }
            return false;
        }

        protected function shouldRenderTabbedPanels()
        {
            if (isset($this->metadata['global']['panelsDisplayType']) &&
            $this->metadata['global']['panelsDisplayType'] == FormLayout::PANELS_DISPLAY_TYPE_TABBED &&
            count($this->metadata['global']['panels']) > 1)
            {
                return true;
            }
            return false;
        }

        protected function addTabsContent($content)
        {
            $this->tabsContent .= $content;
        }

        protected function getTabsContent()
        {
            return $this->tabsContent;
        }

        public function setMorePanelsLinkLabel($label)
        {
            $this->morePanelsLinkLabel = $label;
        }

        protected function getMorePanelsLinkLabel()
        {
            if ($this->morePanelsLinkLabel == null)
            {
                Yii::t('Default', 'More Options');
            }
            else
            {
                return $this->morePanelsLinkLabel;
            }
        }
    }
?>