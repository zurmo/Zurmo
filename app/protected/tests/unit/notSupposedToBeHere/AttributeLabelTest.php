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

    class AttributeLabelTest extends BaseTest
    {
        public function testAttributesTranslateToAllSupportedLanguagesCorrectly()
        {
            $this->assertEquals('en', Yii::app()->language);
            $account = new Account(false);
            $this->assertEquals('Office Phone', $account->getAttributeLabel('officePhone'));
            $this->assertEquals('Billing Address', $account->getAttributeLabel('billingAddress'));
            Yii::app()->language = 'fr';
            $this->assertEquals('Téléphone de Bureau', $account->getAttributeLabel('officePhone'));
            $this->assertEquals('Adresse de facturation', $account->getAttributeLabel('billingAddress'));
            Yii::app()->language = 'it';
            $this->assertEquals('Telefono Ufficio', $account->getAttributeLabel('officePhone'));
            $this->assertEquals('Indirizzo di Fatturazione', $account->getAttributeLabel('billingAddress'));
            Yii::app()->language = 'es';
            $this->assertEquals('Teléfono de la Oficina', $account->getAttributeLabel('officePhone'));
            $this->assertEquals('Dirección de facturación', $account->getAttributeLabel('billingAddress'));
            Yii::app()->language = 'de';
            $this->assertEquals('Bürotelefon', $account->getAttributeLabel('officePhone'));
            $this->assertEquals('Rechnungsadresse', $account->getAttributeLabel('billingAddress'));
            //Set language back to english.
            Yii::app()->language = 'en';
            //Now customize both labels for all supported languages.
            $metadata = Account::getMetadata();
            $metadata['Account']['labels']['officePhone'] = array(
                'de' => 'GermanSomething',
                'en' => 'EnglishSomething',
                'es' => 'SpanishSomething',
                'fr' => 'FrenchSomething',
                'it' => 'ItalianSomething',
            );
            $metadata['Account']['labels']['billingAddress'] = array(
                'de' => 'GermanSomethingElse',
                'en' => 'EnglishSomethingElse',
                'es' => 'SpanishSomethingElse',
                'fr' => 'FrenchSomethingElse',
                'it' => 'ItalianSomethingElse',
            );
            Account::setMetadata($metadata);
            //Set language back to english.
            Yii::app()->language = 'en';
            $this->assertEquals('EnglishSomething', $account->getAttributeLabel('officePhone'));
            $this->assertEquals('EnglishSomethingElse', $account->getAttributeLabel('billingAddress'));
            Yii::app()->language = 'fr';
            $this->assertEquals('FrenchSomething', $account->getAttributeLabel('officePhone'));
            $this->assertEquals('FrenchSomethingElse', $account->getAttributeLabel('billingAddress'));
            Yii::app()->language = 'it';
            $this->assertEquals('ItalianSomething', $account->getAttributeLabel('officePhone'));
            $this->assertEquals('ItalianSomethingElse', $account->getAttributeLabel('billingAddress'));
            Yii::app()->language = 'es';
            $this->assertEquals('SpanishSomething', $account->getAttributeLabel('officePhone'));
            $this->assertEquals('SpanishSomethingElse', $account->getAttributeLabel('billingAddress'));
            Yii::app()->language = 'de';
            $this->assertEquals('GermanSomething', $account->getAttributeLabel('officePhone'));
            $this->assertEquals('GermanSomethingElse', $account->getAttributeLabel('billingAddress'));
            //Set language back to english.
            Yii::app()->language = 'en';
            //Retrieve all the language labels for a particular attribute.
            $officePhoneLabels    = $account->getAttributeLabelsForAllSupportedLanguagesByAttributeName('officePhone');
            $billingAddressLabels = $account->getAttributeLabelsForAllSupportedLanguagesByAttributeName('billingAddress');
            $comparePhoneLabels = array(
                'de' => 'GermanSomething',
                'en' => 'EnglishSomething',
                'es' => 'SpanishSomething',
                'fr' => 'FrenchSomething',
                'it' => 'ItalianSomething',
            );
            $compareAddressLabels = array(
                'de' => 'GermanSomethingElse',
                'en' => 'EnglishSomethingElse',
                'es' => 'SpanishSomethingElse',
                'fr' => 'FrenchSomethingElse',
                'it' => 'ItalianSomethingElse',
            );
            $this->assertEquals($comparePhoneLabels,   $officePhoneLabels);
            $this->assertEquals($compareAddressLabels, $compareAddressLabels);
        }
    }
