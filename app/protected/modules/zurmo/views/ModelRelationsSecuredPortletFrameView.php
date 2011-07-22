<?php
    /**
     * Helper class to handle portlet frame views where the metadata is being populated from outside this class.
     * This class is primarily used to display related model information in the DetailsAndRelationsView. There are
     * two ways this way handles portlets in a user interface. The first way is stacked using the JuiPortlets widget
     * while the second way is tabbed using the CJuiTabs widget.
     */
    class ModelRelationsSecuredPortletFrameView extends SecuredPortletFrameView
    {
        protected $layoutType = '100';

        private $metadata;

        private $portletsAreCollapsible;

        private $portletsAreMovable;

        private $showAsTabs;

        public function __construct($controllerId, $moduleId, $uniqueLayoutId, $params, $metadata,
                                    $portletsAreCollapsible = true, $portletsAreMovable = true, $showAsTabs = false)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($uniqueLayoutId)');
            assert('is_array($params)');
            assert('is_array($metadata)');
            assert('is_bool($portletsAreCollapsible)');
            assert('is_bool($portletsAreMovable)');
            assert('is_bool($showAsTabs)');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->uniqueLayoutId         = $uniqueLayoutId;
            $this->params                 = $params;
            $this->metadata               = $metadata;
            $this->portletsAreCollapsible = $portletsAreCollapsible;
            $this->portletsAreMovable     = $portletsAreMovable;
            $this->showAsTabs             = $showAsTabs;
        }

        protected function renderContent()
        {
            $this->portlets = $this->getPortlets($this->uniqueLayoutId, $this->metadata);
            return $this->renderPortlets($this->uniqueLayoutId,
                                         $this->portletsAreCollapsible,
                                         $this->portletsAreMovable);
        }

        protected function renderPortlets($uniqueLayoutId, $portletsAreCollapsible = true, $portletsAreMovable = true)
        {
            if(!$this->showAsTabs)
            {
                return parent::renderPortlets($uniqueLayoutId, $portletsAreCollapsible, $portletsAreMovable);
            }
            assert('is_bool($portletsAreCollapsible) && $portletsAreCollapsible == false');
            assert('is_bool($portletsAreMovable) && $portletsAreMovable == false');
            return $this->renderPortletsTabbed();
        }

        protected function renderPortletsTabbed()
        {
            assert('count($this->portlets) == 1');
            $tabItems = array();
            foreach ($this->portlets[1] as $noteUsed => $portlet)
            {
                $tabItems[$portlet->getTitle()] = array(
                    'id'      => $portlet->getUniquePortletPageId(),
                    'content' => $portlet->renderContent()
                );
            }
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("JuiTabs");
            $cClipWidget->widget('zii.widgets.jui.CJuiTabs', array(
                'id' => $this->uniqueLayoutId . '-portlet-tabs',
                'tabs' => $tabItems
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['JuiTabs'];
        }

        protected function arePortletsRemovable()
        {
            return false;
        }

        public function isUniqueToAPage()
        {
            return false;
        }
    }
?>