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

    class AuditEventsModalListView extends ListView
    {
        public function __construct($controllerId, $moduleId, $modelClassName, $dataProvider, $gridIdSuffix = null)
        {
            parent::__construct($controllerId, $moduleId, $modelClassName, $dataProvider, array(), false, $gridIdSuffix);
            $this->rowsAreSelectable = false;
        }

        /**
         * Override to remove action buttons.
         */
        protected function getCGridViewLastColumn()
        {
            return array();
        }

        protected function getCGridViewPagerParams()
        {
            return array(
                    'cssFile'          => Yii::app()->baseUrl . '/themes/' . Yii::app()->theme->name . '/css/cgrid-view.css',
                    'prevPageLabel'    => '<span>previous</span>',
                    'nextPageLabel'    => '<span>next</span>',
                    'paginationParams' => GetUtil::getData(),
                    'route'            => $this->getGridViewActionRoute('auditEventsModalList', $this->moduleId),
                    'class'            => 'SimpleListLinkPager',
                );
        }

        /**
         * Override to not run global eval, since it causes doubling up of ajax requests on the pager.
         * (non-PHPdoc)
         * @see ListView::getCGridViewAfterAjaxUpdate()
         */
        protected function getCGridViewAfterAjaxUpdate()
        {
            // Begin Not Coding Standard
            return 'js:function(id, data) {
                        processAjaxSuccessError(id, data);
                    }';
            // End Not Coding Standard
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'derivedAttributeTypes' => array(
                        'AuditEvent',
                    ),
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'dateTime', 'type' => 'DateTime',
                                                      'htmlOptions' => array('nowrap' => 'nowrap')),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'user', 'type' => 'User'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'AuditEvent'),
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
    }
?>
