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
     * Note related array of random seed data parts.
     */
    return array(
        'modelClassName'        => array(
            'Contact',
            'Meeting',
            'Account',
            'Note',
            'Task',
            'Opportunity',
            'Contact',
        ),
        'name'                  => array(
            'Happy Birthday',
            'Discount',
            'Downtime Alert',
            'Sales decrease',
            'Missions alert',
            'Inbox Update',
            'Introducing Zurmo',
        ),
        'subject'               => array(
            'Happy Birthday',
            'Special Offer, 10% discount',
            'Planned Downtime',
            'Q4 Sales decrease',
            'Upcoming Missions',
            'New Inbox Module is live',
            'Lets explore Zurmo',
        ),
        'language'              => array(
            'en',
            'es',
            'it',
            'fr',
            'de',
            'en',
        ),
        'textContent'           => array(
            'Zurmo\'s source code is hosted on bitbucket while we use mercurial for version control.',
            'Our goal with Zurmo is to provide an easy-to-use, easy-to-customize CRM application that can be ' .
                'adapted to any business use case. We have taken special care to think through many different use' .
                ' cases and have designed a system that we believe provides a high degree of flexibility and a wide' .
                ' range of out-of-the-box use cases. Zurmo is capable of supporting your complex business processes, ' .
                'yet very simple to use.',
        ),
        'htmlContent'           => array(
            '<img src="http://zurmo.com/img/logo.png" alt="zurmo" />\'s source code is hosted on bitbucket while we use ' .
                '<img src="http://www.selenic.com/hg-logo/droplets-50.png" alt="mercurial" /> for version control.',
            '<strong>Our goal with Zurmo is to provide an easy-to-use, easy-to-customize CRM application that can be ' .
                'adapted to any business use case. We have taken special care to think through many different use' .
                ' cases and have designed a system that we believe provides a high degree of flexibility and a wide' .
                ' range of out-of-the-box use cases. Zurmo is capable of supporting your complex business processes, ' .
                'yet very simple to use.</strong>'
        ),
    );
?>