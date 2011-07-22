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
     * Buttons and Links that take the user to a new
     * page.
     */
    abstract class ActionElement
    {
        protected $controllerId;
        protected $moduleId;
        protected $modelId;
        protected $route;
        protected $params;
        protected $formRequiredToUse = false;

        abstract public function render();

        abstract protected function getDefaultLabel();

        abstract protected function getDefaultRoute();

        abstract public function getActionType();

        public function __construct($controllerId, $moduleId, $modelId, $params = array())
        {
            $this->controllerId = $controllerId;
            $this->moduleId     = $moduleId;
            $this->modelId      = $modelId;
            $this->params       = $params;
            $this->route        = $this->getRoute();
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

        protected function getRoute()
        {
            return $this->getDefaultRoute();
        }

        public function isFormRequiredToUse()
        {
            return $this->formRequiredToUse;
        }
    }
?>