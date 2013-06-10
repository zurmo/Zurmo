<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Autoresponder related array of random seed data parts.
     */
    return array(
        'name'                          => array(
            '1 hour after subscription',
            '1 day after subscription',
            '1 hour after unsubscription',
            '4 hours after unsubscription',
        ),
        'subject'                       => array(
            'You are now subscribed.',
            'You subscribed today.',
            'You are now unsubscribed',
            'Your unsubscription triggered the next big bang',
        ),
        'htmlContent'                  => array(
            '<p>Thanks for <i>subscribing</i>. You are not gonna <strong>regret</strong> this.</p>',
            '<p>So you like <i>our</i> emails so far?</p>',
            '<p><strong>You are now unsubscribed. Its really sad to see you go but you can always subscribe</strong></p>',
            '<p>So you are <strong>not</strong> coming back?</p>',
        ),
        'textContent'                  => array(
            'Thanks for subscribing. You are not gonna regret this.',
            'So you like our emails so far?',
            'You are now unsubscribed. Its really sad to see you go but you can always subscribe',
            'So you are not coming back?',
        ),
        'secondsFromOperation'                  => array(
            60*60,
            60*60*24,
            60*60,
            60*60*4,
        ),
        'operationType'                  => array(
            Autoresponder::OPERATION_SUBSCRIBE,
            Autoresponder::OPERATION_SUBSCRIBE,
            Autoresponder::OPERATION_UNSUBSCRIBE,
            Autoresponder::OPERATION_UNSUBSCRIBE,
        ),
    );
?>