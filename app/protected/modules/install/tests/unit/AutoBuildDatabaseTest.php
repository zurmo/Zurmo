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
            if ($unfreezeWhenDone)
            {
                RedBeanDatabase::freeze();
            }

            $afterRowCount              = DatabaseCompatibilityUtil::getTableRowsCountTotal();
            //There are only 1 extra rows that are not being removed during the autobuild process.
            //These need to eventually be fixed so they are properly removed, except currency which is ok.
            //currency (1)
            $this->assertEquals($beforeRowCount, ($afterRowCount - 2));
        }

        /**
         * @depends testAutoBuildDatabase
         */
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
                        $tableName      = RedBeanModel::getTableName($model);
                        $columns = R::$writer->getColumns($tableName);
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
                                            $field          = strtolower($attributeName);
                                            $columnType = false;
                                            if (isset($columns[$field]))
                                            {
                                                $columnType         = $columns[$field];
                                            }
                                            $compareType    = null;
                                            $compareTypes = $this->getDatabaseTypesByType($type);
                                            // Remove brackets from database type
                                            $bracketPosition = stripos($columnType, '(');
                                            if ($bracketPosition !== false)
                                            {
                                                $columnType = substr($columnType, 0, $bracketPosition);
                                            }

                                            $databaseColumnType = strtoupper(trim($columnType));
                                            $compareTypeString  = implode(',', $compareTypes); // Not Coding Standard
                                            if (!in_array($databaseColumnType, $compareTypes))
                                            {
                                                $compareTypeString  = implode(',', $compareTypes); // Not Coding Standard
                                                $this->fail("Actual database type {$databaseColumnType} not in expected types: {$compareTypeString}.");
                                            }
                                        }
                                        break;
                                }
                            }
                        }
                    }
                }
            }
        }

        /**
         * @depends testAutoBuildDatabase
         */
        public function testAutoBuildUpgrade()
        {
            $unfreezeWhenDone     = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $unfreezeWhenDone = true;
            }
            // adding Text Field
            $metadata = Account::getMetadata();
            $metadata['Account']['members'][] = 'newField';
            $rules = array('newField', 'type', 'type' => 'string');
            $metadata['Account']['rules'][] = $rules;
            // adding Date Field
            $metadata['Account']['members'][] = 'dateField';
            $rules = array('dateField', 'type', 'type' => 'date');
            $metadata['Account']['rules'][] = $rules;
            // adding Boolean Field
            $metadata['Account']['members'][] = 'booleanField';
            $rules = array('booleanField', 'boolean');
            $metadata['Account']['rules'][] = $rules;
            // adding Integer Field
            $metadata['Account']['members'][] = 'integerField';
            $rules = array('integerField', 'type', 'type' => 'integer');
            $metadata['Account']['rules'][] = $rules;
            // adding DateTime Field
            $metadata['Account']['members'][] = 'dateTimeField';
            $rules = array('dateTimeField', 'type', 'type' => 'datetime');
            $metadata['Account']['rules'][] = $rules;
            // adding URL Field
            $metadata['Account']['members'][] = 'urlField';
            $rules = array('urlField', 'url');
            $metadata['Account']['rules'][] = $rules;
            // adding float Field
            $metadata['Account']['members'][] = 'floatField';
            $rules = array('floatField', 'type', 'type' => 'float');
            $metadata['Account']['rules'][] = $rules;
            // adding longText Field
            $metadata['Account']['members'][] = 'longTextField';
            $rules = array('longTextField', 'type', 'type' => 'longtext');
            $metadata['Account']['rules'][] = $rules;
            // adding Blob Field
            $metadata['Account']['members'][] = 'blobField';
            $rules = array('blobField', 'type', 'type' => 'blob');
            $metadata['Account']['rules'][] = $rules;
            // adding longBlob Field
            $metadata['Account']['members'][] = 'longBlobField';
            $rules = array('longBlobField', 'type', 'type' => 'longblob');
            $metadata['Account']['rules'][] = $rules;

            //print_r($accountMetadata);
            Account::setMetadata($metadata);

            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $messageLogger              = new MessageLogger();
            $beforeRowCount             = DatabaseCompatibilityUtil::getTableRowsCountTotal();
            InstallUtil::autoBuildDatabase($messageLogger);
            if ($unfreezeWhenDone)
            {
                RedBeanDatabase::freeze();
            }
            $afterRowCount              = DatabaseCompatibilityUtil::getTableRowsCountTotal();
            $this->assertEquals($beforeRowCount, $afterRowCount);

            //Check Account fields
            $tableName = RedBeanModel::getTableName('Account');
            $columns   = R::$writer->getColumns($tableName);
            $this->assertEquals('text',             $columns['newfield']);
            $this->assertEquals('date',             $columns['datefield']);
            $this->assertEquals('tinyint(1)',       $columns['booleanfield']);
            $this->assertEquals('int(11) unsigned', $columns['integerfield']);
            $this->assertEquals('datetime',         $columns['datetimefield']);
            $this->assertEquals('blob',             $columns['blobfield']);
            $this->assertEquals('longblob',         $columns['longblobfield']);
            $this->assertEquals('varchar(255)',     $columns['urlfield']);
            $this->assertEquals('double',           $columns['floatfield']);
        }

        /**
         * Based on type validator from models, get database column type
         * @param string $type
         * @return array
         */
        protected function getDatabaseTypesByType($type)
        {
            $typeArray = array(
                'string'    => array('VARCHAR', 'TEXT', 'LONGTEXT'),
                'text'      => array('TEXT'),
                'longtext'  => array('LONG_TEXT'),
                'integer'   => array('TINYINT', 'INT', 'INTEGER', 'BIGINT'),
                'float'     => array('FLOAT', 'DOUBLE'),
                'year'      => array('YEAR'),
                'date'      => array('DATE'),
                'datetime'  => array('DATETIME'),
                'blob'      => array('BLOB'),
                'longblob'  => array('LONG_BLOB'),
                'boolean'   => array('TINY_INT'),
            );

            $databaseTypes = array();
            if (isset($typeArray[$type]))
            {
                $databaseTypes = $typeArray[$type];
            }
            return $databaseTypes;
        }
    }
?>
