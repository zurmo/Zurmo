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

    class ModuleTest extends BaseTest
    {
        public function testGetNestedModule()
        {
            $groupsModule = Yii::app()->getModule('zurmo')->getModule('groups');
            $this->assertEquals('GroupsModule', get_class($groupsModule));
            $zurmoModule   = Yii::app()->findModule('zurmo');
            $groupsModule = $zurmoModule->getModule('groups');
            $this->assertEquals('GroupsModule', get_class($groupsModule));
            $groupsModule = Yii::app()->findModule('groups');
            $this->assertEquals('GroupsModule', get_class($groupsModule));
        }

        /**
         * @depends testGetNestedModule
         */
        public function testGetModuleObjects()
        {
            $modules = Module::getModuleObjects();
            $this->assertTrue (count($modules) > 9);
            $this->assertTrue (array_key_exists('zurmo',          $modules));
            $this->assertTrue (array_key_exists('groups',        $modules));
            $this->assertTrue (array_key_exists('roles',         $modules));
            $this->assertTrue (array_key_exists('home',          $modules));
            $this->assertTrue (array_key_exists('configuration', $modules));
            $this->assertTrue (array_key_exists('accounts',      $modules));
            $this->assertTrue (array_key_exists('contacts',      $modules));
            $this->assertTrue (array_key_exists('leads',         $modules));
            $this->assertTrue (array_key_exists('opportunities', $modules));
            $this->assertTrue (array_key_exists('users',         $modules));
            $this->assertTrue ($modules['zurmo']           instanceof Module);
            $this->assertTrue ($modules['groups']         instanceof Module);
            $this->assertTrue ($modules['roles']          instanceof Module);
            $this->assertTrue ($modules['home']           instanceof Module);
            $this->assertTrue ($modules['worldClock']     instanceof Module);
            $this->assertTrue ($modules['accounts']       instanceof Module);
            $this->assertTrue ($modules['leads']          instanceof Module);
            $this->assertTrue ($modules['contacts']       instanceof Module);
            $this->assertTrue ($modules['opportunities']  instanceof Module);
            $this->assertFalse($modules['zurmo']         ->canDisable());
            $this->assertFalse($modules['groups']       ->canDisable());
            $this->assertFalse($modules['roles']        ->canDisable());
            $this->assertFalse($modules['users']        ->canDisable());
            $this->assertTrue ($modules['home']         ->canDisable());
            $this->assertTrue ($modules['accounts']     ->canDisable());
            $this->assertTrue ($modules['contacts']     ->canDisable());
            $this->assertTrue ($modules['leads']        ->canDisable());
            $this->assertTrue ($modules['opportunities']->canDisable());
            $this->assertTrue ($modules['worldClock']   ->canDisable());
        }

        /**
         * @depends testGetModuleObjects
         */
        public function testGetModuleNameAndDisplayName()
        {
            $modules = Module::getModuleObjects();
            foreach ($modules as $moduleName => $module)
            {
                $this->assertEquals($moduleName, $module::getDirectoryName());
                $this->assertEquals($moduleName, $module->getName());
            }
            $this->assertEquals('Zurmo',         $modules['zurmo']        ::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('Home',          $modules['home']         ::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('Accounts',      $modules['accounts']     ::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('Contacts',      $modules['contacts']     ::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('Leads',         $modules['leads']        ::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('Opportunities', $modules['opportunities']::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('World Clock',   $modules['worldClock']   ::getModuleLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testGetModuleObjects
         */
        public function testModuleDependencies()
        {
            $modules = Module::getModuleObjects();
            // TODO - test getting all dependencies
            // TODO - test getting enabled dependencies
            // TODO - test recursive enabling
            // TODO - test disabling, not recursive
            // TODO - test checking for satisfied dependencies all the way down
        }

        public function testGetDependenciesForModule()
        {
            $module = Yii::app()->findModule('accounts');
            $dependencies = Module::getDependenciesForModule($module);
            $this->assertEquals(
                array('zurmo', 'configuration', 'accounts'),
                $dependencies
            );
        }

        public function testGetModuleLabelByTypeAndLanguage()
        {
            $this->assertEquals('en', Yii::app()->languageHelper->getForCurrentUser());
            $this->assertEquals('Tes', TestModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Test', TestModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('tes', TestModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('test', TestModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));
            $metadata = TestModule::getMetadata();
            $metadata['global']['singularModuleLabels'] = array('en' => 'company', 'de' => 'gesellschaft');
            $metadata['global']['pluralModuleLabels']   = array('en' => 'companies', 'de' => 'gesellschaften');
            TestModule::setMetadata($metadata);
            $this->assertEquals('Company', TestModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Companies', TestModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('company', TestModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('companies', TestModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));
            Yii::app()->language = 'de';
            $this->assertEquals('Gesellschaft', TestModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Gesellschaften', TestModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('gesellschaft', TestModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('gesellschaften', TestModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));
            Yii::app()->language = 'en';
            //Demonstrate getSingularModuleLabel and getPluralModuleLabel and how if they are not overriden, they
            //will not necessarily produce desired results.
            $this->assertEquals('Zurm', ZurmoModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Zurmo', ZurmoModule::getModuleLabelByTypeAndLanguage('Plural'));
        }
    }
?>
