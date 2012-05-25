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
     * Special modal view utilized by calendar to show meetings on a particular day.
     */
    class DaysMeetingsFromCalendarModalListView extends ListView
    {
        protected $redirectUrl;

        protected $ownerOnly = false;

        public function __construct($controllerId, $moduleId, $stringTime, $redirectUrl,
                                    $ownerOnly = false, $relationModel = null)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($stringTime)');
            assert('is_string($redirectUrl) || $redirectUrl == null');
            assert('is_bool($ownerOnly)');
            assert('$relationModel == null || $relationModel instanceof RedBeanModel');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->stringTime             = $stringTime;
            $this->redirectUrl            = $redirectUrl;
            $this->modelClassName         = 'Meeting';
            $this->gridId                 = 'days-meetings-list-view';
            $this->rowsAreSelectable      = false;
            $this->ownerOnly              = $ownerOnly;
            $this->relationModel          = $relationModel;
        }

        protected function getCGridViewPagerParams()
        {
            return array(
                    'cssFile' => Yii::app()->baseUrl . '/themes/' . Yii::app()->theme->name . '/css/cgrid-view.css',
                    'prevPageLabel' => '<span>previous</span>',
                    'nextPageLabel' => '<span>next</span>',
                    'class'          => 'SimpleListLinkPager',
                    'paginationParams' => GetUtil::getData(),
                    'route'         => 'default/daysMeetingsFromCalendarModalList',
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

        /**
         * Override to remove action buttons.
         */
        protected function getCGridViewLastColumn()
        {
            return array();
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'nonPlaceableAttributeNames' => array(
                        'latestDateTime',
                    ),
                    'derivedAttributeTypes' => array(
                        'MeetingDaySummary',
                    ),
                    'gridViewType' => RelatedListView::GRID_VIEW_TYPE_STACKED,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'MeetingDaySummary'),
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

        protected function getGridViewWidgetPath()
        {
            $resolvedMetadata = $this->getResolvedMetadata();
            if (isset($resolvedMetadata['global']['gridViewType']) &&
                     $resolvedMetadata['global']['gridViewType'] == RelatedListView::GRID_VIEW_TYPE_STACKED)
             {
                 return 'ext.zurmoinc.framework.widgets.StackedExtendedGridView';
             }

            return parent::getGridViewWidgetPath();
        }

        /**
         * Override to handle security/access resolution on links.
         */
        public function getLinkString($attributeString)
        {
            $string  = 'ActionSecurityUtil::resolveLinkToEditModelForCurrentUser("' . $attributeString . '", ';
            $string .= '$data, "' . $this->getActionModuleClassName() . '", ';
            $string .= '"' . $this->getGridViewActionRoute('edit') . '", "' . $this->redirectUrl . '")';
            return $string;
        }

        protected function makeSearchAttributeData()
        {
            assert('!($this->ownerOnly && $this->relationModel != null)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'startDateTime',
                    'operatorType'         => 'greaterThanOrEqualTo',
                    'value'                => DateTimeUtil::
                                                convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($this->stringTime)
                ),
                2 => array(
                    'attributeName'        => 'startDateTime',
                    'operatorType'         => 'lessThanOrEqualTo',
                    'value'                => DateTimeUtil::
                                                convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($this->stringTime)
                )
                );
            $searchAttributeData['structure'] = '(1 and 2)';
            if ($this->ownerOnly)
            {
                $searchAttributeData['clauses'][3] =
                array(
                    'attributeName'        => 'owner',
                    'operatorType'         => 'equals',
                    'value'                => Yii::app()->user->userModel->id,
                );
                $searchAttributeData['structure'] = '(1 and 2 and 3)';
            }
            //The assertion above ensures that either ownerOnly or relationModel is populated but not both.
            if ($this->relationModel != null)
            {
                $searchAttributeData['clauses'][3] =
                array(
                    'attributeName'        => 'activityItems',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => (int)$this->relationModel->getClassId('Item')
                );
                $searchAttributeData['structure'] = '(1 and 2 and 3)';
            }
            return $searchAttributeData;
        }

        protected function makeDataProviderBySearchAttributeData($searchAttributeData)
        {
            assert('is_array($searchAttributeData)');
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType('subListPageSize');
            return new RedBeanModelDataProvider( $this->modelClassName, null, false,
                                                                $searchAttributeData, array(
                                                                    'pagination' => array(
                                                                        'pageSize' => $pageSize,
                                                                    )
                                                                ));
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        protected function getDataProvider()
        {
            if ($this->dataProvider == null)
            {
                $this->dataProvider = $this->makeDataProviderBySearchAttributeData($this->makeSearchAttributeData());
            }
            return $this->dataProvider;
        }
    }
?>
