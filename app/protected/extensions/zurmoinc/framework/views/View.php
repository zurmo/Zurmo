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
     * Base class for all views. A view is something that knows how to render
     * itself for display within an XHtml page. Views are arranged in a
     * hierarchy of views within views and when the top level view is
     * asked to render itself it renders the entire hierarchy.
     */
    abstract class View
    {
        /**
         * Some views will need to set this to false in order to avoid
         * some portions of the view being cut off in the user interface.
         * @see MenuView, TitleBarView for examples of views that set this
         * value to false.
         */
        const RENDER_CONTENT_IN_DIV_WITH_OVERFLOW = true;

        /**
         * Extra classes defined to add to the div style for the view.
         * @var array
         */
        protected $cssClasses = array();

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
                $id = '';
            }
            $classes = join(' ', array_merge($this->getCssClasses(), $classes));
            if ($classes != '')
            {
                $classes = " class=\"$classes\"";
            }
            $calledClass = get_called_class();
            if ($calledClass::RENDER_CONTENT_IN_DIV_WITH_OVERFLOW)
            {
                return "<div $id$classes style=\"overflow: auto;\">$content</div>";
            }
            else
            {
                return "<div $id$classes style=\"\">$content</div>";
            }
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
    }
?>