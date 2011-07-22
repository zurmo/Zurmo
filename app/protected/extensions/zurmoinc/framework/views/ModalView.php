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
     * The base View for a modal view.
     * Modal views are inline display popups
     */
    class ModalView extends View
    {
        /**
         * TODO
         */
        protected $uniqueId;

        /**
         * TODO
         */
        protected $pageTitle;

        /**
         * TODO
         */
        public function __construct(CController $controller, View $view, $uniqueId, $pageTitle)
        {
            $this->view = $view;
            $this->controller = $controller;
            $this->uniqueId = $uniqueId;
            $this->pageTitle = $pageTitle;
        }

        public function render()
        {
            $content = parent::render();
            Yii::app()->getClientScript()->render($content);
            return $content;
        }

        /**
         * Renders content for a view including a modal popup
         * container. Triggers the popup to appear upon render
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ModalView");
            $cClipWidget->beginWidget('zii.widgets.jui.CJuiDialog', array(
                'id' => 'modalContainer',
                'options' => array(
                    'title'    => $this->pageTitle,
                    'autoOpen' => true,
                    'modal'    => true,
                    'height'   => 480,
                    'width'    => 700,
                ),
            ));
            echo $this->view->render();
            $cClipWidget->endWidget('zii.widgets.jui.CJuiDialog');
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['ModalView'];
        }
    }
?>
