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
     * Helper class for adapting relation and attribute data into tree data
     * Extended by adapters in reporting and workflow
     */
    class WizardModelRelationsAndAttributesToTreeAdapter
    {
        /**
         * @var string
         */
        protected $treeType;

        /**
         * @see ReportsDefaultController::actionAddAttributeFromTree for an example of where this is called from
         * The nodeId has the treeType as a prefix in order to distinguish from other nodes in the user interface.
         * @param string $nodeId
         * @param string $treeType
         * @return string nodeId without the prefixed treeType
         */
        public static function removeTreeTypeFromNodeId($nodeId, $treeType)
        {
            assert('is_string($nodeId)');
            assert('is_string($treeType)');
            $nodeIdParts  = explode($treeType . '_', $nodeId);
            return $nodeIdParts[1];
        }

        /**
         * @see ReportsDefaultController::actionAddAttributeFromTree for an example of where this is called from
         * @param string $formModelClassName
         * @param string $treeType
         * @param integer $rowNumber
         * @return array of input prefix parts.  Excludes the last element which is typically an attribute since this
         * is not part of the prefix for an Element.  Adds in the formModelClassName, a treeType, and then the rowNumber
         * as the first 2 elements.
         */
        public static function resolveInputPrefixData($formModelClassName, $treeType, $rowNumber)
        {
            assert('is_string($formModelClassName)');
            assert('is_string($treeType)');
            assert('is_int($rowNumber)');
            $inputPrefixData   = array();
            $inputPrefixData[] = $formModelClassName;
            $inputPrefixData[] = $treeType;
            $inputPrefixData[] = $rowNumber;
            return $inputPrefixData;
        }

        /**
         * Extracts the attribute which is the last part of the nodeId and @returns the attribute string.
         * @see ReportsDefaultController::actionAddAttributeFromTree for an example of where this is called from
         * @param string $nodeIdWithoutTreeType
         * @return string
         */
        public static function resolveAttributeByNodeId($nodeIdWithoutTreeType)
        {
            assert('is_string($nodeIdWithoutTreeType)');
            return $nodeIdWithoutTreeType;
        }

        /**
         * @return string
         */
        public function getTreeType()
        {
            return $this->treeType;
        }

        /**
         * @param string $relation
         * @param null|string  $nodeIdPrefix
         * @return string
         */
        protected function makeNodeId($relation, $nodeIdPrefix = null)
        {
            assert('is_string($relation)');
            assert('$nodeIdPrefix == null || is_string($nodeIdPrefix)');
            $content = null;
            if ($nodeIdPrefix != null)
            {
                $content .= $nodeIdPrefix;
            }
            $content .= $relation;
            return $this->treeType . '_' . $content;
        }

        /**
         * @param string $nodeId
         * @return null|string
         */
        protected function resolveNodeIdPrefixByNodeId($nodeId)
        {
            assert('is_string($nodeId)');
            if ($nodeId == 'source')
            {
                return null;
            }
            $relations    = explode(FormModelUtil::RELATION_DELIMITER, $nodeId);
            return implode(FormModelUtil::RELATION_DELIMITER, $relations) . FormModelUtil::RELATION_DELIMITER;
        }

        /**
         * @param $nodeId
         * @return string
         */
        protected function resolveNodeIdByRemovingTreeType($nodeId)
        {
            assert('is_string($nodeId)');
            if ($nodeId == 'source')
            {
                return $nodeId;
            }
            $nodeIdParts  = explode($this->treeType . '_', $nodeId);
            return $nodeIdParts[1];
        }
    }
?>