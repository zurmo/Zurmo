<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

    /**
     * The base View for any view that requires
     * metadata in order to render itself.
     */
    abstract class MetadataView extends View
    {
        protected $editableDesignerMetadata = false;

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
         * Renders a toolbar.
         * @return A string containing the toolbar content
         */
        protected function renderViewToolBar($renderInForm = true)
        {
            $content = '<div class="view-toolbar">';
            $content .= $this->renderActionElementBar($renderInForm);
            $content .= '</div>';
            return $content;
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
            if (isset($metadata['global']['toolbar']) && is_array($metadata['global']['toolbar']['elements']))
            {
                foreach ($metadata['global']['toolbar']['elements'] as $elementInformation)
                {
                    $elementclassname = $elementInformation['type'] . 'ActionElement';
                    $params = array_slice($elementInformation, 1);
                    array_walk($params, array($this, 'resolveEvaluateSubString'));
                    $element  = new $elementclassname($this->controllerId, $this->moduleId, $this->modelId, $params);
                    if (!$this->shouldRenderToolBarElement($element, $elementInformation))
                    {
                        continue;
                    }
                    if (!$renderedInForm && $element->isFormRequiredToUse())
                    {
                        throw NotSupportedException();
                    }
                    $renderedContent = $element->render();
                    if (!$first && !empty($renderedContent))
                    {
                        $content .= '&#160;|&#160;';
                    }
                    $first = false;
                    $content .= $renderedContent;
                }
            }
            return $content;
        }

        protected function resolveEvaluateSubString(& $element, $key)
        {
            MetadataUtil::resolveEvaluateSubString($element);
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
    }
?>
