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

    /**
     * Class that builds demo autoresponderItems.
     */
    class AutoresponderItemsDemoDataMaker extends DemoDataMaker
    {
        protected $ratioToLoad = 3;

        public static function getDependencies()
        {
            return array('contacts', 'marketingLists');
        }

        /**
         * @param DemoDataHelper $demoDataHelper
         */
        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            assert('$demoDataHelper->isSetRange("Contact")');
            assert('$demoDataHelper->isSetRange("Autoresponder")');

            $items = array();
            if ($this->loadMagnitude >= 100)
            {
                foreach (Autoresponder::getAll() as $autoresponder)
                {
                    foreach ($autoresponder->marketingList->marketingListMembers as $marketingListMember)
                    {
                        $item                   = new AutoresponderItem();
                        $item->autoresponder    = $autoresponder;
                        $item->contact          = $marketingListMember->contact;
                        $this->populateModel($item);
                        $saved                  = $item->unrestrictedSave();
                        assert('$saved');
                        $items[]                = $item->id;
                    }
                }
            }
            else
            {
                for ($i = 0; $i < $this->resolveQuantityToLoad(); $i++)
                {
                    $item                   = new AutoresponderItem();
                    $item->autoresponder    = $demoDataHelper->getRandomByModelName('Autoresponder');
                    $item->contact          = $demoDataHelper->getRandomByModelName('Contact');
                    $this->populateModel($item);
                    $saved                  = $item->unrestrictedSave();
                    assert('$saved');
                    $items[]                = $item->id;
                }
            }
            $demoDataHelper->setRangeByModelName('AutoresponderItem', $items[0], $items[count($items)-1]);
        }

        /**
         * @param AutoresponderItem $model
         */
        public function populateModel(& $model)
        {
            assert('$model instanceof AutoresponderItem');
            parent::populateModel($model);
            $model->processed       = (rand() % 2);
            $difference             = rand(1000, 6000);
            if ($model->processed)
            {
                $processTimestamp = time() - $difference;
            }
            else
            {
                $processTimestamp = time() + $difference;
            }
            $model->processDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($processTimestamp);
        }
    }
?>