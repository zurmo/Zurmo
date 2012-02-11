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
     * Zurmo Module includes Roles and Groups
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class ZurmoSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent     ('zurmo/default/about');
            $this->runControllerWithNoExceptionsAndGetContent     ('zurmo/default/recentlyViewed');
            $this->runControllerWithNoExceptionsAndGetContent     ('zurmo/group');
            $this->runControllerWithNoExceptionsAndGetContent     ('zurmo/group/create');
            $this->runControllerWithNoExceptionsAndGetContent     ('zurmo/group/index');
            $this->runControllerWithNoExceptionsAndGetContent     ('zurmo/group/list');
            $this->runControllerWithNoExceptionsAndGetContent     ('zurmo/role');
            $this->runControllerWithNoExceptionsAndGetContent     ('zurmo/role/create');
            $this->runControllerWithNoExceptionsAndGetContent     ('zurmo/role/index');
            $this->runControllerWithNoExceptionsAndGetContent     ('zurmo/role/list');
            $this->runControllerWithRedirectExceptionAndGetContent('zurmo/default');
            $this->runControllerWithNoExceptionsAndGetContent     ('zurmo/default/about');
            //Since we have no error, the page will be empty.
            $this->runControllerWithNoExceptionsAndGetContent     ('zurmo/default/error', true);

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->setGetArray(array(
                'name' => 'something'));
            $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/unsupportedBrowser');

            //Logout user and make sure he is logged out.
            $this->runControllerWithRedirectExceptionAndGetContent('zurmo/default/logout');
            $this->assertTrue(Yii::app()->user->getIsGuest()); //this should evaluate true! // Not Coding Standard
            //Clear the user model so we can relogin and confirm the new user.
            Yii::app()->user->userModel = null;

            //Show login form.
            $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/login');
            //Validate Login form.
            $this->setPostArray(array(
                'ajax'  => 'login-form',
                'LoginForm' => array(   'username'   => 'super',
                                        'password'   => 'super',
                                        'YII_CSRF_TOKEN' => 'wangchung',
                                        'rememberme' => 0)
            ));
            $this->resetGetArray();
            $this->runControllerWithExitExceptionAndGetContent('zurmo/default/login');

            //Login and assert user is logged in ok.
            $this->resetGetArray();
            $this->setPostArray(array(
                'LoginForm' => array(   'username'   => 'super',
                                        'password'   => 'super',
                                        'rememberme' => 0)
            ));
            $this->runControllerWithRedirectExceptionAndGetContent('zurmo/default/login');
            $this->assertFalse(Yii::app()->user->isGuest);
            $this->assertTrue(Yii::app()->user->userModel->username == $super->username);

            //Relogin super user.
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test login form with populated extra header content.
            //First test that the extra content does not show up.
            $this->resetGetArray();
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/login');
            $this->assertTrue(strpos($content, 'xyzabc') === false);
            //Add content and test that it shows up properly.
            $content = '<div style="padding: 7px 7px 7px 80px; color: red;"><b>xyzabc</b></div>';
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'loginViewExtraHeaderContent', $content);
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/login');
            $this->assertTrue(strpos($content, 'xyzabc') !== false);

            //Configuration administration user interface.
            //First make sure settings are not what we are setting them too.
            $this->assertNotEquals('America/Barbados', Yii::app()->timeZoneHelper->getGlobalValue());
            $this->assertNotEquals(9, Yii::app()->pagination->getGlobalValueByType('listPageSize'));
            $this->assertNotEquals(4, Yii::app()->pagination->getGlobalValueByType('subListPageSize'));
            $this->assertNotEquals(7, Yii::app()->pagination->getGlobalValueByType('modalListPageSize'));
            $this->assertNotEquals(8, Yii::app()->pagination->getGlobalValueByType('dashboardListPageSize'));

            $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/configurationEdit');
            //Post fake save that will fail validation.
            $this->resetGetArray();
            $this->setPostArray(array('ZurmoConfigurationForm' =>
                array(  'timeZone' => 'America/Barbados',
                        'listPageSize' => 10,
                        'subListPageSize' => 0,
                        'modalListPageSize' => 8,
                        'dashboardListPageSize' => 8,
                        )));

            $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/configurationEdit');
            //Post fake save that will pass validation.
            $this->resetGetArray();
            $this->setPostArray(array('ZurmoConfigurationForm' =>
                array(  'timeZone' => 'America/Barbados',
                        'listPageSize' => 9,
                        'subListPageSize' => 4,
                        'modalListPageSize' => 7,
                        'dashboardListPageSize' => 7,
                        )));
            $this->runControllerWithRedirectExceptionAndGetContent('zurmo/default/configurationEdit');
            $this->assertEquals('Global configuration saved successfully.', Yii::app()->user->getFlash('notification'));

            //Check to make sure configuration is actually changed.
            $this->assertEquals('America/Barbados', Yii::app()->timeZoneHelper->getGlobalValue());
            $this->assertEquals(9, Yii::app()->pagination->getGlobalValueByType('listPageSize'));
            $this->assertEquals(4, Yii::app()->pagination->getGlobalValueByType('subListPageSize'));
            $this->assertEquals(7, Yii::app()->pagination->getGlobalValueByType('modalListPageSize'));
            $this->assertEquals(7, Yii::app()->pagination->getGlobalValueByType('dashboardListPageSize'));
        }

        public function testFileControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->assertEquals(0, count(FileModel::getAll()));
            $pathToFiles = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files');
            $filePath    = $pathToFiles . DIRECTORY_SEPARATOR . 'testNote.txt';
            $contents    = file_get_contents($pathToFiles . DIRECTORY_SEPARATOR . 'testNote.txt');

            //upload a file
            $filePath    = $pathToFiles . DIRECTORY_SEPARATOR . 'testNote.txt';
            self::resetAndPopulateFilesArrayByFilePathAndName('aTest', $filePath, 'testNote.txt');
            $this->resetPostArray();
            $this->SetGetArray(array('filesVariableName' => 'aTest'));
            $content = $this->runControllerWithExitExceptionAndGetContent('zurmo/fileModel/upload');
            //Confirm the file has actually been uploaded
            $files = FileModel::getAll();
            $compareJsonString = '[{"name":"testNote.txt","type":"text\/plain","size":"6.34KB","id":' . // Not Coding Standard
                                    $files[0]->id . '}]';
            $this->assertEquals($compareJsonString, $content);
            $fileModels = FileModel::getAll();
            $this->assertEquals(1, count($fileModels));
            $this->assertEquals($contents, $fileModels[0]->fileContent->content);
            if (!RedBeanDatabase::isFrozen())
            {
                //add fileModel to a model.
                $model = new ModelWithAttachmentTestItem();
                $model->member = 'test';
                $model->files->add($fileModels[0]);
                $this->assertTrue($model->save());
                $modelId = $model->id;
                $model->forget();

                //download a file.
                $this->setGetArray(array('id' => $fileModels[0]->id, 'modelId' => $modelId,
                                         'modelClassName' => 'ModelWithAttachmentTestItem'));
                $this->resetPostArray();
                $content = $this->runControllerWithExitExceptionAndGetContent('zurmo/fileModel/download');
                $compareContent = 'Testing download.';
                $this->assertEquals($compareContent, $content);
            }
            //todo: test all file errors.

            //Test deleting a file.
            $this->assertEquals(1, count(FileModel::getAll()));
            $this->assertEquals(1, count(FileContent::getAll()));
            $this->setGetArray(array('id' => $fileModels[0]->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('zurmo/fileModel/delete', true);

            //Now confirm that there are no file models or content in the system.
            $this->assertEquals(0, count(FileModel::getAll()));
            $this->assertEquals(0, count(FileContent::getAll()));

            //Test GlobalSearchAutoComplete
            $this->assertTrue(ContactsModule::loadStartingData());
            $this->setGetArray(array('term' => 'something'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/globalSearchAutoComplete');
            $this->assertEquals(CJSON::encode(array(array('href' => '', 'label' => 'No Results Found'))), $content);
        }
    }
?>