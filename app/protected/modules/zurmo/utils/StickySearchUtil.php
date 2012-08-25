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
     * Helper class for working with Sticky searches
     */
    class StickySearchUtil
    {
        public static function clearDataByKey($key)
        {
            assert('is_string($key)');
            Yii::app()->user->setState($key, null);
        }

        public static function getDataByKey($key)
        {
            assert('is_string($key)');
            $stickyData = Yii::app()->user->getState($key);
            if ($stickyData == null)
            {
                return null;
            }
            return unserialize($stickyData);
        }

        /**
         * Given an offset and pageSize, determine what the list offset should be in order to maximize how the list
         * displays in the user interface
         * @param Integer $stickyOffset
         * @param Integer $pageSize
         * @return Integer $finalOffset
         */
        public static function resolveFinalOffsetForStickyList($stickyOffset, $pageSize, $totalCount)
        {
            assert('is_int($stickyOffset)');
            assert('is_int($pageSize)');
            assert('is_int($totalCount)');
            if ($stickyOffset == 0)
            {
                $finalOffset = 0;
            }
            elseif ($pageSize >= $totalCount)
            {
                $finalOffset = 0;
            }
            else
            {
                //lower boundry of half the page size from stickyOffset
                $lowerBoundryOffset = $stickyOffset - round($pageSize / 2);
                //upper boundry of half the page size from stickyOffset
                $upperBoundryOffset = $stickyOffset + round($pageSize / 2);
                if ($lowerBoundryOffset < 0)
                {
                    $finalOffset = 0;
                }
                elseif ($upperBoundryOffset > ($totalCount -1))
                {
                    $finalOffset = $lowerBoundryOffset - ($upperBoundryOffset - ($totalCount- 1));
                }
                else
                {
                    $finalOffset = $lowerBoundryOffset;
                }
            }
            return (int)$finalOffset;
        }

        public static function resolveBreadCrumbViewForDetailsControllerAction(CController $controller, $stickySearchKey,
                                                                           RedBeanModel $model)
        {
            assert('is_string($stickySearchKey)');
            if (ArrayUtil::getArrayValue(GetUtil::getData(), 'stickyOffset') !== null &&
               StickySearchUtil::getDataByKey($stickySearchKey) != null)
            {
                 $stickyLoadUrl = Yii::app()->createUrl($controller->getModule()->getId() . '/' . $controller->getId() . '/renderStickyListBreadCrumbContent',
                                                        array('stickyKey'     => $stickySearchKey,
                                                              'stickyOffset'  => ArrayUtil::getArrayValue(GetUtil::getData(), 'stickyOffset'),
                                                              'stickyModelId' => $model->id));
            }
            else
            {
                $stickyLoadUrl = null;
            }
            return new StickyDetailsAndRelationsBreadCrumbView($controller->getId(),
                                                               $controller->getModule()->getId(),
                                                               array(strval($model)),
                                                               $controller->getModule()->getModuleLabelByTypeAndLanguage('Plural'),
                                                               $stickyLoadUrl);
        }
    }
?>