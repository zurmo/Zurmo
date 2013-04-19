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

    /**
     * The base View for any view that requires
     * metadata in order to render itself.
     */
    abstract class MetadataView extends View
    {
        protected $editableDesignerMetadata = false;

        protected $disableFloatOnToolbar  = false;

        protected $modelId;

        /**
         * Returns metadata for use in automatically generating the view.
         * @see getDefaultMetadata()
         */
        public static function getMetadata()
        {
            $className = get_called_class();
            return $className::getDefaultMetadata();
        }

        /**
         * Returns default metadata for use in automatically generating the view.
         */
        public static function getDefaultMetadata()
        {
            return array();
        }

        /**
         * @returns string content of $content passed in wrapped in the view-toolbar-container and view-toolbar. Also
         * accommodates for ignoring the dock if necessary
         */
        protected function resolveAndWrapDockableViewToolbarContent($content)
        {
            assert('is_string($content)');
            if ($this->disableFloatOnToolbar)
            {
                $disableFloatContent = ' disable-float-bar';
            }
            else
            {
                $disableFloatContent = null;
            }
            $content = ZurmoHtml::tag('div', array('class' => 'form-toolbar'), $content);
            $content = ZurmoHtml::tag('div', array('class' => 'view-toolbar-container clearfix dock' .
                       $disableFloatContent), $content);
            $content = ZurmoHtml::tag('div', array('class' => 'float-bar'), $content);
            return $content;
        }

        /**
         * Renders a toolbar.
         * @return A string containing the toolbar content
         */
        protected function renderViewToolBar($renderInForm = true)
        {
            if ( $renderInForm == true )
            {
                $actionContent = $this->renderActionElementBar($renderInForm);
                if ($actionContent != null)
                {
                    $content  = '<div class="view-toolbar-container clearfix"><div class="portlet-toolbar">';
                    $content .= $actionContent;
                    $content .= '</div></div>';
                    return $content;
                }
            }
            return null;
        }

        /**
         * Render a toolbar above the form layout. This includes buttons and/or
         * links to go to different views or process actions such as save or delete
         * @return A string containing the element's content.
          */
        protected function renderActionElementBar($renderedInForm)
        {
            $metadata = $this::getMetadata();
            $content = null;
            $first = true;
            $dropDownId = null;
            $dropDownItems = array();
            $dropDownItemHtmlOptions = array('prompt' => ''); // we need this so we have a default one to select at the end of operation.
            if (isset($metadata['global']['toolbar']) && is_array($metadata['global']['toolbar']['elements']))
            {
                foreach ($metadata['global']['toolbar']['elements'] as $elementInformation)
                {
                    $renderedContent = null;
                    $this->resolveActionElementInformationDuringRender($elementInformation);
                    array_walk($elementInformation, array($this, 'resolveEvaluateSubString'));
                    $params = array_slice($elementInformation, 1);
                    $elementClassName = $elementInformation['type'] . 'ActionElement';
                    $element  = new $elementClassName($this->controllerId, $this->moduleId, $this->modelId, $params);
                    if (!$this->shouldRenderToolBarElement($element, $elementInformation))
                    {
                        continue;
                    }
                    if (!$renderedInForm && $element->isFormRequiredToUse())
                    {
                        throw new NotSupportedException();
                    }
                    $continueRendering = $this->resolveMassActionLinkActionElementDuringRender($elementClassName,
                                                                                            $element,
                                                                                            $dropDownItems,
                                                                                            $dropDownItemHtmlOptions
                                                                                            );
                    if ($continueRendering)
                    {
                        $renderedContent = $element->render();
                    }
                    else
                    {
                        if (! $dropDownId)
                        {
                            $dropDownId = $elementClassName::getDropdownId();
                        }
                    }
                    if (!$first && !empty($renderedContent))
                    {
                       // $content .= '&#160;|&#160;';
                    }
                    $first = false;
                    $content .= $renderedContent;
                }
            }
            if (!empty($dropDownItems))
            {
                $content .= ZurmoHtml::link('', '#', array('class' => 'mobile-actions'));
                $content .= ZurmoHtml::tag('div', array( 'class' => 'mobile-view-toolbar-container'),
                                ZurmoHtml::dropDownList(
                                        $dropDownId,
                                        '',
                                        $dropDownItems,
                                        $dropDownItemHtmlOptions
                                    )
                                );
            }
            return $content;
        }

        /**
         * Resolves how MassActionLinkElements should be rendered on Mobile Devices
         * @param $elementClassName
         * @param $element
         * @param $dropDownItems
         * @param $dropDownItemHtmlOptions
         * @return bool whether or not to continue rendering this element
         */
        protected function resolveMassActionLinkActionElementDuringRender($elementClassName, & $element, & $dropDownItems, & $dropDownItemHtmlOptions)
        {
            $class = new ReflectionClass($elementClassName);
            if ($class->implementsInterface('SupportsRenderingDropDownInterface') &&
                $elementClassName::shouldRenderAsDropDownWhenRequired() &&
                Yii::app()->userInterface->isMobile())
            {
                if (empty($dropDownItems))
                {
                    $element->registerDropDownScripts();
                }
                $items = $element->getOptions();
                $items = (array_key_exists('label', $items))? array($items) : $items;
                foreach ($items as $item)
                {
                    if($element::useItemUrlAsElementValue())
                    {
                        $value      = $item['url'];
                    }
                    else
                    {
                        $value      = $element->getElementValue();
                    }

                    if (!$value)
                    {
                        $value      = $element->getActionNameForCurrentElement() . '_' . $item['label'];

                    }
                    $optGroup   = $element->getOptGroup();
                    if ($optGroup)
                    {
                        $dropDownItems[$optGroup][$value]   = $item['label'];
                    }
                    else
                    {
                        $dropDownItems[$value]              = $item['label'];
                    }
                    $dropDownItemHtmlOptions['options'][$value] = (isset($item['itemOptions'])) ? $item['itemOptions'] : array();
                }
                return false;
            }
            return true;
        }

        /**
         * Override if any manipulation is needed on the $elementInformaiton prior to rendering
         * @param array $elementInformation
         */
        protected function resolveActionElementInformationDuringRender(& $elementInformation)
        {
        }

        /**
         * This method must not use the @see MetadataUtil::resolveEvaluateSubString because some evaluations might
         * be using $this, which will not work if executed from within a different method.
         * @param mixed $element
         * @param integer $key
         */
        public function resolveEvaluateSubString(& $element, $key)
        {
            if (is_array($element))
            {
                array_walk($element, array($this, 'resolveEvaluateSubString'));
                return;
            }
            if (strpos($element, 'eval:') !== 0)
            {
                return;
            }
            $stringToEvaluate = substr($element, 5);
            eval("\$element = $stringToEvaluate;");
        }

        /**
         * Override in each sub-class if you
         * have applicable designer rules for
         * handling the modification of metadata layouts
         * @return null or a DesignerRules Class;
         */
        public static function getDesignerRulesType()
        {
            return null;
        }

        /**
         * Override in each sub-class if you
         * have a different model form to use than the
         * primary model for the module for this view
         * @return null or a ModelForm class name
         */
        public static function getModelForMetadataClassName()
        {
            return null;
        }

        protected function shouldRenderToolBarElement($element, $elementInformation)
        {
            assert('$element instanceof ActionElement');
            assert('is_array($elementInformation)');
            return true;
        }

        /**
         * Render a menu above the form layout. This includes buttons and/or
         * links to go to different views or process actions such as save or delete
         * @return A string containing the element's content.
          */
        protected function renderActionElementMenu($title = null)
        {
            if ($title == null)
            {
                $title = Zurmo::t('Core', 'Options');
            }
            $metadata  = $this::getMetadata();
            $menuItems = array('label' => $title, 'items' => array());
            if (isset($metadata['global']['toolbar']) && is_array($metadata['global']['toolbar']['elements']))
            {
                foreach ($metadata['global']['toolbar']['elements'] as $elementInformation)
                {
                    $elementClassName  = $elementInformation['type'] . 'ActionElement';
                    $params            = array_slice($elementInformation, 1);
                    array_walk($params, array($this, 'resolveEvaluateSubString'));
                    $element  = new $elementClassName($this->controllerId, $this->moduleId, $this->modelId, $params);
                    if (!$this->shouldRenderToolBarElement($element, $elementInformation))
                    {
                        continue;
                    }
                    if ($element->isFormRequiredToUse())
                    {
                        throw new NotSupportedException();
                    }
                    $menuItems['items'][] = $element->renderMenuItem();
                }
            }
            if (count($menuItems['items']) > 0)
            {
                $cClipWidget = new CClipWidget();
                $cClipWidget->beginClip("OptionMenu");
                $cClipWidget->widget('application.core.widgets.MbMenu', array(
                    'htmlOptions' => array('class' => 'options-menu'),
                    'items'                   => array($menuItems),
                ));
                $cClipWidget->endClip();
                return $cClipWidget->getController()->clips['OptionMenu'];
            }
        }
    }
?>
