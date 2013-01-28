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
     * Used for testing with @see III model
     */
    class IIISearchFormTestModel extends DynamicSearchForm
    {
        public $IIIName;
        public $differentOperatorA;
        public $differentOperatorB;
        public $concatedName;

        public function __construct(RedBeanModel $model)
        {
            parent::__construct($model);
        }

        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('IIIName', 'safe'),
                array('differentOperatorA', 'safe'),
                array('differentOperatorB', 'boolean'),
                array('dateDateTimeADate__Date', 'safe'),
                array('dateDateTimeADateTime__DateTime', 'safe'),
                array('concactedName', 'safe'),
            ));
        }

        public function attributeLabels()
        {
            return array_merge(parent::attributeLabels(), array(
                'concactedName'                    => Zurmo::t('Core', 'Concated Name'),
                'IIIName'                          => Zurmo::t('Core', 'IIIName'),
                'differentOperatorA'               => Zurmo::t('Core', 'differentOperatorA'),
                'differentOperatorB'               => Zurmo::t('Core', 'differentOperatorB'),
            ));
        }

        protected static function getSearchFormAttributeMappingRulesTypes()
        {
            return array_merge(parent::getSearchFormAttributeMappingRulesTypes(), array('differentOperatorA' => 'OwnedItemsOnly'));
        }

        public function getAttributesMappedToRealAttributesMetadata()
        {
            return array_merge(parent::getAttributesMappedToRealAttributesMetadata(), array(
                'IIIName' => array(
                    array('iiiMember'),
                    array('iiiMember2'),
                ),
                'differentOperatorA' => array(
                    array('iiiMember', null, null, 'resolveValueByRules'),
                ),
                'differentOperatorB' => array(
                    array('iiiMember', null, 'endsWith')
                ),
                'concatedName' => array(
                    array('iiiMember'),
                    array('iiiMember2'),
                    array('concatedAttributeNames' => array('iiiMember', 'iiiMember2'))
                ),
            ));
        }
    }
?>