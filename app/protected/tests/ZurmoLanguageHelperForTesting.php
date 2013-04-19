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
     * Contains methods used specifically for testing.
     */
    class ZurmoLanguageHelperForTesting extends ZurmoLanguageHelper
    {
        private $languageCodeArrayForTesting = array('de', 'es', 'fr', 'it');
        private $messagesForTesting = array(
            'de' => array(
                'Default' => array(
                    'Name' => 'Name',
                    'Office Phone' => 'Bürotelefon',
                    'Billing Address' => 'Rechnungsadresse'
                ),
            ),
            'es' => array(
                'Default' => array(
                    'Name' => 'Nombre',
                    'Office Phone' => 'Teléfono de la Oficina',
                    'Billing Address' => 'Dirección de facturación'
                ),
            ),
            'fr' => array(
                'Default' => array(
                    'Name' => 'Nom',
                    'Customer' => 'Client',
                    'New' => 'Nouveau',
                    'In Progress' => 'En cours',
                    'Recycled' => 'Réactivé',
                    'Dead' => 'Mort',
                    'Office Phone' => 'Téléphone de bureau',
                    'Billing Address' => 'Adresse de facturation',
                    'Opportunity' => 'Opportunité'
                ),
            ),
            'it' => array(
                'Default' => array(
                    'Name' => 'Nome',
                    'Office Phone' => 'Telefono Ufficio',
                    'Billing Address' => 'Indirizzo di Fatturazione'
                ),
            ),
        );

        public function activateLanguagesForTesting()
        {
            $supportedLanguages = $this->getSupportedLanguagesData();

            foreach ($this->languageCodeArrayForTesting as $languageCode)
            {
                // Check if the language is supported
                if (!array_key_exists($languageCode, $supportedLanguages))
                {
                    throw new NotFoundException(Zurmo::t('ZurmoModule', 'Language not supported.'));
                }

                $language = new ActiveLanguage;
                $language->code = $supportedLanguages[$languageCode]['code'];
                $language->name = $supportedLanguages[$languageCode]['name'];
                $language->nativeName = $supportedLanguages[$languageCode]['nativeName'];
                $language->activationDatetime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                $language->lastUpdateDatetime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                $language->save();
            }
        }

        public function importMessagesForTesting()
        {
            foreach ($this->messagesForTesting as $languageCode => $categories)
            {
                foreach ($categories as $category => $messages)
                {
                    ZurmoMessageSourceUtil::importMessagesArray(
                        $languageCode,
                        $category,
                        $messages
                    );
                }
            }
        }

        public function deactivateLanguagesForTesting()
        {
            $sourceLanguageModel = ActiveLanguage::getSourceLanguageModel();
            foreach ($this->languageCodeArrayForTesting as $languageCode)
            {
                if ($languageCode == $sourceLanguageModel->code) continue;
                $this->deactivateLanguage($languageCode);
            }
        }

        public function getActiveLanguagesDataForTesting()
        {
            $activeLanguagesData = $this->getActiveLanguagesData();

            foreach ($activeLanguagesData as $languageCode => $languageData)
            {
                if (array_key_exists('activationDatetime', $languageData))
                {
                    unset($activeLanguagesData[$languageCode]['activationDatetime']);
                }

                if (array_key_exists('lastUpdateDatetime', $languageData))
                {
                    unset($activeLanguagesData[$languageCode]['lastUpdateDatetime']);
                }
            }

            return $activeLanguagesData;
        }
    }
?>