<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
    class RedBeanBeforeUpdateHintManager implements RedBean_Observer
    {
        protected $blobOptimizer;
        protected $booleanOptimizer;

        public function __construct($toolbox)
        {
            $this->blobOptimizer     = new RedBean_Plugin_Optimizer_Blob($toolbox);
            $this->booleanOptimizer  = new RedBean_Plugin_Optimizer_Boolean($toolbox);
        }

        public function onEvent($type, $info)
        {
            assert('$type == "update"');
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
                    case 'blob':
                        $this->blobOptimizer->setTable($info->getMeta("type"));
                        $this->blobOptimizer->setColumn($key);
                        $this->blobOptimizer->setValue($info->$key);
                        $this->blobOptimizer->optimize();
                        break;
                    case 'longblob':
                        $this->blobOptimizer->setTable($info->getMeta("type"));
                        $this->blobOptimizer->setColumn($key);
                        $this->blobOptimizer->setValue($info->$key);
                        $this->blobOptimizer->optimize('longblob');
                        break;
                   case 'boolean':
                        $this->booleanOptimizer ->setTable($info->getMeta("type"));
                        $this->booleanOptimizer ->setColumn($key);
                        $this->booleanOptimizer ->setValue($info->$key);
                        $this->booleanOptimizer ->optimize();
                        break;
                    }
                }
            }
        }
    }
?>
