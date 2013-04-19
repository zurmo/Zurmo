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
     * Buttons and Links that take the user to a new
     * page.
     */
    abstract class ActionElement
    {
        protected $controllerId;
        protected $moduleId;
        protected $modelId;
        protected $route;
        protected  $params;
        protected $formRequiredToUse = false;

        abstract public function render();

        abstract protected function getDefaultLabel();

        abstract protected function getDefaultRoute();

        abstract public function getActionType();

        public static function getType()
        {
            $name = get_called_class();
            return substr($name, 0, strlen($name) - strlen('ActionElement'));
        }

        public function __construct($controllerId, $moduleId, $modelId, $params = array())
        {
            $this->controllerId = $controllerId;
            $this->moduleId     = $moduleId;
            $this->modelId      = $modelId;
            $this->params       = $params;
            $this->route        = $this->getRoute();
        }

        /**
         * Override in child class to add support for rendering the element as a menu item.
         */
        public function renderMenuItem()
        {
            throw new NotSupportedException();
        }

        protected function getLabel()
        {
            if (!isset($this->params['label']))
            {
                return $this->getDefaultLabel();
            }
            return $this->params['label'];
        }

        protected function getHtmlOptions()
        {
            if (!isset($this->params['htmlOptions']))
            {
                return array();
            }
            return $this->params['htmlOptions'];
        }

        protected function getAjaxOptions()
        {
            if (!isset($this->params['ajaxOptions']))
            {
                return array();
            }
            return $this->params['ajaxOptions'];
        }

        protected function getRouteParameters()
        {
            if (!isset($this->params['routeParameters']))
            {
                return array();
            }
            return $this->params['routeParameters'];
        }

        protected function getRedirectUrl()
        {
            if (!isset($this->params['redirectUrl']))
            {
                return array();
            }
            return $this->params['redirectUrl'];
        }

        protected function wrapLabel()
        {
            if (!isset($this->params['wrapLabel']))
            {
                return true;
            }
            return $this->params['wrapLabel'];
        }

        protected function getRoute()
        {
            return $this->getDefaultRoute();
        }

        public function isFormRequiredToUse()
        {
            return $this->formRequiredToUse;
        }

        protected function resolveLabelAndWrap()
        {
            if ($this->wrapLabel())
            {
                return ZurmoHtml::wrapLabel($this->getLabel());
            }
            return $this->getLabel();
        }
    }
?>