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
                'Banking',
                'Business Services',
                'Energy',
                'Financial Services',
                'Insurance',
                'Manufacturing',
                'Retail',
                'Technology',
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