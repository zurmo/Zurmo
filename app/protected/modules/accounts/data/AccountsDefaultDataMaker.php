<?php
    /**
     * Class to make default data that needs to be created upon an installation.
     */
    class AccountsDefaultDataMaker extends DefaultDataMaker
    {
        public function make()
        {
            $values = array(
                'Automotive',
                'Adult Entertainment',
                'Financial Services',
                'Mercenaries & Armaments',
            );
            static::makeCustomFieldDataByValuesAndDefault('Industries', $values);

            $values = array(
                'Prospect',
                'Customer',
                'Vendor',
            );
            static::makeCustomFieldDataByValuesAndDefault('AccountTypes', $values);
        }
    }
?>