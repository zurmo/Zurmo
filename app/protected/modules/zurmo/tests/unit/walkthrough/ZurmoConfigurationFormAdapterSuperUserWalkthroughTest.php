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
     * Walkthrough for the super user of global configuration
     */
    class ZurmoConfigurationFormAdapterSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testSuperUserEditConfigurationForm()
        {
            //checking with blank values for required fields
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->setPostArray(array('save'                                        => 'Save',
                                      'ZurmoConfigurationForm'                      => array(
                                        'applicationName'                             => '',
                                        'dashboardListPage'                           => '',
                                        'gamificationModalNotificationsEnabled'       => '1',
                                        'listPageSize'                                => '',
                                        'modalListPageSize'                           => '',
                                        'subListPageSize'                             => '',
                                        'timeZone'                                    => 'America/Chicago'),
                                      )
                               );
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/configurationEdit');
            $this->assertFalse(strpos($content, 'Dashboard portlet list page size') === false);
            $this->assertFalse(strpos($content, 'List page size cannot be blank.') === false);
            $this->assertFalse(strpos($content, 'Popup list page size cannot be blank.') === false);
            $this->assertFalse(strpos($content, 'Sublist page size cannot be blank.') === false);

            //checking with proper values for required fields
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->setPostArray(array('save'                                        => 'Save',
                                      'ZurmoConfigurationForm'                      => array(
                                        'applicationName'                             => 'Demo Company Inc.',
                                        'dashboardListPage'                           => '5',
                                        'gamificationModalNotificationsEnabled'       => '0',
                                        'listPageSize'                                => '10',
                                        'modalListPageSize'                           => '5',
                                        'subListPageSize'                             => '5',
                                        'timeZone'                                    => 'America/Chicago'),
                                      )
                               );
            $this->runControllerWithRedirectExceptionAndGetContent('zurmo/default/configurationEdit');
            $this->assertEquals('Global configuration saved successfully.', Yii::app()->user->getFlash('notification'));
        }
    }
?>