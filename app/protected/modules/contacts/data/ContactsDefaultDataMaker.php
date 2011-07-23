<?php
    /**
     * Class to make default data that needs to be created upon an installation.
     */
    class ContactsDefaultDataMaker extends DefaultDataMaker
    {
        public function make()
        {
            ContactsModule::loadStartingData();

            $values = array(
                'Self-Generated',
                'Inbound Call',
                'Tradeshow',
                'Word of Mouth',
            );
            static::makeCustomFieldDataByValuesAndDefault('LeadSources', $values);
        }
    }
?>