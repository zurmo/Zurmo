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
     * Class to help resolve variable states.  When a filter is used from a related model, if that model's module has
     * variable states, then the appropriate states must be added as additional attribute data to later be filtered on.
     * If for example you are using an account's contact's name, then you need to filter contacts by states that do not
     * include leads.
     */
    class VariableStatesForReportUtil extends ComponentTraversalUtil
    {
        /**
         * @param $modelClassName
         * @param array $attributeIndexes
         * @param null $attributeIndexPrefix
         * @throws PartialRightsForReportSecurityException if the current user is lacking rights to at least one of the
         * states.
         */
        public static function resolveAttributeIndexes($modelClassName, & $attributeIndexes, $attributeIndexPrefix = null)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeIndexPrefix) || $attributeIndexPrefix == null');
            $moduleClassName = $modelClassName::getModuleClassName();
            if (null != $stateMetadataAdapterClassName = $moduleClassName::getStateMetadataAdapterClassName())
            {
                $reportRules  = ReportRules::makeByModuleClassName($moduleClassName);
                $stateAdapterClassName =  $reportRules->resolveStateAdapterUserHasAccessTo(Yii::app()->user->userModel);
                if ($stateAdapterClassName !== null && $stateAdapterClassName !== false)
                {
                    $stateAttributeName = $stateAdapterClassName::getStateAttributeName();
                    $stateAdapter       = new $stateAdapterClassName(array('clauses' => array(), 'structure' => ''));
                    $attributeIndexes[$attributeIndexPrefix] = array($stateAttributeName, $stateAdapter->getStateIds());
                }
                elseif ($stateAdapterClassName === false)
                {
                    throw new PartialRightsForReportSecurityException();
                }
            }
        }
    }
?>
