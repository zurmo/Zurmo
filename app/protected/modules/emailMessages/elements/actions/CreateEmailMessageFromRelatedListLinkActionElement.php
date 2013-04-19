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
     * Element used to display link to a modal window for creating an email.
     */
    class CreateEmailMessageFromRelatedListLinkActionElement extends RelatedListLinkActionElement
    {
        public function render()
        {
            return ZurmoHtml::ajaxLink($this->getLabel(), $this->getDefaultRoute(),
                $this->getAjaxLinkOptions(),
                $this->getHtmlOptions()
            );
        }

        public function renderMenuItem()
        {
            return array('label'           => $this->getLabel(),
                         'url'             => $this->getDefaultRoute(),
                         'linkOptions'     => $this->getHtmlOptions(),
                         'ajaxLinkOptions' => $this->getAjaxLinkOptions()
            );
        }

        protected function getHtmlOptions()
        {
            $htmlOptions            = parent::getHtmlOptions();
            $this->resolveHtmlOptionsId($htmlOptions);
            return $htmlOptions;
        }

        protected function resolveHtmlOptionsId(& $htmlOptions)
        {
            if ($this->getLinkId() != null)
            {
                $htmlOptions['id']      = $this->getLinkId();
            }
        }

        protected function getAjaxLinkOptions()
        {
            $title = Zurmo::t('EmailMessagesModule', 'Email');
            return ModalView::getAjaxOptionsForModalLink(
                                     Zurmo::t('EmailMessagesModule', 'Compose Email'), 'modalContainer', 'auto', 800,
                                                                         array(
                                                                            'my' => 'top',
                                                                            'at' => 'bottom',
                                                                            'of' => '#HeaderView'));
        }

        protected function getDefaultLabel()
        {
            return Zurmo::t('EmailMessagesModule', 'Email');
        }

        protected function getDefaultRoute()
        {
            return Yii::app()->createUrl('/emailMessages/default/createEmailMessage', $this->resolveRouteParamters());
        }

        /**
         * This method is required because when coming from a related list view, the $param array does not have the
         * model id information for the contact or account.
         */
        protected function resolveRouteParamters()
        {
            $routeParameters = $this->getRouteParameters();
            if (!isset($routeParameters['relatedId']))
            {
                $routeParameters['relatedId'] = $this->modelId;
            }
            return $routeParameters;
        }

        public function getActionType()
        {
            return 'Create';
        }

        protected function getGridId()
        {
            if (!isset($this->params['gridId']))
            {
                return null;
            }
            return $this->params['gridId'];
        }

        protected function getLinkId()
        {
            if ($this->getGridId() == null)
            {
                return null;
            }
            return $this->getGridId(). '-createEmail-' . $this->modelId;
        }
    }
?>