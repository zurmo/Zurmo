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

    class PersonCardViewLayoutTest extends ZurmoBaseTest
    {
        public function testRenderContent()
        {
            $user                   = new User();
            $personCardViewLayout   = new PersonCardViewLayout($user);
            $this->assertContains(
                    'img class="gravatar" width="100" height="100"',
                    $personCardViewLayout->renderContent());
            $this->assertContains(
                    '<span class="salutation"></span>',
                    $personCardViewLayout->renderContent());
            $this->assertContains(
                    '<div class="address"><',
                    $personCardViewLayout->renderContent());

            //Creating titles
            $titles = array('testTitle.');
            $customFieldData = CustomFieldData::getByName('Titles');
            $customFieldData->serializedData = serialize($titles);
            $this->assertTrue($customFieldData->save());

            //Create address
            $address = new Address();
            $address->street1 = 'testStreet';

            $user->firstName  = 'test' ;
            $user->lastName   = 'me' ;
            $user->title->value = 'testTitle.';
            $user->primaryAddress = $address;
            $this->assertContains(
                    'test me',
                    $personCardViewLayout->renderContent());
            $this->assertContains(
                    '<span class="salutation">testTitle.</span>',
                    $personCardViewLayout->renderContent());
            $this->assertContains(
                    'testStreet',
                    $personCardViewLayout->renderContent());

            $personCardViewLayout   = new PersonCardViewLayout($user);

            $dataEnhancer = $this->getMock(
                    'DataEnhancer',
                    array('personHasBackOfCard',
                          'getPersonBackOfCardLabel',
                          'getPersonBackOfCardCloseLabel',
                          'getPersonBackOfCardViewContent',
                          'getPersonSocialNetworksViewContent',
                          'getPersonDemographicViewContent',
                          'getPersonAvatar'));

            $dataEnhancer->expects($this->once())
                         ->method('personHasBackOfCard')
                         ->with($user)
                         ->will($this->returnValue(true));
            $dataEnhancer->expects($this->once())
                         ->method('getPersonBackOfCardLabel')
                         ->will($this->returnValue('stubBackOfCardLabel'));
            $dataEnhancer->expects($this->once())
                         ->method('getPersonBackOfCardCloseLabel')
                         ->will($this->returnValue('stubGetBackOfCardCloseLabel'));
            $dataEnhancer->expects($this->once())
                         ->method('getPersonBackOfCardViewContent')
                         ->with($user)
                         ->will($this->returnValue('stubGetPersonBackOfCardViewContent'));
            $dataEnhancer->expects($this->once())
                         ->method('getPersonSocialNetworksViewContent')
                         ->with($user)
                         ->will($this->returnValue('stubGetPersonSocialNetworksViewContent'));
            $dataEnhancer->expects($this->once())
                         ->method('getPersonDemographicViewContent')
                         ->with($user)
                         ->will($this->returnValue('stubGetPersonDemographicViewContent'));
            $dataEnhancer->expects($this->once())
                         ->method('getPersonAvatar')
                         ->with($user)
                         ->will($this->returnValue('stubAvatar'));

            Yii::app()->setComponent('dataEnhancer', $dataEnhancer, false);

            $content = $personCardViewLayout->renderContent();
            $this->assertContains(
                    '<a class="toggle-back-of-card-link mini-button clearfix" href="#">',
                    $content);
            $this->assertContains(
                    '<span class="show">stubBackOfCardLabel</span>',
                    $content);
            $this->assertContains(
                    '<span>stubGetBackOfCardCloseLabel</span>',
                    $content);
            $this->assertContains(
                    '<div class="back-of-card clearfix">stubGetPersonBackOfCardViewContent</div>',
                    $content);
            $this->assertContains(
                    '<div class="social-details">stubGetPersonSocialNetworksViewContent</div>',
                    $content);
            $this->assertContains(
                    '<div class="demographic-details">stubGetPersonDemographicViewContent</div>',
                    $content);
            $this->assertContains(
                    'stubAvatar',
                    $content);
        }
    }
?>