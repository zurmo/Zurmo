<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    class ContactWebFormEditAndDetailsView extends SecuredEditAndDetailsView
    {
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type' => 'SaveButton', 'renderType' => 'Edit'),
                            array('type' => 'CancelLink', 'renderType' => 'Edit'),
                            array('type' => 'EditLink', 'renderType' => 'Details'),
                            array('type' => 'AuditEventsModalListLink', 'renderType' => 'Details'),
                            array('type' => 'ContactWebFormDeleteLink', 'renderType' => 'Details'),
                        ),
                    ),
                    'derivedAttributeTypes' => array(
                        'DerivedExplicitReadWriteModelPermissions'
                    ),
                    'panelsDisplayType' => FormLayout::PANELS_DISPLAY_TYPE_ALL,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'name', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'redirectUrl', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'submitButtonLabel', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'AllContactStatesDropDownForContactWebForm'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'defaultOwner', 'type' => 'User'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null',
                                                      'type'          => 'DerivedExplicitReadWriteModelPermissions'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'excludeStyles', 'type' => 'CheckBox'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'serializedData', 'type' => 'SortableContactWebFormAttributes'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        protected function getNewModelTitleLabel()
        {
            return Zurmo::t('ContactWebFormsModule', 'Create ContactWebFormsModuleSingularLabel',
                             LabelUtil::getTranslationParamsForAllModules());
        }

        protected function renderRightSideFormLayoutForEdit($form)
        {
            return null;
        }

        protected function renderBeforeFormLayoutForDetailsContent()
        {
            $embedScript = '<div id="zurmoExternalWebForm">' .
                            '<script type="text/javascript" ' .
                            'src="' . Yii::app()->createAbsoluteUrl('contacts/external/sourceFiles/', array('id' => $this->model->id)) . '">' .
                            '</script></div>';
            $title = ZurmoHtml::tag('h3', array(), Zurmo::t('ContactWebFormsModule', 'Copy/Paste this code to your web page..'));
            return '<div class="webform-embed-code">' . $title . '<textarea onclick="this.focus();this.select()" readonly="readonly">' . htmlspecialchars($embedScript) . '</textarea></div>';
        }
    }
?>