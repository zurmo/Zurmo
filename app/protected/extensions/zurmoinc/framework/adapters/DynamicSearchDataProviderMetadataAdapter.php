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
     * Adapter class to manipulate dynamic search information for metadata.
     */
    class DynamicSearchDataProviderMetadataAdapter
    {
        const NOT_USED_STRUCTURE_POSITION = 'notUsed';

        protected $metadata;

        protected $model;

        protected $userId;

        protected $sanitizedDynamicSearchAttributes;

        protected $dynamicStructure;

        public function __construct(array $metadata, SearchForm $model, $userId, $sanitizedDynamicSearchAttributes, $dynamicStructure)
        {
            assert('array($metadata)');
            assert('isset($metadata["clauses"])');
            assert('isset($metadata["structure"])');
            assert('is_int($userId)');
            assert('is_array($sanitizedDynamicSearchAttributes) && count($sanitizedDynamicSearchAttributes)  > 0');
            assert('is_string($dynamicStructure)');
            $this->metadata                         = $metadata;
            $this->model                            = $model;
            $this->userId                           = $userId;
            $this->sanitizedDynamicSearchAttributes = $sanitizedDynamicSearchAttributes;
            $this->dynamicStructure                 = $dynamicStructure;
        }

        /**
         * Creates where clauses and adds structure information
         * to existing DataProvider metadata.
         */
        public function getAdaptedDataProviderMetadata()
        {
            $metadata                   = $this->metadata;
            $clauseCount                = count($metadata['clauses']);
            $structure                  = $this->dynamicStructure;
            $correctlyPositionedClauses = array();
            $this->processData($this->sanitizedDynamicSearchAttributes, $clauseCount, $correctlyPositionedClauses, $metadata);
            krsort($correctlyPositionedClauses);
            //Resolve any unused clauses first before replacing real clauses.
            foreach ($correctlyPositionedClauses as $position => $correctlyPositionedClauseData)
            {
                if ($correctlyPositionedClauseData[$position] == self::NOT_USED_STRUCTURE_POSITION)
                {
                    $structure = strtr(strtolower($structure), $correctlyPositionedClauseData);
                    unset($correctlyPositionedClauses[$position]);
                }
            }
            //Replace clauses still used.
            foreach ($correctlyPositionedClauses as $position => $correctlyPositionedClauseData)
            {
                $structure = strtr(strtolower($structure), $correctlyPositionedClauseData);
            }
            //Now resolve and remove any unused clauses and nearby operators.
            $structure = str_ireplace(' or '  . self::NOT_USED_STRUCTURE_POSITION,           '', $structure);
            $structure = str_ireplace(' and ' . self::NOT_USED_STRUCTURE_POSITION,           '', $structure);
            $structure = str_ireplace('('     . self::NOT_USED_STRUCTURE_POSITION . ' or ',  '(', $structure);
            $structure = str_ireplace('('     . self::NOT_USED_STRUCTURE_POSITION . ' and ', '(', $structure);
            if (empty($metadata['structure']))
            {
                $metadata['structure'] = '(' . $structure . ')';
            }
            else
            {
                $metadata['structure'] = '(' . $metadata['structure'] . ') and (' . $structure . ')';
            }
            return $metadata;
        }

        protected function processData($searchAttributes, & $clauseCount, & $correctlyPositionedClauses, & $metadata)
        {
            foreach ($searchAttributes as $position => $searchAttribute)
            {
                $structurePosition = self::resolveUnsetAndGetSructurePosition($searchAttribute);
                self::resolveUnsetAttributeIndexOrDerivedType($searchAttribute);
                $metadataAdapter = new SearchDataProviderMetadataAdapter(
                    $this->model,
                    $this->userId,
                    $searchAttribute
                );
                $searchItemMetadata = $metadataAdapter->getAdaptedMetadata(true, ($clauseCount + 1));
                if (count($searchItemMetadata['clauses']) > 0)
                {
                    $metadata['clauses']                            = $metadata['clauses'] + $searchItemMetadata['clauses'];
                    $clauseCount                                    = $clauseCount + count($searchItemMetadata['clauses']);
                    $correctlyPositionedClauses
                        [$structurePosition][$structurePosition]    = $searchItemMetadata['structure'];
                }
                else
                {
                    $correctlyPositionedClauses
                        [$structurePosition][$structurePosition]    = self::NOT_USED_STRUCTURE_POSITION;
                }
            }
        }

        protected static function resolveUnsetAndGetSructurePosition(& $searchAttribute)
        {
            if (isset($searchAttribute['structurePosition']))
            {
                $structurePosition = $searchAttribute['structurePosition'];
                unset($searchAttribute['structurePosition']);
                return $structurePosition;
            }
        }

        protected static function resolveUnsetAttributeIndexOrDerivedType(& $searchAttribute)
        {
            if (isset($searchAttribute['attributeIndexOrDerivedType']))
            {
                unset($searchAttribute['attributeIndexOrDerivedType']);
            }
        }
    }
?>