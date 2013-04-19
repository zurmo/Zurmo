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
     * View for displaying a row of attribute information for a component
     */
    abstract class AttributeRowForWizardModelComponentView extends View
    {
        /**
         * @var bool
         */
        public    $addWrapper = true;

        /**
         * @var WizardModelAttributeToElementAdapter
         */
        protected $elementAdapter;

        /**
         * @var int
         */
        protected $rowNumber;

        /**
         * @var array
         */
        protected $inputPrefixData;

        /**
         * @var string
         */
        protected $attribute;

        /**
         * @var bool
         */
        protected $hasTrackableStructurePosition;

        /**
         * @var bool
         */
        protected $showRemoveLink;

        /**
         * @var string
         */
        protected $treeType;

        public static function getFormId()
        {
            return WizardView::getFormId();
        }

        /**
         * @param $elementAdapter
         * @param integer $rowNumber
         * @param array $inputPrefixData
         * @param string $attribute
         * @param bool $hasTrackableStructurePosition
         * @param bool $showRemoveLink
         * @param string $treeType
         * @throws NotSupportedException if the remove link should be shown but the tree type is null
         */
        public function __construct($elementAdapter, $rowNumber, $inputPrefixData, $attribute,
                                    $hasTrackableStructurePosition, $showRemoveLink = true, $treeType)
        {
            assert('$elementAdapter instanceof WizardModelAttributeToElementAdapter');
            assert('is_int($rowNumber)');
            assert('is_array($inputPrefixData)');
            assert('is_string($attribute)');
            assert('is_bool($hasTrackableStructurePosition)');
            assert(is_bool($showRemoveLink)); // Not Coding Standard
            assert('$treeType == null || is_string($treeType)');
            $this->elementAdapter                     = $elementAdapter;
            $this->rowNumber                          = $rowNumber;
            $this->inputPrefixData                    = $inputPrefixData;
            $this->attribute                          = $attribute;
            $this->hasTrackableStructurePosition      = $hasTrackableStructurePosition;
            $this->showRemoveLink                     = $showRemoveLink;
            $this->treeType                           = $treeType;
            if ($showRemoveLink && $treeType == null)
            {
                throw new NotSupportedException();
            }
        }

        public function render()
        {
            return $this->renderContent();
        }

        /**
         * @return string
         */
        protected function renderContent()
        {
            $content  = '<div>';
            $resolvedHasFilterOrTriggerClass = null;
            if ($this->hasTrackableStructurePosition)
            {
                $content .= $this->renderAttributeRowNumberLabel();
                $content .= $this->renderHiddenStructurePositionInput();
                $resolvedHasFilterOrTriggerClass = ' ' . $this->getHasFilterOrTriggerClass();
            }
            $content .= $this->renderAttributeContent();
            $content .= '</div>';
            if ($this->showRemoveLink)
            {
                $content .= ZurmoHtml::link('—', '#', array('class' => 'remove-dynamic-row-link ' . $this->treeType));
            }
            $content  =  ZurmoHtml::tag('div', array('class' => "dynamic-row{$resolvedHasFilterOrTriggerClass}"), $content);
            if ($this->addWrapper)
            {
                return ZurmoHtml::tag('li', array(), $content);
            }
            return $content;
        }

        protected function getHasFilterOrTriggerClass()
        {
            return 'hasFilter';
        }

        /**
         * @return string
         */
        protected function renderAttributeRowNumberLabel()
        {
            return ZurmoHtml::tag('span', array('class' => 'dynamic-row-number-label'),
                                          ($this->rowNumber + 1) . '.');
        }

        /**
         * @return string
         */
        protected function renderHiddenStructurePositionInput()
        {
            $hiddenInputName     = Element::resolveInputNamePrefixIntoString(
                                            array_merge($this->inputPrefixData, array('structurePosition')));
            $hiddenInputId       = Element::resolveInputIdPrefixIntoString(
                                            array_merge($this->inputPrefixData, array('structurePosition')));
            $idInputHtmlOptions  = array('id' => $hiddenInputId, 'class' => 'structure-position');
            return ZurmoHtml::hiddenField($hiddenInputName, ($this->rowNumber + 1), $idInputHtmlOptions);
        }

        /**
         * @return string
         */
        protected function renderAttributeContent()
        {
            $content = $this->elementAdapter->getContent();
            return $content;
        }
    }
?>