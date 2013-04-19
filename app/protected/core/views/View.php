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
     * Base class for all views. A view is something that knows how to render
     * itself for display within an XHtml page. Views are arranged in a
     * hierarchy of views within views and when the top level view is
     * asked to render itself it renders the entire hierarchy.
     */
    abstract class View
    {
        /**
         * Extra classes defined to add to the div style for the view.
         * @var array
         */
        protected $cssClasses = array();

        protected $title;

        /**
         * Tells View that it can render the extending class' divs with
         * and id matching their name. Must be overridden to return
         * false in extending classes that can be rendered multiple times
         * within a page to avoid generating a page with non-unique ids which
         * will fail XHtml validation. For those it will render a class
         * attribute with their name.
         */
        public function isUniqueToAPage()
        {
            return true;
        }

        /**
         * Renders a div element with a id or class attribute set to the type
         * of the view, (depending on the value returned by isUniqueToAPage()),
         * and containing the content of any matching template found
         * in the themes/&lt;themename&gt;/ directory if it exists, marked by
         * begin/end comments, and the content of the view rendered by
         * renderContent(). All are correctly indented by indent().
         *
         * If the template does not exist in the active theme folder, it will attempt
         * to locate the file in the themes/default/templates folder and include it if
         * it exists.
         */
        public function render()
        {
            $theme        = Yii::app()->theme->name;
            $name         = get_class($this);
            $templateName = "themes/$theme/templates/$name.xhtml";
            $content      = $this->renderContent();
            if (!file_exists($templateName))
            {
                $templateName = "themes/default/templates/$name.xhtml";
            }
            if (file_exists($templateName))
            {
                $span           = "<span class=\"$name\" />";
                $templateSource = file_get_contents($templateName);
                if (strpos($templateSource, $span))
                {
                    $content = str_replace($span, $content, $templateSource);
                }
                $content = "<!-- Start of $templateName -->$content<!-- End of $templateName -->";
            }
            $classes = RuntimeUtil::getClassHierarchy(get_class($this), 'View');
            if ($this->isUniqueToAPage())
            {
                $id = " id=\"$name\"";
                unset($classes[0]);
            }
            else
            {
                $id = $this->getId();
                if ($id != null)
                {
                    $id = " id=\"$id\"";
                }
            }
            $classes = join(' ', array_merge($this->getCssClasses(), $classes));
            if ($classes != '')
            {
                $classes = " class=\"$classes\"";
            }
            $calledClass = get_called_class();
            if (YII_DEBUG)
            {
                $reflection = new ReflectionClass( $calledClass );
                $classFile = $reflection->getFileName();
                return "<!--Called in: $classFile--><div" . $id . $classes . $this->getViewStyle() . ">$content</div>";
            }
            else
            {
                return "<div" . $id . $classes . $this->getViewStyle() . ">$content</div>";
            }
        }

        /**
         * @returns id of view if UniqueToAPage is false.  Override if you want to pass an id in.
         */
        protected function getId()
        {
        }

        /**
         * Renders the view content.
         */
        protected abstract function renderContent();

        public function setCssClasses(array $classes)
        {
            $this->cssClasses = $classes;
        }

        public function getCssClasses()
        {
            return $this->cssClasses;
        }

        protected function getViewStyle()
        {
            return null;
        }

        protected function renderTitleContent()
        {
            return StringUtil::renderFluidTitleContent($this->getTitle());
        }

        public function getTitle()
        {
            return $this->title;
        }
    }
?>