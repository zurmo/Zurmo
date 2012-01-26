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

    // HintManager copied from...
    // http://groups.google.com/group/redbeanorm/browse_thread/thread/7eb59797e8478a89/61eae7941cae1970
    // Used in testDateTimeHinting, and maybe other things, below. null test added.
    class HintManager implements RedBean_Observer {                                                 // Not Coding Standard
            public function __construct( $toolbox ) {                                               // Not Coding Standard
                    $this->dateOpt = new RedBean_Plugin_Optimizer_Datetime( $toolbox );             // Not Coding Standard
            }                                                                                       // Not Coding Standard
                                                                                                    // Not Coding Standard
            public function onEvent( $type, $info ) {                                               // Not Coding Standard
                    $hints = $info->getMeta("hint");                                                // Not Coding Standard
                    if ($hints !== null) {                                                          // Not Coding Standard
                            foreach ($hints as $k=>$v) {                                            // Not Coding Standard
                                    if ($v=="date"){ //or select an optimizer based on value in $v  // Not Coding Standard
                                            $this->dateOpt->setTable($info->getMeta("type"));       // Not Coding Standard
                                            $this->dateOpt->setColumn($k);                          // Not Coding Standard
                                            $this->dateOpt->setValue($info->$k);                    // Not Coding Standard
                                            $this->dateOpt->optimize();                             // Not Coding Standard
                                    }                                                               // Not Coding Standard
                            }                                                                       // Not Coding Standard
                    }
            }
    }

    // This is for testing details of how RedBean works.
    class RedBeanTest extends BaseTest
    {
        public function testZeros()
        {
            $thing = R::dispense('thing');
            $thing->zero = 0;
            R::store($thing);
            $id = $thing->id;
            unset($thing);
            $thing = R::load('thing', $id);
            $this->assertEquals(0, $thing->zero);

            //Try saving a second thing.
            $thing = R::dispense('thing');
            $thing->zero = 2;
            R::store($thing);
            $id = $thing->id;
            unset($thing);
            $thing = R::load('thing', $id);
            $this->assertEquals(2, $thing->zero);
        }

        public function testNulls()
        {
            $thing = R::dispense('thing');
            $thing->zero = null;
            R::store($thing);
            $id = $thing->id;
            unset($thing);
            $thing = R::load('thing', $id);
            $this->assertEquals(null, $thing->zero);
        }

        public function testGetAllTableReturnsNullOn5_2()
        {
            $sql = 'select id from atableneverhere';
            $rows = R::getAll($sql);
            if (version_compare(PHP_VERSION, '5.3.0', '>'))
            {
                $this->assertTrue(is_array($rows));
            }
            else
            {
                $this->assertTrue($rows === null);
            }
        }

        public function testStringContainingOnlyNumbers()
        {
            $thing = R::dispense('thing');
            $thing->phoneNumberNumber  = 5551234;
            $thing->phoneNumberString1 = '555-1234';
            $thing->phoneNumberString2 = '5551234';
            R::store($thing);
            $databaseType = R::$toolbox->getDatabaseAdapter()->getDatabase()->getDatabaseType();
            switch ($databaseType)
            {
                case 'mysql':
                    $sql = 'desc thing;';
                    $rows = R::getAll($sql);
                    $this->assertEquals('phoneNumberNumber',   $rows[2]['Field']);
                    $this->assertEquals('int(11) unsigned',    $rows[2]['Type']);
                    $this->assertEquals('phoneNumberString1',  $rows[3]['Field']);
                    $this->assertEquals('varchar(255)',        $rows[3]['Type']);
                    $this->assertEquals('phoneNumberString2',  $rows[4]['Field']);
                    $this->assertEquals('int(11) unsigned',    $rows[4]['Type']);
                    break;

                case 'sqlite':
                    $sql  = 'pragma table_info(\'thing\');';
                    $rows = R::getAll($sql);
                    $this->assertEquals('phoneNumberNumber',   $rows[2]['name']);
                    $this->assertEquals('INTEGER',             $rows[2]['type']);
                    $this->assertEquals('phoneNumberString1',  $rows[3]['name']);
                    $this->assertEquals('TEXT',                $rows[3]['type']);
                    $this->assertEquals('phoneNumberString2',  $rows[4]['name']);
                    $this->assertEquals('INTEGER',             $rows[4]['type']);
                    break;

                case 'pgsql':
                    $sql = 'select column_name, data_type from information_schema.columns where table_name = \'thing\' and column_name like \'phone%\' order by column_name;';
                    $rows = R::getAll($sql);
                    $this->assertEquals('phonenumbernumber',   $rows[0]['column_name']);
                    $this->assertEquals('integer',             $rows[0]['data_type']);
                    $this->assertEquals('phonenumberstring1',  $rows[1]['column_name']);
                    $this->assertEquals('text',                $rows[1]['data_type']);
                    $this->assertEquals('phonenumberstring2',  $rows[2]['column_name']);
                    $this->assertEquals('text',                $rows[2]['data_type']);
                    break;

                default:
                    $this->fail('Test does not support database type: ' . $databaseType);
            }
        }

        public function testRedBeanTypesShowingPDODodginess()
        {
            $wukka = R::dispense('wukka');
            $wukka->integer = 69;
            $wukka->string  = 'xxx';
            R::store($wukka);
            $this->assertEquals('integer', gettype($wukka->integer));
            $this->assertEquals('string',  gettype($wukka->string));
            $this->assertTrue  ($wukka->integer !== $wukka->string);
            $id = $wukka->id;
            unset($wukka);

            $databaseType = R::$toolbox->getDatabaseAdapter()->getDatabase()->getDatabaseType();
            switch ($databaseType)
            {
                case 'mysql':
                    $sql = 'desc wukka;';
                    $rows = R::getAll($sql);
                    $this->assertEquals('integer',             $rows[1]['Field']);
                    $this->assertEquals('tinyint(3) unsigned', $rows[1]['Type']);
                    $this->assertEquals('string',              $rows[2]['Field']);
                    $this->assertEquals('varchar(255)',        $rows[2]['Type']);
                    break;
            }

            $wukka = R::load('wukka', $id);
            $this->assertEquals('string', gettype($wukka->integer)); // Dodgy.
            $this->assertEquals('string', gettype($wukka->string));
            $this->assertTrue  ($wukka->integer !== $wukka->string);
        }

        public function testGetBeanWhenThereIsNoneToGet()
        {
            $bean = R::dispense('a');
            $bean2 = R::getBean($bean, 'b');
            if (!method_exists('R', 'getVersion') ||
                substr(R::getVersion(), 0, 3) == '1.2')
            {
                $this->assertTrue($bean2 !== null);
            }
            else
            {
                $this->assertEquals('1.3', substr(R::getVersion(), 0, 3));
                $this->assertTrue($bean2 === null);
            }
        }

        public function testUniqueMeta()
        {
            $bean = R::dispense('hombre');

            if (!method_exists('R', 'getVersion') ||
                substr(R::getVersion(), 0, 3) == '1.2')
            {
                $bean->setMeta("buildcommand.unique.0", array( "nombre") );
            }
            else
            {
                $this->assertEquals('1.3', substr(R::getVersion(), 0, 3));
                $bean->setMeta("buildcommand.unique", array(array("nombre")));
            }

            $bean->nombre = 'Pablo';
            R::store($bean);

            $bean2 = R::dispense('hombre');
            $bean2->nombre = 'Pablo';

            try
            {
                R::store($bean2);
                $this->fail('Expected a RedBean_Exception_SQL: Integrity constraint violation');
            }
            catch (RedBean_Exception_SQL $e)
            {
                $message = "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'Pablo'";
                $this->assertEquals($message, substr($e->getMessage(), 0, strlen($message)));
            }
        }

        public function testExampleStoredProcedure()
        {
            $wukka = R::dispense('wukka');
            $wukka->integer = 666;
            $wukka->string  = 'yyy';
            R::store($wukka);
            try
            {
                R::exec("drop procedure get_wukka_integer");
            }
            catch (Exception $e)
            {
            }
            R::exec("
                create procedure get_wukka_integer(in the_string varchar(255), out the_integer int(11))
                begin
                    select wukka.integer
                    into the_integer
                    from wukka
                    where wukka.string = the_string;
                end
            ");
            R::exec("call get_wukka_integer('yyy', @the_integer)");
            $this->assertEquals(666, R::getCell("select @the_integer"));
        }

        /**
         * @depends testExampleStoredProcedure
         */
        public function testExampleStoredFunction()
        {
            try
            {
                R::exec("drop function get_wukka_integer2");
            }
            catch (Exception $e)
            {
            }
            R::exec("
                create function get_wukka_integer2(the_string varchar(255))
                returns int(11)
                begin
                    declare the_integer int(11);
                    select wukka.integer
                    into the_integer
                    from wukka
                    where wukka.string = the_string;
                    return the_integer;
                end
            ");
            $this->assertEquals(666, R::getCell("select get_wukka_integer2('yyy')"));
        }

        public function testCascadedDeleteDoesNotWorkForLinkedBeans()
        {
            $person = R::dispense('person');
            $person->name = 'bill';
            R::store($person);

            $phone  = R::dispense('phone');
            $phone->number = '555-1234';
            R::store($phone);

            R::$linkManager->link($phone, $person);
            R::store($phone);

            // Either way this doesn't work.
//            RedBean_Plugin_Constraint::addConstraint($person, $phone);
            RedBean_Plugin_Constraint::addConstraint($phone, $person);

            $id = $phone->id;
            unset($phone);

            R::trash($person);
            unset($person);

            $phone = R::load('phone', $id);
            $this->assertNotNull($phone); // The phone is not deleted.
        }

        public function testDateTimeFields()
        {
            $toolbox = RedBean_Setup::kickstartDev(Yii::app()->db->connectionString,
                                                   Yii::app()->db->username,
                                                   Yii::app()->db->password);
            $optimizer = new RedBean_Plugin_Optimizer($toolbox);
            $optimizer->addOptimizer(new RedBean_Plugin_Optimizer_DateTime($toolbox));
            $redbean = $toolbox->getRedBean();
            $redbean->addEventListener('update', $optimizer);

            for ($i = 1; $i < 10; $i++)
            {
                $person = R::dispense("person");
                $person->name = "bill$i";
                $person->date1 = time();
                $person->date2 = date('Y-m-d H:i:s');
                $redbean->store($person);
            }
            // TODO: to be continued...
        }

        public function testDateTimeHinting()
        {
            $toolbox = RedBean_Setup::kickstartDev(Yii::app()->db->connectionString,
                                                   Yii::app()->db->username,
                                                   Yii::app()->db->password);

            // Copied directly from...
            // http://groups.google.com/group/redbeanorm/browse_thread/thread/7eb59797e8478a89/61eae7941cae1970
            $hint = new HintManager( R::$toolbox );                 // Not Coding Standard
            R::$redbean->addEventListener( "after_update", $hint ); // Not Coding Standard
            R::exec("drop table if exists bean");                   // Not Coding Standard
            $bean = R::dispense("bean");                            // Not Coding Standard
            $bean->setMeta("hint",array("prop"=>"date"));           // Not Coding Standard
            $bean->prop = "2010-01-01 10:00:00";                    // Not Coding Standard
            R::store($bean);                                        // Not Coding Standard

            $rows = R::getAll('desc bean');
            $this->assertEquals('prop',     $rows[1]['Field']);
            $this->assertEquals('datetime', $rows[1]['Type']);
        }
    }
?>
