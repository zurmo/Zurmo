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
     * Display a button to activate the browser's desktop notifications
     */
    class DesktopNotificationElement extends Element
    {
        /**
         * Renders the button.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            $content = $this->renderEnableDesktopNotificationsCheckBox();
            return ZurmoHtml::tag('div', array('id' => 'enableDesktopNotifications'), $content);
        }

        protected function renderControlNonEditable()
        {
            throw new NotImplementedException();
        }

        protected function renderLabel()
        {
            $content  = parent::renderLabel();
            $content .= $this->renderTooltipContentForEnableDesktopNotifications();
            return $content;
        }

        protected function renderEnableDesktopNotificationsCheckBox()
        {
            $htmlOptions             = array();
            $htmlOptions['id']       = $this->getEditableInputId();
            $htmlOptions['name']     = $this->getEditableInputName();
            $htmlOptions['disabled'] = $this->getDisabledValue();
            $htmlOptions             = array_merge($this->getHtmlOptions(), $htmlOptions);
            $content                 = $this->form->checkBox($this->model, $this->attribute, $htmlOptions);
            return ZurmoHtml::tag('span', array(), $content);
        }

        protected static function renderTooltipContentForEnableDesktopNotifications()
        {
            $link        = ZurmoHtml::link(Zurmo::t('Core', 'Click Here'),
                                           '',
                                           array('onClick' => 'js:desktopNotifications.requestAutorization(); return false;'));

            $title       = Zurmo::t('UsersModule', '<p>This feature only works in Chrome when real time updates are globally enabled. </p>' .
                                             '<p>Permissions need to be activated for each browser.</p>' .
                                             '<p>To activate, <u>{link}</u> and choose "allow" at browser request.</p>',
                                  array('{link}' => $link)
                            );
            $content     = ZurmoHtml::tag('span',
                                          array('id'    => 'user-enable-desktop-notifications-tooltip',
                                                'class' => 'tooltip',
                                                'title' => $title,
                                               ),
                                          '?');
            $qtip        = new ZurmoTip(array('options' => array('position' => array('my' => 'bottom right', 'at' => 'top left'),
                                                                 'hide'     => array('event' => 'unfocus'),
                                                                 'show'     => array('event' => 'click')

                )));
            $qtip->addQTip("#user-enable-desktop-notifications-tooltip");
            return $content;
        }
    }
?>