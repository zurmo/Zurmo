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
     * Helper class for working with Sticky searches
     */
    class StickySearchUtil extends StickyUtil
    {
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
            $className = static::resolveStickyDetailsAndRelationsBreadCrumbViewClassName();
            return new $className($controller->getId(), $controller->getModule()->getId(),
                                  static::resolveBreadcrumbLinks($model),
                                  $controller->getModule()->getModuleLabelByTypeAndLanguage('Plural'), $stickyLoadUrl);
        }

        protected static function resolveStickyDetailsAndRelationsBreadCrumbViewClassName()
        {
            return 'StickyDetailsAndRelationsBreadCrumbView';
        }

        protected static function resolveBreadcrumbLinks(RedBeanModel $model)
        {
            return array(strval($model));
        }
    }
?>