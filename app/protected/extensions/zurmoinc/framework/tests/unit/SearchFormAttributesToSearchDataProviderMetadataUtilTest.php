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

    class SearchFormAttributesToSearchDataProviderMetadataUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testGetMetadata()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $searchForm = new ASearchFormTestModel(new MixedRelationsModel());
            $metadata = SearchFormAttributesToSearchDataProviderMetadataUtil::getMetadata($searchForm, 'anyA', 'xyz');
            $compareData = array(array('primaryA'   => array('value' => array('name' => 'xyz'))),
                                 array('secondaryA' => array('value' => array('name' => 'xyz'))));
            $this->assertEquals($compareData, $metadata);

            $metadata = SearchFormAttributesToSearchDataProviderMetadataUtil::getMetadata($searchForm, 'ABName', 'abc');
            $compareData = array(array('aName' => array('value' => 'abc')),
                                 array('bName' => array('value' => 'abc')));
            $this->assertEquals($compareData, $metadata);

            $metadata = SearchFormAttributesToSearchDataProviderMetadataUtil::
                        getMetadata($searchForm, 'differentOperatorA', '1');
            $compareData = array(array('primaryA'   => array('value' => array('name' => $super->id))));
            $this->assertEquals($compareData, $metadata);

            $metadata = SearchFormAttributesToSearchDataProviderMetadataUtil::
                        getMetadata($searchForm, 'differentOperatorA', '');
            $compareData = array(array('primaryA'   => array('value' => array('name' => null))));
            $this->assertEquals($compareData, $metadata);

            $metadata = SearchFormAttributesToSearchDataProviderMetadataUtil::
                        getMetadata($searchForm, 'differentOperatorB', 'def');
            $compareData = array(array('aName'   => array('value' => 'def', 'operatorType' => 'endsWith')));
            $this->assertEquals($compareData, $metadata);
        }

        public function testGetMetadataForDynamicDateAttribute()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $searchForm = new ASearchFormTestModel(new MixedRelationsModel());

            //TEST when no value present
            $metadata = SearchFormAttributesToSearchDataProviderMetadataUtil::
                        getMetadata($searchForm, 'date__Date', null);
            $compareData = array(array('date' => array('value' => null)));
            $this->assertEquals($compareData, $metadata);

            //Test Date = Today
            $value              = array();
            $value['type']      = MixedDateTypesSearchFormAttributeMappingRules::TYPE_TODAY;
            $metadata           = SearchFormAttributesToSearchDataProviderMetadataUtil::
                                  getMetadata($searchForm, 'date__Date', $value);
            $todayDateTime      = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
            $today              = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                  $todayDateTime->getTimeStamp());
            $compareData        = array(array('date' => array('value' => $today, 'operatorType' => 'equals')));
            $this->assertEquals($compareData, $metadata);

            //Test Date = Tomorrow
            $value              = array();
            $value['type']      = MixedDateTypesSearchFormAttributeMappingRules::TYPE_TOMORROW;
            $metadata           = SearchFormAttributesToSearchDataProviderMetadataUtil::
                                  getMetadata($searchForm, 'date__Date', $value);
            $tomorrowDateTime   = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
            $tomorrow           = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                    $tomorrowDateTime->getTimeStamp() + (60 * 60 *24));
            $compareData        = array(array('date' => array('value' => $tomorrow, 'operatorType' => 'equals')));
            $this->assertEquals($compareData, $metadata);

            //Test Date = Yesterday
            $value              = array();
            $value['type']      = MixedDateTypesSearchFormAttributeMappingRules::TYPE_YESTERDAY;
            $metadata           = SearchFormAttributesToSearchDataProviderMetadataUtil::
                                  getMetadata($searchForm, 'date__Date', $value);
            $yesterdayDateTime  = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
            $yesterday          = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                  $yesterdayDateTime->getTimeStamp() - (60 * 60 *24));
            $compareData        = array(array('date' => array('value' => $yesterday, 'operatorType' => 'equals')));
            $this->assertEquals($compareData, $metadata);

            //Test Date = After X
            $value              = array();
            $value['type']      = MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER;
            $value['firstDate'] = '2011-05-05';
            $metadata           = SearchFormAttributesToSearchDataProviderMetadataUtil::
                                  getMetadata($searchForm, 'date__Date', $value);
            $compareData        = array(array('date' => array('value' => '2011-05-05',
                                                                'operatorType' => 'greaterThanOrEqualTo')));
            $this->assertEquals($compareData, $metadata);

            //Test Date = Before X
            $value              = array();
            $value['type']      = MixedDateTypesSearchFormAttributeMappingRules::TYPE_BEFORE;
            $value['firstDate'] = '2011-05-04';
            $metadata           = SearchFormAttributesToSearchDataProviderMetadataUtil::
                                  getMetadata($searchForm, 'date__Date', $value);
            $compareData        = array(array('date' => array('value' => '2011-05-04',
                                                              'operatorType' => 'lessThanOrEqualTo')));
            $this->assertEquals($compareData, $metadata);

            //Test Date = On X
            $value              = array();
            $value['type']      = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $value['firstDate'] = '2011-05-04';
            $metadata           = SearchFormAttributesToSearchDataProviderMetadataUtil::
                                  getMetadata($searchForm, 'date__Date', $value);
            $compareData        = array(array('date' => array('value' => '2011-05-04',
                                                              'operatorType' => 'equals')));
            $this->assertEquals($compareData, $metadata);

            //Test Date = Between X and Y
            $value              = array();
            $value['type']      = MixedDateTypesSearchFormAttributeMappingRules::TYPE_BETWEEN;
            $value['firstDate'] = '2011-05-04';
            $value['secondDate'] = '2011-06-04';
            $metadata           = SearchFormAttributesToSearchDataProviderMetadataUtil::
                                  getMetadata($searchForm, 'date__Date', $value);
            $compareData        = array(array('date' => array('value' => '2011-05-04',
                                                              'operatorType' => 'greaterThanOrEqualTo',
                                                              'appendStructureAsAnd' => true)),
                                        array('date' => array('value' => '2011-06-04',
                                                              'operatorType' => 'lessThanOrEqualTo',
                                                              'appendStructureAsAnd' => true)));
            $this->assertEquals($compareData, $metadata);

            //Test Date next 7 days
            $value              = array();
            $value['type']      = MixedDateTypesSearchFormAttributeMappingRules::TYPE_NEXT_7_DAYS;
            $metadata           = SearchFormAttributesToSearchDataProviderMetadataUtil::
                                  getMetadata($searchForm, 'date__Date', $value);
            $todayDateTime      = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
            $today              = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                  $todayDateTime->getTimeStamp());
            $compareData        = array(array('date' => array('value'                => $today,
                                                              'operatorType'         => 'greaterThanOrEqualTo',
                                                              'appendStructureAsAnd' => true)),
                                        array('date' => array('value' => MixedDateTypesSearchFormAttributeMappingRules::
                                                                         calculateNewDateByDaysFromNow(7),
                                                              'operatorType'         => 'lessThanOrEqualTo',
                                                              'appendStructureAsAnd' => true)));
            $this->assertEquals($compareData, $metadata);

            //Test Date last 7 days
            $value              = array();
            $value['type']      = MixedDateTypesSearchFormAttributeMappingRules::TYPE_LAST_7_DAYS;
            $metadata           = SearchFormAttributesToSearchDataProviderMetadataUtil::
                                  getMetadata($searchForm, 'date__Date', $value);
            $todayDateTime      = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
            $today              = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                  $todayDateTime->getTimeStamp());
            $compareData        = array(array('date' => array('value' => MixedDateTypesSearchFormAttributeMappingRules::
                                                                         calculateNewDateByDaysFromNow(-7),
                                                              'operatorType'         => 'greaterThanOrEqualTo',
                                                              'appendStructureAsAnd' => true)),
                                        array('date' => array('value'                => $today,
                                                              'operatorType'         => 'lessThanOrEqualTo',
                                                              'appendStructureAsAnd' => true)));
            $this->assertEquals($compareData, $metadata);
        }

        public function testGetMetadataForDynamicDateTimeAttribute()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $searchForm = new ASearchFormTestModel(new MixedRelationsModel());

            //Make sure the timeZone is different than UTC for testing.
            Yii::app()->user->userModel->timeZone = 'America/Chicago';

            //TEST when no value present
            $metadata = SearchFormAttributesToSearchDataProviderMetadataUtil::
                        getMetadata($searchForm, 'dateTime__DateTime', null);
            $compareData = array(array('dateTime' => array('value' => null)));
            $this->assertEquals($compareData, $metadata);

            //Test Date = Today
            $value              = array();
            $value['type']      = MixedDateTypesSearchFormAttributeMappingRules::TYPE_TODAY;
            $metadata           = SearchFormAttributesToSearchDataProviderMetadataUtil::
                                  getMetadata($searchForm, 'dateTime__DateTime', $value);
            $todayDateTime      = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
            $today              = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                  $todayDateTime->getTimeStamp());
            $compareData        = array(
                                    array('dateTime'  =>
                                        array('value' =>
                                            DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($today),
                                              'operatorType'         => 'greaterThanOrEqualTo',
                                              'appendStructureAsAnd' => true)),
                                    array('dateTime'  =>
                                        array('value' =>
                                            DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($today),
                                              'operatorType'         => 'lessThanOrEqualTo',
                                              'appendStructureAsAnd' => true)));
            $this->assertEquals($compareData, $metadata);

            //Test Date = Tomorrow
            $value              = array();
            $value['type']      = MixedDateTypesSearchFormAttributeMappingRules::TYPE_TOMORROW;
            $metadata           = SearchFormAttributesToSearchDataProviderMetadataUtil::
                                  getMetadata($searchForm, 'dateTime__DateTime', $value);
            $tomorrowDateTime   = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
            $tomorrow           = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                    $tomorrowDateTime->getTimeStamp() + (60 * 60 *24));
            $compareData        = array(
                                    array('dateTime'  =>
                                        array('value' =>
                                            DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($tomorrow),
                                              'operatorType'         => 'greaterThanOrEqualTo',
                                              'appendStructureAsAnd' => true)),
                                    array('dateTime'  =>
                                        array('value' =>
                                            DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($tomorrow),
                                              'operatorType'         => 'lessThanOrEqualTo',
                                              'appendStructureAsAnd' => true)));
            $this->assertEquals($compareData, $metadata);

            //Test Date = Yesterday
            $value              = array();
            $value['type']      = MixedDateTypesSearchFormAttributeMappingRules::TYPE_YESTERDAY;
            $metadata           = SearchFormAttributesToSearchDataProviderMetadataUtil::
                                  getMetadata($searchForm, 'dateTime__DateTime', $value);
            $yesterdayDateTime  = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
            $yesterday          = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                  $yesterdayDateTime->getTimeStamp() - (60 * 60 *24));
            $compareData        = array(
                                    array('dateTime'  =>
                                        array('value' =>
                                            DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($yesterday),
                                              'operatorType'         => 'greaterThanOrEqualTo',
                                              'appendStructureAsAnd' => true)),
                                    array('dateTime'  =>
                                        array('value' =>
                                            DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($yesterday),
                                              'operatorType'         => 'lessThanOrEqualTo',
                                              'appendStructureAsAnd' => true)));
            $this->assertEquals($compareData, $metadata);

            //Test Date = After X
            $value              = array();
            $value['type']      = MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER;
            $value['firstDate'] = '2011-05-05';
            $metadata           = SearchFormAttributesToSearchDataProviderMetadataUtil::
                                  getMetadata($searchForm, 'dateTime__DateTime', $value);
            $compareData        = array(
                                    array('dateTime'  =>
                                        array('value' => DateTimeUtil::
                                                         convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay('2011-05-05'),
                                              'operatorType'         => 'greaterThanOrEqualTo')));
            $this->assertEquals($compareData, $metadata);

            //Test Date = Before X
            $value              = array();
            $value['type']      = MixedDateTypesSearchFormAttributeMappingRules::TYPE_BEFORE;
            $value['firstDate'] = '2011-05-04';
            $metadata           = SearchFormAttributesToSearchDataProviderMetadataUtil::
                                  getMetadata($searchForm, 'dateTime__DateTime', $value);
            $compareData        = array(
                                    array('dateTime'  =>
                                        array('value' => DateTimeUtil::
                                                         convertDateIntoTimeZoneAdjustedDateTimeEndOfDay('2011-05-04'),
                                              'operatorType'         => 'lessThanOrEqualTo')));
            $this->assertEquals($compareData, $metadata);

            //Test Date next 7 days
            $value              = array();
            $value['type']      = MixedDateTypesSearchFormAttributeMappingRules::TYPE_NEXT_7_DAYS;
            $metadata           = SearchFormAttributesToSearchDataProviderMetadataUtil::
                                  getMetadata($searchForm, 'dateTime__DateTime', $value);
            $todayDateTime      = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
            $today              = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                  $todayDateTime->getTimeStamp());
            $todayPlus7Days     = MixedDateTypesSearchFormAttributeMappingRules::calculateNewDateByDaysFromNow(7);
            $compareData        = array(
                                    array('dateTime'  =>
                                        array('value' =>
                                            DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($today),
                                              'operatorType'         => 'greaterThanOrEqualTo',
                                              'appendStructureAsAnd' => true)),
                                    array('dateTime'  =>
                                        array('value' =>
                                            DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($todayPlus7Days),
                                              'operatorType'         => 'lessThanOrEqualTo',
                                              'appendStructureAsAnd' => true)));
            $this->assertEquals($compareData, $metadata);

            //Test Date last 7 days
            $value              = array();
            $value['type']      = MixedDateTypesSearchFormAttributeMappingRules::TYPE_LAST_7_DAYS;
            $metadata           = SearchFormAttributesToSearchDataProviderMetadataUtil::
                                  getMetadata($searchForm, 'dateTime__DateTime', $value);
            $todayDateTime      = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
            $today              = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                  $todayDateTime->getTimeStamp());
            $todayMinus7Days     = MixedDateTypesSearchFormAttributeMappingRules::calculateNewDateByDaysFromNow(-7);
            $compareData        = array(
                                    array('dateTime'  =>
                                        array('value' =>
                                            DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($todayMinus7Days),
                                              'operatorType'         => 'greaterThanOrEqualTo',
                                              'appendStructureAsAnd' => true)),
                                    array('dateTime'  =>
                                        array('value' =>
                                            DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($today),
                                              'operatorType'         => 'lessThanOrEqualTo',
                                              'appendStructureAsAnd' => true)));
            $this->assertEquals($compareData, $metadata);
        }
    }
?>