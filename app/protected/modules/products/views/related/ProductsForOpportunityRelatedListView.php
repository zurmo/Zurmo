<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class ProductsForOpportunityRelatedListView extends ProductsRelatedListView
    {
        /**
         * Override the panels and toolbar metadata.
         */
        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata['global']['toolbar']['elements'][] =
                                array('type'                 => 'ProductSelectFromRelatedListAjaxLink',
                                    'portletId'              => 'eval:$this->params["portletId"]',
                                    'relationAttributeName'  => 'eval:$this->getRelationAttributeName()',
                                    'relationModelId'        => 'eval:$this->params["relationModel"]->id',
                                    'relationModuleId'       => 'eval:$this->params["relationModuleId"]',
                                    'uniqueLayoutId'         => 'eval:$this->uniqueLayoutId',
                                //TODO: fix this 'eval' of $this->uniqueLayoutId above so that it can properly work being set/get from DB then getting evaluated
                                //currently it will not work correctly since in the db it would store a static value instead of it still being dynamic
                                    'ajaxOptions'            => 'eval:static::resolveAjaxOptionsForSelectList()',
                                    'htmlOptions'            => array( 'id' => 'SelectProductsForOpportunityFromRelatedListLink',
                                                                        'live' => false) //This is there are no double bindings
            );
            $metadata['global']['panels'] = array(
                array(
                    'rows' => array(
                        array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'name', 'type' => 'Text', 'isLink' => true),
                                            ),
                                        ),
                                    )
                        ),
                        array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'quantity', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                        ),
                        array('cells' =>
                            array(
                                array(
                                    'elements' => array(
                                        array('attributeName' => 'sellPrice', 'type' => 'CurrencyValue'),
                                    ),
                                ),
                            )
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        protected function getRelationAttributeName()
        {
            return 'opportunity';
        }

        public static function getDisplayDescription()
        {
            return Zurmo::t('ProductsModule', 'ProductsModulePluralLabel For OpportunitiesModuleSingularLabel',
                        LabelUtil::getTranslationParamsForAllModules());
        }

        protected function getUniquePageId()
        {
            return 'OpportunityProductsForPortletView';
        }

        protected static function resolveAjaxOptionsForSelectList()
        {
            $title = Zurmo::t('ProductTemplatesModule', 'ProductTemplatesModuleSingularLabel Search',
                LabelUtil::getTranslationParamsForAllModules());
            return ModalView::getAjaxOptionsForModalLink($title);
        }

        public static function getAllowedOnPortletViewClassNames()
        {
            return array('OpportunityDetailsAndRelationsView');
        }
    }
?>