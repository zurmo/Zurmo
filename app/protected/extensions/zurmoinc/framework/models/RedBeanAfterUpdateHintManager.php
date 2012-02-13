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

    // http://groups.google.com/group/redbeanorm/browse_thread/thread/7eb59797e8478a89/61eae7941cae1970
    // This was supplied in the RedBean forum by Gabor.
    class RedBeanAfterUpdateHintManager implements RedBean_Observer
    {
        protected $dateOptimizer;
        protected $datetimeOptimizer;
        protected $idOptimizer;

        public function __construct($toolbox)
        {
            $this->dateOptimizer     = new RedBean_Plugin_Optimizer_Date    ($toolbox);
            $this->datetimeOptimizer = new RedBean_Plugin_Optimizer_Datetime($toolbox);
            $this->idOptimizer       = new RedBean_Plugin_Optimizer_Id      ($toolbox);
        }

        public function onEvent($type, $info)
        {
            assert('$type == "after_update"');
            if (RedBeanDatabase::isFrozen())
            {
                return;
            }
            $hints = $info->getMeta("hint");
            if ($hints !== null)
            {
                assert('is_array($hints)');
                foreach ($hints as $key => $value)
                {
                    switch ($value)
                    {
                    case 'date':
                        $this->dateOptimizer    ->setTable($info->getMeta("type"));
                        $this->dateOptimizer    ->setColumn($key);
                        $this->dateOptimizer    ->setValue($info->$key);
                        $this->dateOptimizer    ->optimize();
                        break;

                    case 'datetime':
                        $this->datetimeOptimizer->setTable($info->getMeta("type"));
                        $this->datetimeOptimizer->setColumn($key);
                        $this->datetimeOptimizer->setValue($info->$key);
                        $this->datetimeOptimizer->optimize();
                        break;

                    case 'id':
                        $this->idOptimizer      ->setTable($info->getMeta("type"));
                        $this->idOptimizer      ->setColumn($key);
                        $this->idOptimizer      ->setValue($info->$key);
                        $this->idOptimizer      ->optimize();
                        break;
                    }
                }
            }
        }
    }
?>
