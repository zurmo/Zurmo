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
     * Filter used by controllers to ascertain whether
     * any visible models exist for the logged in user. If not, it will display a helpful message.
     */
    class ZeroModelsCheckControllerFilter extends CFilter
    {
        public $controller;

        public $stateMetadataAdapterClassName = null;

        protected function preFilter($filterChain)
        {
            if (isset($_POST['ajax']))
            {
                return true;
            }
            $modelClassName    = $this->controller->getModule()->getPrimaryModelName();

            if ($this->stateMetadataAdapterClassName != null)
            {
                $stateClassName       = $this->stateMetadataAdapterClassName;
                $metadata             = array('clauses' => array(), 'structure' => '');
                $stateMetadataAdapter = new $stateClassName($metadata);
                $metadata             = $stateMetadataAdapter->getAdaptedDataProviderMetadata();
                $joinTablesAdapter    = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
                $where                = RedBeanModelDataProvider::makeWhere($modelClassName, $metadata, $joinTablesAdapter);
            }
            else
            {
                $joinTablesAdapter = null;
                $where             = null;
            }
            if ($modelClassName::getCount($joinTablesAdapter, $where) != 0)
            {
                return true;
            }
            $messageViewClassName         = $this->controller->getModule()->getPluralCamelCasedName() . 'ZeroModelsYetView';
            $messageView                  = new $messageViewClassName($this->controller->getId(),
                                                                      $this->controller->getModule()->getId(),
                                                                      $modelClassName);
            $pageViewClassName            = $this->controller->getModule()->getPluralCamelCasedName() . 'PageView';
            $view                         = new $pageViewClassName(ZurmoDefaultViewUtil::
                                                 makeStandardViewForCurrentUser($this->controller, $messageView));
            echo $view->render();
            return false;
        }
    }
?>