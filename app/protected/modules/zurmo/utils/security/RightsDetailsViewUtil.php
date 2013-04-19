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
     * Helper class to dynamically generate
     * view metadata based on rightsData array.
     */
    class RightsDetailsViewUtil extends SecurityViewUtil
    {
        /**
         * Makes right metadata panels grouped by module
         * @return array - view metadata
         */
        protected static function makeMetadataFromData($data)
        {
            $data            = static::resolveModuleLabelAndSort($data);
            $calledClassName = get_called_class();
            $panelCount      = 0;
            $metadata = array(
                'global' => array(
                    'panels' => array(
                        array(
                            'rows' => array(
                            ),
                        ),
                    ),
                )
            );
            foreach ($data as $moduleClassName => $moduleLabelAndItems)
            {
                $elements = array();
                foreach ($moduleLabelAndItems['items'] as $item => $itemInformation)
                {
                    $element = $calledClassName::getElementInformation(
                        $moduleClassName,
                        $item,
                        $itemInformation);
                    if ($element != null)
                    {
                        $elements[] = $element;
                    }
                }
                if (count($elements) > 0)
                {
                    $metadata['global']['panels'][$panelCount]['title']      = $moduleLabelAndItems['label'];
                    foreach ($elements as $element)
                    {
                        $metadata['global']['panels'][$panelCount]['rows'][] = $calledClassName::getRowByElement($element);
                    }
                    $panelCount++;
                }
            }
            return $metadata;
        }

        protected static function resolveModuleLabelAndSort($data)
        {
            $classAndLabels = array();
            foreach ($data as $moduleClassName => $moduleItems)
            {
                $classAndLabels[$moduleClassName] = $moduleClassName::getModuleLabelByTypeAndLanguage('Plural');
            }
            asort($classAndLabels);
            $sortedData = array();
            foreach ($classAndLabels as $moduleClassName => $label)
            {
                $sortedData[$moduleClassName] = array('label' => $label, 'items' => $data[$moduleClassName]);
            }
            return $sortedData;
        }
    }
?>