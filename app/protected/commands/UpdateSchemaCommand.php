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

    /**
     * UpdateSchemaCommand allows the schema to be updated.  This is useful if you are developing
     * and make changes to metadata that affects the database schema.
     */
    class UpdateSchemaCommand extends CConsoleCommand
    {
        public function getHelp()
        {
            return <<<EOD
    USAGE
      zurmoc updateSchema <username>

    DESCRIPTION
      This command runs an update on the database schema. It calls the
      RedBeanDatabaseBuilderUtil::autoBuildModels.

    PARAMETERS
     * username: username to log in as and run the import processes. Typically 'super'.
                  This user must be a super administrator.
EOD;
    }

    /**
     * Execute the action.
     * @param array command line parameters specific for this command
     */
    public function run($args)
    {
        set_time_limit('300');
        if (!isset($args[0]))
        {
            $this->usageError('A username must be specified.');
        }
        try
        {
            Yii::app()->user->userModel = User::getByUsername($args[0]);
        }
        catch (NotFoundException $e)
        {
            $this->usageError('The specified username does not exist.');
        }
        $group = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
        if(!$group->users->contains(Yii::app()->user->userModel))
        {
            $this->usageError('The specified user is not a super administrator.');
        }
        InstallUtil::runAutoBuildFromUpdateSchemaCommand();
    }
}
?>