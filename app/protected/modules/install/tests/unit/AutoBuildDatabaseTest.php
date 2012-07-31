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
     * Special class to isolate the autoBuildDatabase method and test that the rows are the correct
     * count before and after running this method.  AutoBuildDatabase is used both on installation
     * but also during an upgrade or manually  to update the database schema based on any detected
     * changes.
     */
    class AutoBuildDatabaseTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::app()->gameHelper->muteScoringModelsOnSave();
        }

        public static function tearDownAfterClass()
        {
            Yii::app()->gameHelper->unmuteScoringModelsOnSave();
            parent::tearDownAfterClass();
        }

        public function testAutoBuildDatabase()
        {
            $unfreezeWhenDone     = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $unfreezeWhenDone = true;
            }
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $messageLogger              = new MessageLogger();
            $beforeRowCount             = DatabaseCompatibilityUtil::getTableRowsCountTotal();
            InstallUtil::autoBuildDatabase($messageLogger);
            $afterRowCount              = DatabaseCompatibilityUtil::getTableRowsCountTotal();
            //There are only 1 extra rows that are not being removed during the autobuild process.
            //These need to eventually be fixed so they are properly removed, except currency which is ok.
            //currency (1)
            $this->assertEquals($beforeRowCount, ($afterRowCount - 1));
            if ($unfreezeWhenDone)
            {
                RedBeanDatabase::freeze();
            }
        }

        public function testColumnType()
        {
            if (RedBeanDatabase::isFrozen())
            {
                $rootModels = array();
                foreach (Module::getModuleObjects() as $module)
                {
                    $moduleAndDependenciesRootModelNames    = $module->getRootModelNamesIncludingDependencies();
                    $rootModels                             = array_merge(  $rootModels,
                                                                        array_diff($moduleAndDependenciesRootModelNames,
                                                                        $rootModels));
                }

                foreach ($rootModels as $model)
                {
                    $meta = $model::getDefaultMetadata();
                    if (isset($meta[$model]['rules']))
                    {
                        foreach ($meta[$model]['rules'] as $rule)
                        {
                            if (is_array($rule) && count($rule) >= 3)
                            {
                                $attributeName       = $rule[0];
                                $validatorName       = $rule[1];
                                $validatorParameters = array_slice($rule, 2);
                                switch ($validatorName)
                                {
                                    case 'type':
                                        if (isset($validatorParameters['type']))
                                        {
                                            $type           = $validatorParameters['type'];
                                            $tableName      = RedBeanModel::getTableName($model);
                                            $field          = strtolower($attributeName);
                                            $row            = R::getRow("SHOW COLUMNS FROM $tableName where field='$field'");
                                            $compareType    = null;
                                            if ($row !== false)
                                            {
                                                $compareType = $this->getDbTypeValue($row['Type']);
                                            }
                                            $this->assertEquals($compareType, $type);
                                        }
                                        break;
                                }
                            }
                        }
                    }
                }
            }
        }

        protected function getDbTypeValue($value)
        {
            $typeArray = array(
                'string'    => array('CHAR', 'VARCHAR', 'TINYTEXT', 'TEXT', 'MEDIUMTEXT', 'LONGTEXT', 'ENUM', 'SET'),
                'integer'   => array('TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'INTEGER', 'BIGINT'),
                'float'     => array('FLOAT', 'DOUBLE', 'DECIMAL', 'NUMERIC'),
                'timestamp' => array('TIMESTAMP'),
                'year'      => array('YEAR'),
                'date'      => array('DATE'),
                'time'      => array('TIME'),
                'datetime'  => array('DATETIME'),
                'blob'      => array('TINY_BLOB', 'MEDIUM_BLOB', 'LONG_BLOB', 'BLOB')
            );
            $value              = strtoupper($value);
            $startCuttingPos    = stripos($value, '(');
            $searchValue        = $value;
            if ($startCuttingPos !== false)
            {
                $searchValue = substr($value, 0, $startCuttingPos);
            }
            foreach ($typeArray as $type => $array)
            {
                if (in_array($searchValue, $array))
                {
                    return $type;
                }
            }
            return null;
        }
    }
?>
