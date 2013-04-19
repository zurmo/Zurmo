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

    class CustomFieldsModelTest extends BaseTest
    {
        /**
         * There previously was a problem with a model being created, then a new custom field added, then if you try
         * to access the available CustomFieldData it was not available on that model.  Since performance3 work
         * this has been resolve and this test confirms it works ok
         */
        public function testConstructIncompleteIsNotNeeded()
        {
            //First create AAA model and save
            $aaa = new AAA();
            $aaa->aaaMember = 'test';
            $saved = $aaa->save();
            $this->assertTrue($saved);
            $aaaId = $aaa->id;
            $aaa->forget();
            unset($aaa);

            //Second create customFieldData
            $values = array(
                'Item 1',
                'Item 2',
                'Item 3',
            );
            $labels = array(
                'fr' => 'Item 1 fr',
                'fr' => 'Item 2 fr',
                'fr' => 'Item 3 fr',
            );
            $customFieldData = CustomFieldData::getByName('Items');
            $customFieldData->serializedData   = serialize($values);
            $customFieldData->serializedLabels = serialize($labels);
            $this->assertTrue($customFieldData->save());
            $id = $customFieldData->id;
            unset($customFieldData);

            //Third create a CustomField on AAA
            $metadata = AAA::getMetadata();
            $metadata['AAA']['customFields']['newCustomField'] = 'Items';
            $metadata['AAA']['relations']['newCustomField']    = array(RedBeanModel::HAS_ONE, 'CustomField');
            AAA::setMetadata($metadata);

            //Fourth make sure AAA can utilize CustomFieldData after being constructed
            $aaa = AAA::GetById($aaaId);

            $this->assertTrue($aaa->isAnAttribute('newCustomField'));
            $dropDownArray = unserialize($aaa->newCustomField->data->serializedData);
            $this->assertCount(3, $dropDownArray);

            //Fifth make sure a new model has the data available
            $aaa = new AAA();
            $this->assertTrue($aaa->isAnAttribute('newCustomField'));
            $dropDownArray = unserialize($aaa->newCustomField->data->serializedData);
            $this->assertCount(3, $dropDownArray);
        }
    }
