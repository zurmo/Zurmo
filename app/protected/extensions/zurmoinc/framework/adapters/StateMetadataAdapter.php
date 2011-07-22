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

    /**
     * Adapter class to manipulate state information
     * for a module into metadata.
     */
    abstract class StateMetadataAdapter
    {
        protected $metadata;

        public function __construct(array $metadata)
        {
            assert('isset($metadata["clauses"])');
            assert('isset($metadata["structure"])');
            $this->metadata = $metadata;
        }

        /**
         * Creates where clauses and adds structure information
         * to existing DataProvider metadata.
         */
        public function getAdaptedDataProviderMetadata()
        {
            $metadata = $this->metadata;
            $stateIds = $this->getStateIds();
            if (empty($stateIds))
            {
                return $metadata;
            }
            $clauseCount = count($metadata['clauses']);
            $startingCount = $clauseCount + 1;
            $structure = '';
            $first = true;
            foreach ($stateIds as $stateId)
            {
                $metadata['clauses'][$startingCount] = array(
                    'attributeName' => 'state',
                    'operatorType'  => 'equals',
                    'value'         => $stateId
                );
                if (!$first)
                {
                    $structure .= ' or ';
                }
                $first = false;
                $structure .= $startingCount;
                $startingCount++;
            }
            if (empty($metadata['structure']))
            {
                $metadata['structure'] = $structure;
            }
            else
            {
                $metadata['structure'] = '(' . $metadata['structure'] . ') and (' . $structure . ')';
            }
            return $metadata;
        }

        /**
         * @return array of states that should be included
         * for the Module
         */
        abstract protected function getStateIds();
    }
?>