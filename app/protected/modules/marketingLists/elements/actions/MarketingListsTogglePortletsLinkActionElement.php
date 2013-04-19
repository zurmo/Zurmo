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
     * Class to render link to toggle portlets for a report grid view
     */
    class MarketingListsTogglePortletsLinkActionElement extends LinkActionElement
    {
        /**
         * @return null
         */
        public function getActionType()
        {
            return null;
        }

        /**
         * @return string
         */
        public function render()
        {
            $content  = null;
            return $content; // TODO: @Shoaibi: Low: Just for 1.5 release.
            $membersClass = $this->getMembersPortletClass();
            $autorespondersClass = $this->getAutorespondersPortletClass();
            if ($membersClass)
            {
                $htmlOptions = array('onClick' => 'js:$(".' . $membersClass . '").parentsUntil("li").parent().toggle();');
                $label       = ZurmoHtml::label(Zurmo::t('MarketingListsModule', 'Members'), Zurmo::t('MarketingListsModule', 'Members'), array('class' => 'label-for-marketing-list-widgets'));
                $content    .= ZurmoHtml::checkBox(Zurmo::t('MarketingListsModule', 'Members'), true, $htmlOptions) . $label;
            }
            if ($autorespondersClass)
            {
                $htmlOptions = array('onClick' => 'js:$(".' . $autorespondersClass . '").parentsUntil("li").parent().toggle();');
                $label       = ZurmoHtml::label(Zurmo::t('MarketingListsModule', 'Autoresponders'), Zurmo::t('MarketingListsModule', 'Autoresponders'), array('class' => 'label-for-marketing-list-widgets'));
                $content    .= ZurmoHtml::checkBox(Zurmo::t('MarketingListsModule', 'Autoresponders'), true, $htmlOptions) . $label;
            }
            return ZurmoHtml::tag('div', $this->getHtmlOptions(), $content );
        }

        /**
         * @return string
         */
        protected function getDefaultLabel()
        {
            return Zurmo::t('MarketingListsModule', 'Toggle View');
        }

        /**
         * @return null
         */
        protected function getDefaultRoute()
        {
            return null;
        }

        protected function getMembersPortletClass()
        {
            return ArrayUtil::getArrayValueWithExceptionIfNotFound($this->params, 'membersPortletClass');
        }

        protected function getAutorespondersPortletClass()
        {
            return ArrayUtil::getArrayValueWithExceptionIfNotFound($this->params, 'autorespondersPortletClass');
        }
    }
?>