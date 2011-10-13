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

    /*
        These tests use classes in the tests/models directory which
        have this inheritance structure to test the RedBeanModel class.

        Key: <- = is-a, | = has-a

                               RedBeanModel <- F
                                       |
        RedBeanModel <- A <- B <- C <- D
                        ^         |
                        |         |
                        Y         |
                                  |
                  RedBeanModel <- E
                                  |
                  RedBeanModel <- G

        RedBeanModel <- Z

        How RedBeanModel works - the derived classes supply it with the meta data that
        it needs to correctly represent each class in its own table and link
        them to related classes, on demand loading the related classes.
    */

    class RedBeanModelTest extends BaseTest
    {
        public function testA()
        {
            $a = new A();
            $a->a = 1;
            $a->uniqueRequiredEmail = 'a@zurmoinc.com';
            $this->assertTrue($a->save());
            $id = $a->id;
            unset($a);
            $a = A::getById($id);
            $this->assertEquals(1,                $a->a);
            $this->assertEquals('a@zurmoinc.com', $a->uniqueRequiredEmail);
        }

        /**
         * @depends testA
         */
        public function testAAAttributeDoesntValidateJunkAsBoolean1()
        {
            $a = new A();
            $a->a = 'a';
            $a->uniqueRequiredEmail = 'whatever@zurmoinc.com';
            $this->assertFalse($a->validate());
            $this->assertEquals(
                array('a' =>
                    array(
                        'A must be either 1 or 0.'
                    )
                ),
                $a->getErrors()
            );
            $this->assertFalse($a->save());
        }

        /**
         * @depends testA
         */
        public function testAAAttributeSavesJunkAsBooleanIfWeExplicitlyDontValidate()
        {
            // Dumb thing to do, but yii allows saving without calling
            // validate so we're just testing that it is correctly doing
            // exactly what we're asking it to do. In this example the
            // column will automatically be changed to a string column
            // by RedBean.
            $a = new A();
            $a->a = 'a';
            $a->uniqueRequiredEmail = 'whatever@zurmoinc.com';
            $this->assertTrue($a->save(false));
            $a->delete();
        }

        /**
         * @depends testA
         */
        public function testGetAll()
        {
            $data = array(
                array(1, 'a1@zurmoinc.com'),
                array(0, 'a2@zurmoinc.com'),
                array(0, 'a3@zurmoinc.com'),
                array(1, 'a4@zurmoinc.com'),
            );
            foreach ($data as $aAndZ)
            {
                $a = new A();
                $a->a = $aAndZ[0];
                $a->uniqueRequiredEmail = $aAndZ[1];
                $a->validate();
                $this->assertTrue($a->save());
            }
            $allAs = A::getAll();
            $this->assertEquals(5, count($allAs));
        }

        /**
         * @depends testGetAll
         */
        public function testGetRange()
        {
            $allAs = A::getSubset(null, 0, 1);
            $this->assertEquals(1, count($allAs));
            $allAs = A::getSubset(null, 0, 2);
            $this->assertEquals(2, count($allAs));
            $allAs = A::getSubset(null, 2, 3);
            $this->assertEquals(3, count($allAs));
        }

        /**
         * @depends testGetRange
         */
        public function testGetAllWithSorting()
        {
            $allAs = A::getAll('a');
            $this->assertEquals(5, count($allAs));
            $this->assertEquals(0, $allAs[0]->a);
            $this->assertEquals(0, $allAs[1]->a);
            $this->assertEquals(1, $allAs[2]->a);
            $this->assertEquals(1, $allAs[3]->a);
            $this->assertEquals(1, $allAs[4]->a);

            $allAs = A::getAll('a', true);
            $this->assertEquals(5, count($allAs));
            $this->assertEquals(1, $allAs[0]->a);
            $this->assertEquals(1, $allAs[1]->a);
            $this->assertEquals(1, $allAs[2]->a);
            $this->assertEquals(0, $allAs[3]->a);
            $this->assertEquals(0, $allAs[4]->a);

            $allAs = A::getAll('uniqueRequiredEmail');
            $this->assertEquals(5, count($allAs));
            if (in_array(RedBeanDatabase::getDatabaseType(), array('sqlite', 'pgsql')))
            {
                $this->assertEquals(1,                 $allAs[0]->a);
                $this->assertEquals('a1@zurmoinc.com', $allAs[0]->uniqueRequiredEmail);
                $this->assertEquals(0,                 $allAs[1]->a);
                $this->assertEquals('a2@zurmoinc.com', $allAs[1]->uniqueRequiredEmail);
                $this->assertEquals(0,                 $allAs[2]->a);
                $this->assertEquals('a3@zurmoinc.com', $allAs[2]->uniqueRequiredEmail);
                $this->assertEquals(1,                 $allAs[3]->a);
                $this->assertEquals('a4@zurmoinc.com', $allAs[3]->uniqueRequiredEmail);
                $this->assertEquals(1,                 $allAs[4]->a);
                $this->assertEquals('a@zurmoinc.com',  $allAs[4]->uniqueRequiredEmail);
            }
            else
            {
                $this->assertEquals(1,                 $allAs[0]->a);
                $this->assertEquals('a@zurmoinc.com',  $allAs[0]->uniqueRequiredEmail);
                $this->assertEquals(1,                 $allAs[1]->a);
                $this->assertEquals('a1@zurmoinc.com', $allAs[1]->uniqueRequiredEmail);
                $this->assertEquals(0,                 $allAs[2]->a);
                $this->assertEquals('a2@zurmoinc.com', $allAs[2]->uniqueRequiredEmail);
                $this->assertEquals(0,                 $allAs[3]->a);
                $this->assertEquals('a3@zurmoinc.com', $allAs[3]->uniqueRequiredEmail);
                $this->assertEquals(1,                 $allAs[4]->a);
                $this->assertEquals('a4@zurmoinc.com', $allAs[4]->uniqueRequiredEmail);
            }

            $allAs = A::getAll('uniqueRequiredEmail', true);
            $this->assertEquals(5, count($allAs));
            if (in_array(RedBeanDatabase::getDatabaseType(), array('sqlite', 'pgsql')))
            {
                $this->assertEquals(1,                 $allAs[4]->a);
                $this->assertEquals('a1@zurmoinc.com', $allAs[4]->uniqueRequiredEmail);
                $this->assertEquals(0,                 $allAs[3]->a);
                $this->assertEquals('a2@zurmoinc.com', $allAs[3]->uniqueRequiredEmail);
                $this->assertEquals(0,                 $allAs[2]->a);
                $this->assertEquals('a3@zurmoinc.com', $allAs[2]->uniqueRequiredEmail);
                $this->assertEquals(1,                 $allAs[1]->a);
                $this->assertEquals('a4@zurmoinc.com', $allAs[1]->uniqueRequiredEmail);
                $this->assertEquals(1,                 $allAs[0]->a);
                $this->assertEquals('a@zurmoinc.com',  $allAs[0]->uniqueRequiredEmail);
            }
            else
            {
                $this->assertEquals(1,                 $allAs[4]->a);
                $this->assertEquals('a@zurmoinc.com',  $allAs[4]->uniqueRequiredEmail);
                $this->assertEquals(1,                 $allAs[3]->a);
                $this->assertEquals('a1@zurmoinc.com', $allAs[3]->uniqueRequiredEmail);
                $this->assertEquals(0,                 $allAs[2]->a);
                $this->assertEquals('a2@zurmoinc.com', $allAs[2]->uniqueRequiredEmail);
                $this->assertEquals(0,                 $allAs[1]->a);
                $this->assertEquals('a3@zurmoinc.com', $allAs[1]->uniqueRequiredEmail);
                $this->assertEquals(1,                 $allAs[0]->a);
                $this->assertEquals('a4@zurmoinc.com', $allAs[0]->uniqueRequiredEmail);
            }
        }

        /**
         * @depends testGetAllWithSorting
         */
        public function testGetRangeWithSorting()
        {
            $allAs = A::getSubset(null, 0, 2, null, 'uniqueRequiredEmail');
            $this->assertEquals(2, count($allAs));
            if (in_array(RedBeanDatabase::getDatabaseType(), array('sqlite', 'pgsql')))
            {
                $this->assertEquals(1,                 $allAs[0]->a);
                $this->assertEquals('a1@zurmoinc.com', $allAs[0]->uniqueRequiredEmail);
                $this->assertEquals(0,                 $allAs[1]->a);
                $this->assertEquals('a2@zurmoinc.com', $allAs[1]->uniqueRequiredEmail);
            }
            else
            {
                $this->assertEquals(1,                 $allAs[0]->a);
                $this->assertEquals('a@zurmoinc.com',  $allAs[0]->uniqueRequiredEmail);
                $this->assertEquals(1,                 $allAs[1]->a);
                $this->assertEquals('a1@zurmoinc.com', $allAs[1]->uniqueRequiredEmail);
            }
            $allAs = A::getSubset(null, 2, 3, null, 'uniqueRequiredEmail');
            $this->assertEquals(3, count($allAs));
            if (in_array(RedBeanDatabase::getDatabaseType(), array('sqlite', 'pgsql')))
            {
                $this->assertEquals(0,                 $allAs[0]->a);
                $this->assertEquals('a3@zurmoinc.com', $allAs[0]->uniqueRequiredEmail);
                $this->assertEquals(1,                 $allAs[1]->a);
                $this->assertEquals('a4@zurmoinc.com', $allAs[1]->uniqueRequiredEmail);
                $this->assertEquals(1,                 $allAs[2]->a);
                $this->assertEquals('a@zurmoinc.com',  $allAs[2]->uniqueRequiredEmail);
            }
            else
            {
                $this->assertEquals(0,                 $allAs[0]->a);
                $this->assertEquals('a2@zurmoinc.com', $allAs[0]->uniqueRequiredEmail);
                $this->assertEquals(0,                 $allAs[1]->a);
                $this->assertEquals('a3@zurmoinc.com', $allAs[1]->uniqueRequiredEmail);
                $this->assertEquals(1,                 $allAs[2]->a);
                $this->assertEquals('a4@zurmoinc.com', $allAs[2]->uniqueRequiredEmail);
            }

            $allAs = A::getSubset(null, 4, 1, null, 'uniqueRequiredEmail');
            $this->assertEquals(1, count($allAs));
            if (in_array(RedBeanDatabase::getDatabaseType(), array('sqlite', 'pgsql')))
            {
                $this->assertEquals(1,                $allAs[0]->a);
                $this->assertEquals('a@zurmoinc.com', $allAs[0]->uniqueRequiredEmail);
            }
            else
            {
                $this->assertEquals(1,                 $allAs[0]->a);
                $this->assertEquals('a4@zurmoinc.com', $allAs[0]->uniqueRequiredEmail);
            }

            $allAs = A::getSubset(null, 5, 1, null, 'uniqueRequiredEmail');
            $this->assertEquals(0, count($allAs));

            $allAs = A::getSubset(null, 500, 123, null, 'uniqueRequiredEmail');
            $this->assertEquals(0, count($allAs));
        }

        /**
         * @depends testA
         */
        public function testB()
        {
            $b = new B();
            $b->a = 0;
            $b->b = 'b';
            $b->uniqueRequiredEmail = 'b@zurmoinc.com';
            $this->assertTrue($b->validate());
            $this->assertTrue($b->save());
            $id = $b->id;
            unset($b);
            $b = B::getById($id);
            $this->assertEquals(0,                $b->a);
            $this->assertEquals('b@zurmoinc.com', $b->uniqueRequiredEmail);
        }

        /**
         * @depends testB
         */
        public function testC()
        {
            $e = new E();
            $e->e = 'theDefaultE';
            $this->assertTrue($e->save());

            $c = new C();
            $c->a = 0;
            $c->b = 'b';
            $c->c = 'c';
            $c->eRequired  = $e;
            $c->eUnique->e = strval(time());
            $this->assertNotNull($c->e);
            $this->assertNotNull($c->e->g);
            $c->e->e = 'e';
            $c->e->g->g = 'g';
            $this->assertTrue($c->save());
            $id = $c->id;
            unset($c);
            $c = C::getById($id);
            $this->assertEquals(0,   $c->a);
            $this->assertEquals('b', $c->b);
            $this->assertEquals('c', $c->c);
            $this->assertEquals('e', $c->e->e);
            $this->assertEquals('g', $c->e->g->g);
            $attributeNames = $c->attributeNames();
            $this->assertEquals(13, count($attributeNames));
            $this->assertTrue(in_array('a',                   $attributeNames));
            $this->assertTrue(in_array('b',                   $attributeNames));
            $this->assertTrue(in_array('c',                   $attributeNames));
            $this->assertTrue(in_array('e',                   $attributeNames));
            $this->assertTrue(in_array('defaultedInt',        $attributeNames));
            $this->assertTrue(in_array('junk',                $attributeNames));
            $this->assertTrue(in_array('uniqueRequiredEmail', $attributeNames));
        }

        /**
         * @depends testC
         */
        public function testCWithoutExplicitEAndG()
        {
            $c = new C();
            $c->a = 1;
            $c->b = 'b';
            $c->c = 'c';
            $this->assertTrue($c->save(false));
            $id = $c->id;
            unset($c);
            $c = C::getById($id);
            $this->assertNotNull($c->e);
            $this->assertNotNull($c->e->g);
            $c->e->e = 'e';
            $c->e->g->g = 'g';
            $this->assertTrue($c->save(false));
            unset($c);
            $c = C::getById($id);
            $this->assertEquals(1,   $c->a);
            $this->assertEquals('b', $c->b);
            $this->assertEquals('c', $c->c);
            $this->assertEquals('e', $c->e->e);
            $this->assertEquals('g', $c->e->g->g);
        }

        /**
         * @depends testCWithoutExplicitEAndG
         */
        public function testD()
        {
            $d = new D();
            $d->a = 1;
            $d->b = 'b';
            $d->c = 'c';
            $d->d = 'd';
            $d->eRequired->e = 'hello';
            $this->assertNotNull($d->f);
            $d->f->f = 'f';
            $this->assertTrue($d->save());
            $id = $d->id;
            unset($d);
            $d = D::getById($id);
            $this->assertEquals(1,   $d->a);
            $this->assertEquals('b', $d->b);
            $this->assertEquals('c', $d->c);
            $this->assertEquals('d', $d->d);
            $this->assertEquals('f', $d->f->f);
        }

        public function testGetAttributeLabel()
        {
            $a = new TestGetAttributeLabelModel();
            $this->assertEquals('This Is A Member Of Model', $a->getAttributeLabel('thisIsAMemberOfModel'));
            $this->assertEquals('And Another One',           $a->getAttributeLabel('andAnotherOne'));
            $this->assertEquals('Ooh A Boo Ba Doo',          $a->getAttributeLabel('oohABooBaDoo'));
            $this->assertEquals('Ooh A Boo Ba Doo D',        $a->getAttributeLabel('oohABooBaDooD'));
        }

        /**
         * @depends testA
         */
        public function testGetValidatorsAndIsAttributeRequired()
        {
            $a = new A();
            $a->a = 1;
            $this->assertTrue ($a->isAttributeRequired('a'));
            $this->assertFalse($a->isAttributeRequired('junk'));
            $this->assertFalse($a->isAttributeRequired('uniqueRequiredEmail'));
            $validators = $a->getValidators('a');
            $this->assertEquals(3, count($validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelRequiredValidator', $validators));
            $this->assertTrue(TestHelpers::isClassInArray('CBooleanValidator',             $validators));
            $validators = $a->getValidators('junk');
            $this->assertEquals(0, count($validators));
            $validators = $a->getValidators('uniqueRequiredEmail');
            $this->assertEquals(2, count($validators));
            $this->assertTrue(TestHelpers::isClassInArray('CEmailValidator',               $validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelUniqueValidator',   $validators));
            $validators = $a->getValidators();
            $this->assertEquals(6, count($validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelRequiredValidator',     $validators));
            $this->assertTrue(TestHelpers::isClassInArray('CBooleanValidator',                 $validators));
            $this->assertTrue(TestHelpers::isClassInArray('CEmailValidator',                   $validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelUniqueValidator',       $validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelTypeValidator',         $validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelDefaultValueValidator', $validators));
            $validators = $a->getValidatorList();
            $this->assertEquals(7, count($validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelRequiredValidator',     $validators));
            $this->assertTrue(TestHelpers::isClassInArray('CBooleanValidator',                 $validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelDefaultValueValidator', $validators));
            $this->assertTrue(TestHelpers::isClassInArray('CEmailValidator',                   $validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelUniqueValidator',       $validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelTypeValidator',         $validators));
        }

        /*
         * @depends testA
         */
        public function testGetValidatorsAndGetValidatorListWithScenarios()
        {
            $a = new A();
            $validators = $a->getValidators();
            $this->assertEquals(6, count($validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelRequiredValidator',     $validators));
            $this->assertTrue(TestHelpers::isClassInArray('CBooleanValidator',                 $validators));
            $this->assertTrue(TestHelpers::isClassInArray('CEmailValidator',                   $validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelUniqueValidator',       $validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelTypeValidator',         $validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelDefaultValueValidator', $validators));
            $validators = $a->getValidatorList();
            $this->assertEquals(7, count($validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelRequiredValidator',     $validators));
            $this->assertTrue(TestHelpers::isClassInArray('CBooleanValidator',                 $validators));
            $this->assertTrue(TestHelpers::isClassInArray('CEmailValidator',                   $validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelUniqueValidator',       $validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelTypeValidator',         $validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelDefaultValueValidator', $validators));

            $validators = $a->getValidators('a');
            $this->assertEquals(3, count($validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelRequiredValidator', $validators));
            $this->assertTrue(TestHelpers::isClassInArray('CBooleanValidator',             $validators));

            $validators = $a->getValidators('junk');
            $this->assertEquals(0, count($validators));

            $validators = $a->getValidators('uniqueRequiredEmail');
            $this->assertEquals(2, count($validators));
            $this->assertTrue(TestHelpers::isClassInArray('CEmailValidator',               $validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelUniqueValidator',   $validators));

            $a->setScenario('Tuesday');
            $this->assertEquals('Tuesday', $a->getScenario());
            $validators = $a->getValidators();
            $this->assertEquals(7, count($validators));
            $validators = $a->getValidatorList();
            $this->assertEquals(7, count($validators));

            $validators = $a->getValidators('a');
            $this->assertEquals(3, count($validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelRequiredValidator', $validators));
            $this->assertTrue(TestHelpers::isClassInArray('CBooleanValidator',             $validators));

            $validators = $a->getValidators('junk');
            $this->assertEquals(0, count($validators));

            $validators = $a->getValidators('uniqueRequiredEmail');
            $this->assertEquals(3, count($validators));
            $this->assertTrue(TestHelpers::isClassInArray('CEmailValidator',               $validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelRequiredValidator', $validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelUniqueValidator',   $validators));

            $a->setScenario('Monday');
            $this->assertEquals('Monday', $a->getScenario());
            $validators = $a->getValidators();
            $this->assertEquals(6, count($validators));
            $validators = $a->getValidatorList();
            $this->assertEquals(7, count($validators));

            $validators = $a->getValidators('a');
            $this->assertEquals(3, count($validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelRequiredValidator', $validators));
            $this->assertTrue(TestHelpers::isClassInArray('CBooleanValidator',             $validators));

            $validators = $a->getValidators('junk');
            $this->assertEquals(0, count($validators));

            $validators = $a->getValidators('uniqueRequiredEmail');
            $this->assertEquals(2, count($validators));
            $this->assertTrue(TestHelpers::isClassInArray('CEmailValidator',             $validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelUniqueValidator', $validators));

            $a->setScenario('');
            $this->assertEquals('', $a->getScenario());
            $validators = $a->getValidators();
            $this->assertEquals(6, count($validators));
            $validators = $a->getValidatorList();
            $this->assertEquals(7, count($validators));

            $validators = $a->getValidators('a');
            $this->assertEquals(3, count($validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelRequiredValidator', $validators));
            $this->assertTrue(TestHelpers::isClassInArray('CBooleanValidator',             $validators));

            $validators = $a->getValidators('junk');
            $this->assertEquals(0, count($validators));

            $validators = $a->getValidators('uniqueRequiredEmail');
            $this->assertEquals(2, count($validators));
            $this->assertTrue(TestHelpers::isClassInArray('CEmailValidator',             $validators));
            $this->assertTrue(TestHelpers::isClassInArray('RedBeanModelUniqueValidator', $validators));
        }

        /**
         * @depends testGetValidatorsAndIsAttributeRequired
         */
        public function testAddErrorAddErrorsHasErrorsAndGetErrors()
        {
            $a = new A();
            $a->a = 1;
            $this->assertFalse($a->hasErrors());
            $this->assertFalse($a->hasErrors('a'));

            $a->addError('a', 'A is jammed.');
            $this->assertTrue($a->hasErrors());
            $this->assertTrue($a->hasErrors('a'));
            $errors = $a->getErrors('a');
            $this->assertEquals(1, count($errors));
            $this->assertEquals('A is jammed.', $errors[0]);
            $errors = $a->getErrors();
            $this->assertEquals(1, count($errors));
            $this->assertTrue(isset($errors['a']));
            $this->assertEquals(1, count($errors['a']));
            $this->assertEquals('A is jammed.', $errors['a'][0]);

            $a->a = 1;
            $this->assertTrue($a->validate());
            $this->assertFalse($a->hasErrors());
            $this->assertFalse($a->hasErrors('a'));
            $errors = $a->getErrors('a');
            $this->assertEquals(0, count($errors));

            $a->addError('uniqueRequiredEmail', 'Unique Required Email is gash.');
            $this->assertTrue($a->hasErrors());
            $this->assertFalse($a->hasErrors('a'));
            $this->assertTrue($a->hasErrors('uniqueRequiredEmail'));
            $errors = $a->getErrors('a');
            $this->assertEquals(0, count($errors));
            $errors = $a->getErrors('uniqueRequiredEmail');
            $this->assertEquals(1, count($errors));
            $this->assertEquals('Unique Required Email is gash.', $errors[0]);
            $errors = $a->getErrors();
            $this->assertEquals(1, count($errors));
            $this->assertTrue(isset($errors['uniqueRequiredEmail']));
            $this->assertEquals(1, count($errors['uniqueRequiredEmail']));
            $this->assertEquals('Unique Required Email is gash.', $errors['uniqueRequiredEmail'][0]);

            $this->assertTrue($a->hasErrors());
            $this->assertFalse($a->hasErrors('a'));
            $this->assertTrue($a->hasErrors('uniqueRequiredEmail'));
            $a->clearErrors();
            $this->assertFalse($a->hasErrors());
            $this->assertFalse($a->hasErrors('a'));
            $this->assertFalse($a->hasErrors('uniqueRequiredEmail'));

            $a->uniqueRequiredEmail = 'jason@zurmoinc.com';
            $this->assertTrue($a->validate());
            $this->assertFalse($a->hasErrors());

            $this->assertTrue($a->save());
            $id = $a->id;
            unset($a);
            $a = A::getById($id);
            $this->assertEquals(1, $a->a);
            $this->assertEquals('jason@zurmoinc.com', $a->uniqueRequiredEmail);
        }

        /**
         * @depends testAddErrorAddErrorsHasErrorsAndGetErrors
         */
        public function testValidateAndGetErrors()
        {
            $a = new A();
            $this->assertFalse($a->hasErrors());

            $a->a = null;
            $this->assertFalse($a->validate());
            $this->assertTrue($a->hasErrors());
            $errors = $a->getErrors('a');
            $this->assertEquals(1, count($errors));
            $this->assertEquals('A cannot be blank.', $errors[0]);
            $errors = $a->getErrors();
            $this->assertEquals(1, count($errors));
            $this->assertEquals(1, count($errors['a']));
            $this->assertEquals('A cannot be blank.', $errors['a'][0]);

            $a->a = 'hello';
            $this->assertFalse($a->validate());
            $this->assertTrue($a->hasErrors());
            $errors = $a->getErrors('a');
            $this->assertEquals(1, count($errors));
            $this->assertEquals('A must be either 1 or 0.', $errors[0]);
            $errors = $a->getErrors();
            $this->assertEquals(1, count($errors));
            $this->assertEquals(1, count($errors['a']));
            $this->assertEquals('A must be either 1 or 0.', $errors['a'][0]);

            $a->a = 1;
            $this->assertTrue($a->validate());
            $this->assertFalse($a->hasErrors());
            $errors = $a->getErrors('a');
            $this->assertEquals(0, count($errors));
            $errors = $a->getErrors();
            $this->assertEquals(0, count($errors));

            $a->uniqueRequiredEmail = 'something';
            $this->assertFalse($a->validate());
            $this->assertTrue($a->hasErrors());
            $errors = $a->getErrors('a');
            $this->assertEquals(0, count($errors));
            $errors = $a->getErrors('uniqueRequiredEmail');
            $this->assertEquals(1, count($errors));
            $this->assertEquals('Unique Required Email is not a valid email address.', $errors[0]);
            $errors = $a->getErrors();
            $this->assertEquals(1, count($errors));
            $this->assertFalse(isset($errors['a']));
            $this->assertEquals(1, count($errors['uniqueRequiredEmail']));
            $this->assertEquals('Unique Required Email is not a valid email address.', $errors['uniqueRequiredEmail'][0]);

            $a->uniqueRequiredEmail = 'nev@zurmoinc.com';
            $this->assertTrue($a->validate());
            $this->assertFalse($a->hasErrors());
            $this->assertFalse($a->hasErrors('a'));
            $this->assertFalse($a->hasErrors('uniqueRequiredEmail'));
            $errors = $a->getErrors();
            $this->assertEquals(0, count($errors));
            $errors = $a->getErrors('a');
            $this->assertEquals(0, count($errors));
            $errors = $a->getErrors('uniqueRequiredEmail');
            $this->assertEquals(0, count($errors));
        }

        /**
         * @depends testC
         */
        public function testGetErrorsDrillingDownRelatedModels()
        {
            $c = new C();

            $c->a = null;
            $this->assertFalse ($c->validate());
            $this->assertTrue  ($c->hasErrors());
            $errors = $c->getErrors();
            $this->assertEquals(2, count($errors));
            $this->assertEquals(1, count($errors['a']));
            $this->assertEquals('A cannot be blank.', $errors['a'][0]);
            $this->assertEquals(1, count($errors['eRequired']));
            $this->assertEquals('E Required cannot be blank and must validate.', $errors['eRequired'][0]);

            $c->a = 1;
            $c->eRequired->e = 'hello';
            $this->assertTrue  ($c->validate());

            $c->a = 2;
            $c->eRequired = null;
            $this->assertFalse($c->validate());
            $this->assertTrue ($c->hasErrors());
            $errors = $c->getErrors();
            $this->assertEquals(2, count($errors));
            $this->assertEquals(1, count($errors['a']));
            $this->assertEquals('A must be either 1 or 0.', $errors['a'][0]);
            $this->assertEquals(1, count($errors['eRequired']));
            $this->assertEquals('E Required cannot be blank and must validate.', $errors['eRequired'][0]);
            $errors = $c->getErrors('a');
            $this->assertEquals(1, count($errors));
            $this->assertEquals(1, count($errors[0]));
            $this->assertEquals('A must be either 1 or 0.', $errors[0]);

            $c->a = 1;
            $c->eRequired = new E();
            $c->eRequired->e = 'hello';
            $this->assertTrue($c->validate());

            $c->e->e = 'thisistoolongbecauseehasalengthrule';
            $this->assertFalse ($c->validate());
            $this->assertTrue  ($c->hasErrors());
            $errors = $c->getErrors();
            $this->assertEquals(1, count($errors));
            $this->assertEquals(1, count($errors['e']));      // $c->e
            $this->assertEquals(1, count($errors['e']['e'])); // $c->e->e
            $this->assertEquals('E is too long (maximum is 16 characters).', $errors['e']['e'][0]);
            $errors = $c->getErrors('e');
            $this->assertEquals(1, count($errors));
            $this->assertEquals(1, count($errors['e']));      // $c->e
            $this->assertEquals('E is too long (maximum is 16 characters).', $errors['e'][0]);
            $errors = $c->getErrors(array('e', 'e'));         // $c->e->e
            $this->assertEquals(1, count($errors));
            $this->assertEquals('E is too long (maximum is 16 characters).', $errors[0]);
        }

        /**
         * @depends testGetErrorsDrillingDownRelatedModels
         */
        public function testGetErrorsDrillingDownRelatedOneToManyModels()
        {
            $c = new C();
            $c->a = 1;
            $c->eRequired->e = 'hello';
            $this->assertTrue($c->validate());

            $c->eMany->add(new E());
            $c->eMany->add(new E());
            $c->eMany->add(new E());
            $e4 = new E();
            $e5 = new E();
            $c->eMany->add($e4);
            $c->eMany->add($e5);
            $this->assertEquals(5, $c->eMany->count());
            $this->assertTrue($c->validate());

            $e4->e = 'this is too looooooong';
            $e5->e = 'this is too looooooong as well';
            $this->assertFalse($c->validate());
            $errors = $c->getErrors();
            $this->assertEquals(1, count($errors));
            $this->assertEquals(2, count($errors['eMany']));
            $this->assertEquals('E is too long (maximum is 16 characters).', $errors['eMany'][3]['e'][0]);
            $this->assertEquals('E is too long (maximum is 16 characters).', $errors['eMany'][4]['e'][0]);
        }

        public function testBulkSetAndGet()
        {
            $_FAKEPOST = array(
                'a'                   => 1,
                'junk'                => 'something',
                'uniqueRequiredEmail' => 'c1@zurmoinc.com',
                'eRequired'           => array('e' => 'hello'),
            );

            $c1 = new C();
            $c1->setAttributes($_FAKEPOST);
            $this->assertEquals(1,                 $c1->a);
            $this->assertEquals(null,              $c1->junk);
            $this->assertEquals('c1@zurmoinc.com', $c1->uniqueRequiredEmail);
            $this->assertTrue($c1->validate());
            $this->assertTrue($c1->save());
            $idC1 = $c1->id;
            unset($c1);

            $_FAKEPOST2 = array(
                'a'                   => 1,
                'junk'                => 'something',
                'uniqueRequiredEmail' => 'c2@zurmoinc.com',
                'eRequired'           => array('e' => 'hello'),
            );

            $c2 = new C();
            $c2->setAttributes($_FAKEPOST2, false);
            $this->assertEquals(1,                 $c2->a);
            $this->assertEquals('something',       $c2->junk);
            $this->assertEquals('c2@zurmoinc.com', $c2->uniqueRequiredEmail);
            $this->assertTrue($c2->validate());
            $this->assertTrue($c2->save());
            $idC2 = $c2->id;
            unset($c2);

            $_FAKEPOST = array(
                'a'                   => 'garbage',
                'junk'                => 'something',
                'uniqueRequiredEmail' => 'junk',
            );

            $c3 = new C();
            $c3->setAttributes($_FAKEPOST);
            $this->assertEquals('garbage',   $c3->a);
            $this->assertEquals(null,        $c3->junk);
            $this->assertEquals('junk',      $c3->uniqueRequiredEmail);
            $this->assertFalse($c3->validate());
            $this->assertFalse($c3->save());
            unset($c3);

            $c1 = C::getById($idC1);
            $this->assertEquals(1,                 $c1->a);
            $this->assertEquals(null,              $c1->junk);
            $this->assertEquals('c1@zurmoinc.com', $c1->uniqueRequiredEmail);
            $values = $c1->getAttributes();
            $this->assertEquals(1,                 $values['a']);
            $this->assertEquals(null,              $values['junk']);
            $this->assertEquals('c1@zurmoinc.com', $values['uniqueRequiredEmail']);

            $c2 = C::getById($idC2);
            $this->assertEquals(1,                 $c2->a);
            $this->assertEquals('something',       $c2->junk);
            $this->assertEquals('c2@zurmoinc.com', $c2->uniqueRequiredEmail);
            $values = $c2->getAttributes();
            $this->assertEquals(1,                 $values['a']);
            $this->assertEquals('something',       $values['junk']);
            $this->assertEquals('c2@zurmoinc.com', $values['uniqueRequiredEmail']);
        }

        /**
         * @depends testValidateAndGetErrors
         */
        public function testRedBeanModelUniqueValidator()
        {
            $a1 = new A();
            $a1->a = 1;
            $a1->uniqueRequiredEmail = 'ross@zurmoinc.com';
            $this->assertTrue($a1->save());
            // Make sure it doesn't fail because it is
            // the one that has the same uniqueRequiredEmail.
            $a1->a = 0;
            $this->assertTrue($a1->save());

            $a2 = new A();
            $a2->a = 1;
            $a2->uniqueRequiredEmail = 'webmaster@zurmoinc.com';
            $this->assertTrue($a2->validate());

            $a2->uniqueRequiredEmail = 'ross@zurmoinc.com';
            $this->assertFalse($a2->validate());
            $this->assertTrue($a2->hasErrors());
            $errors = $a2->getErrors('a');
            $this->assertEquals(0, count($errors));
            $errors = $a2->getErrors('uniqueRequiredEmail');
            $this->assertEquals(1, count($errors));
            $this->assertEquals('Unique Required Email "ross@zurmoinc.com" has already been taken.', $errors[0]);
            $errors = $a2->getErrors();
            $this->assertEquals(1, count($errors));
            $this->assertTrue(isset($errors['uniqueRequiredEmail']));
            $this->assertEquals(1, count($errors['uniqueRequiredEmail']));
            $this->assertEquals('Unique Required Email "ross@zurmoinc.com" has already been taken.', $errors['uniqueRequiredEmail'][0]);
        }

        /**
         * @depends testRedBeanModelUniqueValidator
         */
        public function testThatUniqueValidatorCatchesTheNotUniqueBeforeRedBeanDoes()
        {
            // Note: RedBean finding that the attribute value is not unique will still
            // have to be catered for because of concurrent uses of the system. Todo is
            // to figure out what Yii does about it.

            $b1 = new B();
            $b1->a = 1;
            $b1->uniqueRequiredEmail = 'sales@zurmoinc.com';
            $this->assertTrue($b1->save());

            $b2 = new B();
            $b2->a = 1;
            $b2->uniqueRequiredEmail = 'sales@zurmoinc.com';
            $this->assertFalse($b2->validate()); // Fails validation...

            try
            {
                $b2->save(false);                // ...saving it without validating.
                $this->fail('Expected a RedBean_Exception_SQL.');
            }
            catch (RedBean_Exception_SQL $e)
            {
                if (RedBeanDatabase::getDatabaseType() == 'sqlite')
                {
                    $this->assertEquals('SQLSTATE[23000]: Integrity constraint violation: 19 column uniquerequiredemail is not unique',
                                        $e->getMessage());
                }
                elseif (RedBeanDatabase::getDatabaseType() == 'pgsql')
                {
                    $this->assertEquals('SQLSTATE[23505]: Unique violation: 7 ERROR:  duplicate key value violates unique constraint "',
                                        substr($e->getMessage(), 0, 93));
                }
                else
                {
                    $this->assertEquals("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'sales@zurmoinc.com' for key",
                                        substr($e->getMessage(), 0, 98));
                }
            }
        }

        /**
         * @depends testValidateAndGetErrors
         */
        public function testRedBeanModelDefaultValueValidator()
        {
            $theDefaultE = E::getByE('theDefaultE');

            $c1 = new C();
            $c1->a = 1;
            $this->assertEquals(69,               $c1->defaultedInt);
            $this->assertEquals($theDefaultE->id, $c1->eDefaulted1->id);
            $this->assertEquals($theDefaultE->id, $c1->eDefaulted2->id);
            $c1->validate();
        }

        /**
         * @dependss testValidateAndGetErrors
         */
        public function testRedBeanModelNumberValidator()
        {
            $model = new TestPrecisionModel();

            // Make sure it's inheriting the base validator.
            $model->number = 2;
            $this->assertFalse($model->validate());
            $model->number = 6;
            $this->assertFalse($model->validate());
            $model->number = 4;
            $this->assertTrue($model->validate());

            $model->numberPositive5Precision = 1.234567;
            $model->validate();
            $this->assertEquals(
                array(
                    'numberPositive5Precision' => array(
                        'Number Positive 5 Precision is too precise (maximum decimal places is 5).'
                    ),
                ),
                $model->getErrors()
            );

            $model->numberPositive5Precision = 1.23456;
            $model->validate();
            $this->assertEquals(array(), $model->getErrors());

            $model->numberNegative3Precision = 1234567;
            $model->validate();
            $this->assertEquals(
                array(
                    'numberNegative3Precision' => array(
                        'Number Negative 3 Precision is too precise (maximum decimal places is -3).'
                    ),
                ),
                $model->getErrors()
            );

            $model->numberNegative3Precision = 123000;
            $model->validate();
            $this->assertEquals(array(), $model->getErrors());

            $model->numberZeroPrecision = 1234567.123;
            $model->validate();
            $this->assertEquals(
                array(
                    'numberZeroPrecision' => array(
                        'Number Zero Precision is too precise (maximum decimal places is 0).'
                    ),
                ),
                $model->getErrors()
            );

            $model->numberZeroPrecision = 1234567;
            $model->validate();
            $this->assertEquals(array(), $model->getErrors());

            $model->numberNullPrecision = 1.234567;
            $model->validate();
            $this->assertEquals(array(), $model->getErrors());

            $model->numberNullPrecision = 1.23456;
            $model->validate();
            $this->assertEquals(array(), $model->getErrors());

            $model->numberNullPrecision = 1234567;
            $model->validate();
            $this->assertEquals(array(), $model->getErrors());

            $model->numberNullPrecision = 123000;
            $model->validate();
            $this->assertEquals(array(), $model->getErrors());

            $model->numberNullPrecision = 1234567.123;
            $model->validate();
            $this->assertEquals(array(), $model->getErrors());
        }

        /**
         * @depends testA
         */
        public function testDefaultedPropertySavedAsIs()
        {
            $thing = new TestDefaultedAttributeModel();
            $this->assertEquals('no-reply@nowhere.com', $thing->email1);
            $this->assertEquals('no-reply@nowhere.com', $thing->email2);
            $thing->validate();
            $this->assertEquals('no-reply@nowhere.com', $thing->email2);
            $this->assertTrue($thing->save());
            $id = $thing->id;
            unset($thing);

            $thing = TestDefaultedAttributeModel::getById($id);
            $this->assertEquals('no-reply@nowhere.com', $thing->email1);
            $this->assertEquals('no-reply@nowhere.com', $thing->email2);

            //Now test when default setting is false.
            $thing = new TestDefaultedAttributeModel(false);
            $this->assertEquals('no-reply@nowhere.com', $thing->email1);
            $this->assertEquals(null, $thing->email2);
        }

        /**
         * @depends testDefaultedPropertySavedAsIs
         */
        public function testDefaultedPropertyModifiedAndSaved()
        {
            $thing = new TestDefaultedAttributeModel();
            $this->assertEquals('no-reply@nowhere.com', $thing->email1);
            $this->assertEquals('no-reply@nowhere.com', $thing->email2);

            $thing->email1 = 'a@zurmoinc.com';
            $thing->email2 = 'b@zurmoinc.com';
            $this->assertEquals('a@zurmoinc.com', $thing->email1);
            $this->assertEquals('b@zurmoinc.com', $thing->email2);

            $this->assertTrue($thing->save());
            $id = $thing->id;
            unset($thing);

            $thing = TestDefaultedAttributeModel::getById($id);
            $this->assertEquals('a@zurmoinc.com', $thing->email1);
            $this->assertEquals('b@zurmoinc.com', $thing->email2);
        }

        /**
         * @depends testDefaultedPropertyModifiedAndSaved
         */
        public function testDefaultedAndRemovedAndSaved()
        {
            //Test that if you had a default value then removed it, that the
            //value remains empty.
            $thing = new TestDefaultedAttributeModel();
            $this->assertEquals('no-reply@nowhere.com', $thing->email2);
            $thing->email2 = null;
            $this->assertEquals(null, $thing->email2);
            $this->assertTrue($thing->save());
            //It should have saved.
            $this->assertEquals(null, $thing->email2);
            $id = $thing->id;
            unset($thing);
            $thing = TestDefaultedAttributeModel::getById($id);
            $this->assertEquals(null, $thing->email2);
        }

        /**
         * @depends testA
         */
        public function testModelWithPropertyNamedLikeSqlKeyword()
        {
            $thing = new TestAttributeNamedLikeSqlKeywordModel();
            $thing->column = 123;
            $this->assertTrue($thing->save());
            $id = $thing->id;
            unset($thing);
            $thing = TestAttributeNamedLikeSqlKeywordModel::getById($id);
            $this->assertEquals(123, $thing->column);
        }

        /**
         * @depends testA
         */
        public function testZeros()
        {
            $thing = new TestSimplestModel();
            $thing->member = 0;
            $this->assertTrue($thing->save());
            $id = $thing->id;
            unset($thing);
            $thing = TestSimplestModel::getById($id);
            $this->assertEquals(0, $thing->member);
        }

        /**
         * @depends testZeros
         */
        public function testNull()
        {
            $thing = new TestSimplestModel();
            $thing->member = null;
            $this->assertTrue($thing->save());
            $id = $thing->id;
            unset($thing);
            $thing = TestSimplestModel::getById($id);
            $this->assertEquals(null, $thing->member);
        }

        /**
         * @depends testA
         */
        public function testBools()
        {
            $thing = new TestBooleanAttributeModel();
            $thing->bool = 1;
            $this->assertTrue($thing->save());
            $id = $thing->id;
            unset($thing);

            $thing = TestBooleanAttributeModel::getById($id);
            $this->assertEquals   (1,    $thing->bool);
            $this->assertNotEquals(true, $thing->bool);

            $thing->bool = 0;
            $this->assertTrue($thing->save());
            $id = $thing->id;
            unset($thing);

            $thing = TestBooleanAttributeModel::getById($id);
            $this->assertEquals   (0,     $thing->bool);
            $this->assertNotEquals(false, $thing->bool);

            $thing->bool = 3;
            $this->assertFalse($thing->save());
        }

        /**
         * @depends testC
         */
        public function testRequiredRelatedModel()
        {
            $c = new C();
            $c->a = 1;
            $c->eRequired->e = 'hello';
            $this->assertTrue($c->validate());
            $this->assertTrue($c->save());
            $c->eRequired = null;
            $this->assertFalse($c->validate());
            $this->assertFalse($c->save());
            // Make sure it doesn't fail because it is
            // the one that has the same related model.
            $c->a = 0;
            $this->assertFalse($c->save());
        }

        /**
         * @depends testC
         */
        public function notReady_testDefaultForRelatedModel()
        {
            $c = new C();
            $c->a = 1;
            $this->assertTrue($c->validate());

            $theDefaultE = E::getByE('theDefaultE');
            $this->assertEquals($c->e2->id, $theDefaultE->id);
            $this->assertEquals($c->e3->id, $theDefaultE->id);
        }

        /**
         * @depends testB
         */
        public function testParentModelsOnNewModelsDoNotGetRowsUntilTheyAreSaved()
        {
            $countBefore = intval(R::getCell("select count(*) from a;"));
            $account = new B();
            $countAfter  = intval(R::getCell("select count(*) from a;"));
            $this->assertEquals($countBefore, $countAfter);
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testIdIsReadOnly()
        {
            $a = new A();
            $a->id = 123;
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testReadOnlyAttribute()
        {
            $thing = new TestReadOnlyAttributeModel();
            $this->assertFalse($thing->isAttributeReadOnly('notReadOnly'));
            $this->assertTrue ($thing->isAttributeReadOnly('readOnly'));
            $thing->notReadOnly = 1;
            $this->assertEquals(69, $thing->readOnly);
            $this->assertTrue ($thing->validate());
            $this->readOnly = 70;
        }

        public function testIsSame()
        {
            $model1 = new TestIdentityModel();
            $model1->name = '1';
            $this->assertTrue($model1->save());
            $this->assertTrue($model1->isSame($model1));

            $model2 = new TestIdentityModel();
            $model2->name = '2';
            $this->assertTrue($model2->save());
            $this->assertTrue($model2->isSame($model2));

            $this->assertFalse($model1->isSame($model2));
            $this->assertFalse($model2->isSame($model1));

            $anotherInstanceOfModel1 = TestIdentityModel::getByName('1');
            $this->assertTrue ($anotherInstanceOfModel1->isSame($model1));
            $this->assertFalse($anotherInstanceOfModel1->isSame($model2));

            $a = new A();
            $a->a = 1;
            $a->uniqueRequiredEmail = 'identity@zurmoinc.com';
            $this->assertTrue($a->save());
            $this->assertTrue($a->isSame($a));

            $this->assertFalse($a     ->isSame($model1));
            $this->assertFalse($a     ->isSame($model2));
            $this->assertFalse($model1->isSame($a));
            $this->assertFalse($model2->isSame($a));
        }

        public function testSelfRelatingThings()
        {
            $model1 = new TestSelfRelatingModel();
            $model1->name = 'Wilfred';
            $this->assertTrue($model1->save());
            $this->assertEquals(0, $model1->bunch->count());

            $model2 = new TestSelfRelatingModel();
            $model2->name = 'Jemima';
            $this->assertTrue($model2->save());
            $this->assertEquals(0, $model2->bunch->count());

            $model3 = new TestSelfRelatingModel();
            $model3->name = 'Elouise';
            $this->assertTrue($model3->save());
            $this->assertEquals(0, $model3->bunch->count());

            $model1->bunch->add($model2);
            $this->assertTrue($model1->save());
            $this->assertEquals(1, $model1->bunch->count());
            $this->assertEquals(0, $model2->bunch->count());
            $this->assertEquals(0, $model3->bunch->count());

            $model1->bunch->add($model3);
            $this->assertTrue($model1->save());
            $this->assertEquals(2, $model1->bunch->count());
            $this->assertEquals(0, $model2->bunch->count());
            $this->assertEquals(0, $model3->bunch->count());

            unset($model1);
            unset($model2);
            unset($model3);

            $model1 = TestSelfRelatingModel::getByName('Wilfred');
            $model2 = TestSelfRelatingModel::getByName('Jemima');
            $model3 = TestSelfRelatingModel::getByName('Elouise');

            $this->assertEquals(2, $model1->bunch->count());
            $this->assertEquals(0, $model2->bunch->count());
            $this->assertEquals(0, $model3->bunch->count());
        }

        /**
         * @depends testC
         */
        public function testDownCast()
        {
            // Don't worry about this creation of the objects.
            // Skip...

            $b = new B();
            $b->name = 'the B model';
            $b->b = 'b';
            $this->assertTrue($b->validate());
            $this->assertTrue($b->save());

            $y = new Y();
            $y->name = 'the Y model';
            $y->y = 'y';
            $this->assertTrue($y->save());

            $c = new C();
            $c->name = 'the C model';
            $c->b = 'b';
            $c->c = 'c';
            $c->eUnique->e = strval(time());
            $c->e->e = 'e';
            $c->eRequired->e = 'hello';
            $c->e->g->g = 'g';
            $this->assertTrue($c->save());

            // ...down to here and just concern yourself
            // with the inheritance heirarchy.

            // As per the top of this file....
            // You will see that B and Y derive from A,
            // and C derives from B. E is required
            // because it is defaulted on C, which
            // is nothing to do with this test. Another
            // test uses it.

            $a1 = A::getByName('the B model');
            $a2 = A::getByName('the Y model');
            $a3 = A::getByName('the C model');

            // Here is where we see the problem with
            // the object relational mapping that
            // RedBeanModel supplies.
            // RedBeanModel knows that $a1 and
            // $a2 and $a3 are the same objects
            // as $b and $y and $c from its point
            // of view.
            $this->assertTrue($a1->isSame($b)); // :)
            $this->assertTrue($a2->isSame($y)); // :)
            $this->assertTrue($a3->isSame($c)); // :)

            // But they aren't from php's point
            // of view. We cannot treat $a1 as
            // a B, nor $a2 as a C, as we could
            // if they were purely OO objects in
            // memory that someone had given us
            // somehow.
            $this->assertTrue ($a1 instanceof A);
            $this->assertTrue ($a1 instanceof A);
            $this->assertFalse($a1 instanceof B); // :(
            $this->assertFalse($a1 instanceof Y); // :(
            $this->assertFalse($a3 instanceof C); // :(

            // So with the RedBeanModel object
            // relational model we have some
            // brokenness in our objects and their
            // polymorphism. To solve this in a
            // general and automatic way would
            // make something that is already too
            // slow more complicated, though
            // it would be nice (and OO correct)
            // from a usage point of view.

            // The solution/workaround is to give
            // us the ability to get $a1 and $a2
            // and $a3 as objects that are really
            // of the types of $b and $y and $c
            // when we know that that is possible.

            // Note that we look for C derived from B
            // first because if do B by itself it will
            // match and downcast to a B before it
            // finds C.
            $inheritancePossibilities = array(array('B', 'C'), 'B', 'Y');

            $this->assertTrue($a1->castDown($inheritancePossibilities) instanceof B); // :)
            $this->assertTrue($a2->castDown($inheritancePossibilities) instanceof Y); // :)
            $this->assertTrue($a3->castDown($inheritancePossibilities) instanceof C); // :)

            // Downcasting to what it already is doesn't
            // spaz out, and we could have just gone for
            // what we know they, if in fact we do know.
            // ie: we don't have to give it multiple
            // possibilities.
            $this->assertTrue($a1->castDown(array('A'))                instanceof A); // :)
            $this->assertTrue($a2->castDown(array('A'))                instanceof A); // :)
            $this->assertTrue($a3->castDown(array('A'))                instanceof A); // :)
            $this->assertTrue($a1->castDown(array('B'))                instanceof B); // :)
            $this->assertTrue($a2->castDown(array('Y'))                instanceof Y); // :)
            $this->assertTrue($a3->castDown(array(array('B', 'C')))    instanceof C); // :)

            // It's not pretty, but it should be required
            // fairly rarely, and if we can make it prettier
            // later there are plenty of regression tests.
        }

        /**
         * @depends testC
         */
        public function testDownCastToSameType()
        {
            $b = new B();
            $b->a = 0;
            $b->b = 'b';
            $this->assertTrue($b->validate());
            $this->assertTrue($b->save());

            // Will throw a NoFoundException, to be
            // taken as a bad cast exception.
            $b->castDown(array('B'));
        }

        /**
         * @depends testC
         * @expectedException NotFoundException
         */
        public function testDownCastToWrongType()
        {
            $b = new B();
            $b->a = 0;
            $b->b = 'b';
            $this->assertTrue($b->validate());
            $this->assertTrue($b->save());

            // Will throw a NoFoundException, to be
            // taken as a bad cast exception.
            $b->castDown(array('C', 'D'));
        }

        public function testSetAttributeWithEmptyValue()
        {
            $model = new TestNameModel();
            $model->name = 'abc';
            $this->assertTrue($model->save());
            $model = TestNameModel::getById($model->id);
            $this->assertEquals('abc', $model->name);
            $fakePostData = array(
                'name' => '',
            );
            $model->setAttributes($fakePostData);
            $this->assertEquals('', $model->name);
        }

        public function testDateTime()
        {
            $now = time();
            $model = new TestDateTimeModel();
            $model->myDate = '2011-06-07';
            $model->myDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $this->assertTrue($model->save());
            $id = $model->id;
            unset($model);
            $model = TestDateTimeModel::getById($id);
            $this->assertEquals('2011-06-07', $model->myDate);
            $this->assertEquals($now, DateTimeUtil::convertDbFormatDateTimeToTimestamp($model->myDateTime));

            $rows = R::getAll('desc testdatetimemodel');
            $this->assertEquals('mydate',     $rows[1]['Field']);
            $this->assertEquals('date',       $rows[1]['Type']);
            $this->assertEquals('mydatetime', $rows[2]['Field']);
            $this->assertEquals('datetime',   $rows[2]['Type']);
        }

        public function testMakeSubsetOrCountSqlQueryWithExtraSelectParts()
        {
            $quote               = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');
            $quotedExtraSelectColumnNameAndAliases = array('a' => 'aAlias', 'b' => 'bAlias');
            $subsetSql = I::makeSubsetOrCountSqlQuery('i', $joinTablesAdapter, 1, 5, null, null, false, false,
                                                                 $quotedExtraSelectColumnNameAndAliases);
            $compareSubsetSql  = "select {$quote}i{$quote}.{$quote}id{$quote} id, a aAlias, b bAlias ";
            $compareSubsetSql .= "from {$quote}i{$quote} ";
            $compareSubsetSql .= ' limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);
        }

        public function testBlobAttribute()
        {
            $binaryStuff = "test\x0\x1\xffstuff";
            $bigBinaryStuff = "test\x0\x1\xffbiggerstuff";
            $model = new TestBlobModel();
            $model->binaryStuff = $binaryStuff;
            $model->bigBinaryStuff = $bigBinaryStuff;
            $this->assertTrue($model->save());
            $id = $model->id;
            $model->forget();
            unset($model);

            $rows = R::getAll('desc testblobmodel');
            $this->assertEquals('binarystuff',    $rows[1]['Field']);
            $this->assertEquals('blob',           $rows[1]['Type']);
            $this->assertEquals('bigbinarystuff', $rows[2]['Field']);
            $this->assertEquals('longblob',       $rows[2]['Type']);

            $model = TestBlobModel::getById($id);
            $this->assertEquals($binaryStuff, $model->binaryStuff);
            $this->assertEquals($bigBinaryStuff, $model->bigBinaryStuff);
        }

        /**
         * Issue came up because attributeNamesNotBelongsToOrManyMany wasn't being cached and as a result
         * an existing model, if you tried to save it, it would not validate correctly and allow required attributes
         * to pass.
         */
        public function testModelCachesProperlyAndValidationWorksBasedOnAllNecessaryPropertiesCaching()
        {
            $m = new M();
            $m->m = 'aValue';
            $this->assertTrue($m->validate());
            $this->assertTrue($m->save());
            $mId = $m->id;
            $m->forget(); //need this here to demonstrate that the caching is working correctly

            $m = new M();
            $this->assertFalse($m->validate());
            $this->assertFalse($m->save());
            $m->forget(); //need this here to demonstrate that the caching is working correctly

            //Now edit the existing M and clear the required attribute m. It should fail validation.
            $m = M::getById($mId);
            unset($m);
            RedBeanModelsCache::forgetAllModelIdentifiersToModels();

            //Now reretrieve.
            $m = M::getById($mId);
            $m->m = null;
            $this->assertFalse($m->validate());
            $this->assertFalse($m->save());
            $m->m = 'aNewValue';
            $this->assertTrue($m->validate());
            $this->assertTrue($m->save());
        }

        public function testSwappingSameRelationModelsForSaving()
        {
            $j          = new J();
            $j->jMember = 'b';
            $this->assertTrue($j->save());
            $jId = $j->id;
            $j->forget();
            unset($j);

            $i          = new I();
            $i->iMember = 'a';
            $i->j       = J::getById($jId);
            $this->assertTrue($i->save());
            $iId = $i->id;
            $i->forget();
            unset($i);

            $jSame = J::getById($jId);
            $i     = I::getById($iId);
            $i->j  = $jSame;
            $this->assertTrue($i->save());
            $i->forget();
            unset($i);

            $i     = I::getById($iId);
            $this->assertEquals($i->j, $jSame);
            $this->assertEquals($i->j, J::getById($jId));
        }
    }
?>
