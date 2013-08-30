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
     * Base class for handling sequential processes for both data analysis and final data import
     */
    abstract class ImportSequentialProcess extends SequentialProcess
    {
        protected $import;

        protected $mappingData;

        protected $importRules;

        protected $dataProvider;

        protected static function resolvePageByParams($params)
        {
            assert('$params === null || is_array($params)');
            if (!isset($params['page']))
            {
                $page = 0;
            }
            else
            {
                $page = $params['page'];
            }
            return $page;
        }

        protected function resolveNextPagingAndParams($page, $params)
        {
            assert('$params === null || is_array($params)');
            $pageCount                             = $this->dataProvider->getPagination()->getPageCount();
            $pageSize                              = $this->dataProvider->getPagination()->getPageSize();
            $totalItemCount                        = $this->dataProvider->getTotalItemCount();
            $this->subSequenceCompletionPercentage = (($page + 1) / $pageCount) * 100;
            if (($page + 1) == $pageCount)
            {
                $this->nextStep    = 'complete';
                $this->setNextMessageByStep($this->nextStep);
                return null;
            }
            else
            {
                $params['page'] = ($page + 1);
                $this->nextStep = 'processRows';
                $this->setNextMessageByStep($this->nextStep);
                $startItemCount = (($page + 1) * $pageSize) + 1;
                if (($startItemCount + ($pageSize - 1) > $totalItemCount))
                {
                    $endItemCount = $totalItemCount;
                }
                else
                {
                    $endItemCount = ($page + 2) * $pageSize;
                }
                $labelParams = array('{startItemCount}' => $startItemCount,
                    '{endItemCount}'   => $endItemCount,
                    '{totalItemCount}' => $totalItemCount);
                $nextMessage = ' ' . Zurmo::t('ImportModule', 'Record(s) {startItemCount} - {endItemCount} of {totalItemCount}',
                    $labelParams);
                $this->nextMessage .= $nextMessage;
                return $params;
            }
        }

        protected function complete()
        {
            $this->nextStep    = null;
            $this->nextMessage = null;
            $this->complete    = true;
            return null;
        }
    }
?>