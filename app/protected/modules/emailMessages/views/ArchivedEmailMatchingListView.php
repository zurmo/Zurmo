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
     * View is used to display rows of email messages that are archived but not connected to any contacts or leads.
     */
    class ArchivedEmailMatchingListView extends SecuredListView
    {
       /**
        * Override to set rows as not being selectable.
        */
       public function __construct(
            $controllerId,
            $moduleId,
            $modelClassName,
            $dataProvider
        )
        {
            parent::__construct($controllerId, $moduleId, $modelClassName, $dataProvider, array());
            $this->rowsAreSelectable = false;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'derivedAttributeTypes' => array(
                        'ArchivedEmailMatching',
                    ),
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'ArchivedEmailMatching'),
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

        /**
         * Override so the edit link does not show.
         * (non-PHPdoc)
         * @see SecuredListView::getCGridViewLastColumn()
         */
        protected function getCGridViewLastColumn()
        {
            return array();
        }

        /**
         * Override so the pagination link works properly.
         * (non-PHPdoc)
         * @see ListView::getCGridViewPagerParams()
         */
        protected function getCGridViewPagerParams()
        {
            $defaultGridViewPagerParams = array(
                        'prevPageLabel'    => '<span>previous</span>',
                        'nextPageLabel'    => '<span>next</span>',
                        'class'            => 'EndlessListLinkPager',
                        'paginationParams' => GetUtil::getData(),
                        'route'            => $this->getGridViewActionRoute('matchingList', $this->moduleId),
                    );
            if (empty($this->gridViewPagerParams))
            {
                return $defaultGridViewPagerParams;
            }
            else
            {
                return array_merge($defaultGridViewPagerParams, $this->gridViewPagerParams);
            }
        }
    }
?>