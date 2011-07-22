<?php
    class ImportColumnDataSanitizationAnalyzer
    {
        public function __construct($modelImportRules, $dataProvider)
        {
            assert('$dataProvider instanceof XXDataProvider');
        }

        public function analyzeByColumnNameAndColumnMappingData($columnName, $columnMappingData)
        {

            //$columnMappingData would have information like ["attributeNameOrDerivedTypeData"]
                //$columnMappingData["attributeNameOrDerivedTypeData"]['ownerMappingAttributeType']

                //??? but what class would understand this: $columnMappingData["attributeNameOrDerivedTypeData"]['ownerMappingAttributeType']
                //I guess OwnerAttributeImportRules would have to understand it, this is a derived type i guess. and the sanitization to use
                //that is getAttributeValueSanitizerUtilNames would have to make a decision based on that data. which saniziation to pass back
                //cause we want OwnerIdsanitizer and OwnerUserNameSanitizer as seperate and not knowing of each other.  There fore the attributeImportRules
                //must know.

                assert('is_string($columnMappingData["attributeNameOrDerivedType"])');
                $attributeImportRules = AttributeImportRulesFactory::makeAttributeImportRulesByColumnMappingData(
                                                                        $columnMappingData);
                if($attributeValueSanitizerUtilNames = $attributeImportRules->getAttributeValueSanitizerUtilNames() != null)
                {
                    assert('is_array($attributeValueSanitizerUtils)');
                    foreach($attributeValueSanitizerUtilNames as $attributeValueSanitizerUtilName)
                    {
                        //???i think here you will probably need to check IF the util supports any type of acceptableAttributeValuesCheck
                        //???example, trim, could be used, but not part of a acceptable values check, it is just run regardless.

                        if($attributeValueSanitizerUtilName::supportsSqlBulkAttributeValuesSanitizer())
                        {
 //ACTUALLY - if we move the below logics into               $importAcceptableAttributeValuesCheckResults
 //then we can also move dataProvider in here too and then we dont have to pass Dataprovider into ImportColumnsSanitizerUtil
                            $sqlBulkAttributeValuesSanitizer = $attributeValueSanitizerUtilName::
                                                                    makeSqlBulkAttributeValuesSanitizerByModelAndAttributeImportRules($modelImportRules, $attributeImportRules);
                            assert('$sqlBulkAttributeValuesSanitizer != null');
                            $clean = $sqlBulkAttributeValuesSanitizer->getAreAllValuesAcceptableByDataProvider($dataProvider);
                            //??? importAcceptableAttributeValuesCheckResults seems like a strange name.
                            //??? if this is owner, and we selected username, how do we pass that info here?
                            $importAcceptableAttributeValuesCheckResults->addCheckResult(
                                $importColumnName, $clean, $sqlBulkAttributeValuesSanitizer);
                        }
                        else
                        {
                            $attributeValueSanitizer = $attributeValueSanitizerUtilName::
                                                                    makeAttributeValueSanitizerByModelAndAttributeImportRules($modelImportRules, $attributeImportRules);
                            assert('$attributeValueSanitizer != null');
                            $clean = $attributeValueSanitizer::isValueAcceptable();
                            //if this is owner, and we selected username, how do we pass that info here? - i think this is answered above.
                            //??? are we looping based on data provider to run this saniization test or what?

                            //??? is the below done after the loop or before?
                            $importAcceptableAttributeValuesCheckResults->addCheckResult(
                                $importColumnName, $clean, $attributeValueSanitizer);
                        }
                    }
                }
        }
    }
?>