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

    /**
     * The base View for a modal view.
     * Modal views are inline display popups
     */
    class ModalView extends View
    {
        /**
         * TODO
         */
        public function __construct(CController $controller, View $view)
        {
            $this->view       = $view;
            $this->controller = $controller;
        }

        public function render()
        {
            $content = parent::render();
            Yii::app()->getClientScript()->render($content);
            return $content;
        }

        /**
         * Renders content for a view.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            return $this->view->render();
        }

        public static function getAjaxOptionsForModalLink($title, $containerId = 'modalContainer', $height = 'auto', $width = 600, $position = 'center', $class = null)
        {
            assert('is_string($containerId)');
            assert('is_string($title)');
            assert('$height == "auto" || is_int($height)');
            assert('is_int($width)');
            assert('is_string($position) || is_array($position)');
            assert('is_string($class) || $class == null');
            return array(
                    'beforeSend' => static::getAjaxBeforeSendOptionForModalLinkContent($title, $containerId, $height, $width, $position, $class),
                    'update'     => '#' . $containerId);
        }

        public static function getAjaxBeforeSendOptionForModalLinkContent($title, $containerId = 'modalContainer', $height = 'auto', $width = 600, $position = 'center', $class = null)
        {
            assert('is_string($containerId)');
            assert('is_string($title)');
            assert('$height == "auto" || is_int($height)');
            assert('is_int($width)');
            assert('is_string($position) || is_array($position)');
            assert('is_string($class) || $class == null');
            if ($height == 'auto')
            {
                $heightContent = "'auto'";
            }
            else
            {
                $heightContent = $height;
            }
            if (is_array($position))
            {
                $position = CJSON::encode($position);
            }
            else
            {
                $position = '" . $position . "';
            }
            $dialogClassContent = null;
            if ($class != null)
            {
                $dialogClassContent = ", 'dialogClass':'" . $class . "'";
            }
            // Begin Not Coding Standard
            return "js:function(){jQuery('#" . $containerId . "').html('');" .
                                    "makeLargeLoadingSpinner('" . $containerId . "');" .
                                    "window.scrollTo(0, 0);" .
                                    "jQuery('#" . $containerId . "').dialog({'title':\"" . CJavaScript::quote($title) . "\",'autoOpen':true," .
                                    "'modal':true,'height':" . $heightContent . ",'width':" . $width .
                                    ", 'position':" . $position . $dialogClassContent . "}); return true;}";
            // End Not Coding Standard
        }
    }
?>
