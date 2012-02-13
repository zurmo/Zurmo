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
     * Helper functionality for use in RedBeanModels and derived models.
     * These functions cater for specific databases other than MySQL,
     * then by default return results for for MySQL.
     *
     * TODO: make it do what is described above. For now it just does
     * MySQL regardless of what database is in use.
     */
    class ZurmoDatabaseCompatibilityUtil
    {
        private static $storedFunctions = array(

            // Permitables - Rights

            'create function get_permitable_explicit_actual_right(
                                permitable_id int(11),
                                module_name   varchar(255),
                                right_name    varchar(255)
                             )
            returns tinyint
            DETERMINISTIC
            READS SQL DATA
            begin
                declare result tinyint;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        return 0;
                    end;

                select max(type)
                into   result
                from   _right
                where  _right.modulename    = module_name and
                       name                 = right_name and
                       _right.permitable_id = permitable_id;
                if result is null then
                    return 0;
                end if;
                return result;
            end;',

            // Permitables - Policies

            'create function get_permitable_explicit_actual_policy(
                                permitable_id int(11),
                                module_name   varchar(255),
                                policy_name   varchar(255)
                             )
            returns varchar(255) # A policy value can be anything RedBean will do whatever it it needs to to store it,
            DETERMINISTIC
            READS SQL DATA
            begin                # but since PDO returns it as a string I am too, until I know if that is a bad thing.
                declare result tinyint;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        return null;
                    end;

                select value
                into   result
                from   policy
                where  policy.modulename    = module_name and
                       name                 = policy_name and
                       policy.permitable_id = permitable_id
                limit  1;
                return result;
            end;',

            // Permitables - Other

            'create function permitable_contains_permitable(
                                permitable_id_1 int(11),
                                permitable_id_2 int(11)
                             )
            returns tinyint
            DETERMINISTIC
            READS SQL DATA
            begin
                declare result tinyint;
                declare user_id_1, user_id_2, group_id_1, group_id_2 int(11);

                # If they are both users just compare if they are the same user.
                select get_permitable_user_id(permitable_id_1)
                into   user_id_1;
                select get_permitable_user_id(permitable_id_2)
                into   user_id_2;
                if user_id_1 is not null and user_id_2 is not null then
                    set result = permitable_id_1 = permitable_id_2;
                else                                                            # Not Coding Standard
                    # If the first is a user and the second is a group return false.
                    select get_permitable_group_id(permitable_id_2)
                    into   group_id_2;
                    if user_id_1 is not null and group_id_2 is not null then
                        set result = 0;
                    else                                                        # Not Coding Standard
                        # Otherwise the first is a group, just return if it contains
                        # the second.
                        select get_permitable_group_id(permitable_id_1)
                        into   group_id_1;
                        if group_id_1 is not null then
                            select group_contains_permitable(group_id_1, permitable_id_2)
                            into result;
                        end if;
                    end if;
                end if;
                return result;
            end;',

            '# returns null if the permitable is not a user.
            create function get_permitable_user_id(
                                _permitable_id int(11)
                            )
            returns int(11)
            DETERMINISTIC
            READS SQL DATA
            begin
                declare result int(11);
                declare exit handler for 1146 # Table doesn\'t exist.
                    begin                     # RedBean hasn\'t created it yet.
                        return 0;
                    end;

                select id
                into   result
                from   _user
                where  _user.permitable_id = _permitable_id;
                return result;
            end;',

            '# returns null if the permitable is not a group.
            create function get_permitable_group_id(
                                _permitable_id int(11)
                            )
            returns int(11)
            begin
                declare result int(11);
                declare exit handler for 1146 # Table doesn\'t exist.
                    begin                     # RedBean hasn\'t created it yet.
                        return 0;
                    end;

                select id
                into   result
                from   _group
                where  _group.permitable_id = _permitable_id;
                return result;
            end;',

            // Users - Rights

            '# recursive_get_user_actual_right could just be called get_user_actual_right
             # and be called directly, but making the call from php and then doing
             # a second call to select @result is significantly slower than making
             # one call and having the extra level of indirection in MySQL.
            create function get_user_actual_right(
                                _user_id    int(11),
                                module_name varchar(255),
                                right_name  varchar(255)
                            )
            returns tinyint
            DETERMINISTIC
            READS SQL DATA
            begin
                declare result tinyint;
                declare is_super_administrator tinyint;

                select named_group_contains_user(\'Super Administrators\', _user_id)
                into   is_super_administrator;
                if is_super_administrator then
                    set result = 1;
                else                                                            # Not Coding Standard
                    call recursive_get_user_actual_right(_user_id, module_name, right_name, result);
                end if;
                return result;
            end;',

            'create function get_user_explicit_actual_right(
                                _user_id    int(11),
                                module_name varchar(255),
                                right_name  varchar(255)
                             )
            returns tinyint
            DETERMINISTIC
            READS SQL DATA
            begin
                declare result tinyint;
                declare _permitable_id tinyint;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        return 0;
                    end;

                select permitable_id
                into   _permitable_id
                from   _user
                where  id = _user_id;
                select get_permitable_explicit_actual_right(_permitable_id, module_name, right_name)
                into result;
                return result;
            end;',

            'create function get_user_inherited_actual_right(
                                _user_id    int(11),
                                module_name varchar(255),
                                right_name  varchar(255)
                             )
            returns tinyint
            DETERMINISTIC
            READS SQL DATA
            begin
                declare combined_right tinyint default 0;
                declare __group_id int(11);
                declare no_more_records tinyint default 0;
                declare _group_ids cursor for
                    select _group_id
                    from   _group__user
                    where  _group__user._user_id = _user_id;
                declare continue handler for not found
                    set no_more_records = 1;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        return 0;
                    end;

                open _group_ids;
                fetch _group_ids into __group_id;
                while no_more_records = 0 do
                    select combined_right |
                           get_group_explicit_actual_right (__group_id, module_name, right_name) |
                           get_group_inherited_actual_right(__group_id, module_name, right_name)
                    into combined_right;
                    fetch _group_ids into __group_id;
                end while;
                close _group_ids;

                select combined_right |
                       get_named_group_explicit_actual_right(\'Everyone\', module_name, right_name)
                into combined_right;

                if (combined_right & 2) = 2 then
                    return 2;
                end if;
                return combined_right;
            end;',

            // Users - Policies

            'create function get_user_explicit_actual_policy(
                                _user_id    int(11),
                                module_name varchar(255),
                                policy_name varchar(255)
                             )
            returns varchar(255) # A policy value can be anything RedBean will do whatever it it needs to to store it,
            DETERMINISTIC
            READS SQL DATA
            begin                # but since PDO returns it as a string I am too, until I know if that is a bad thing.
                declare result tinyint;
                declare _permitable_id tinyint;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        return 0;
                    end;

                select permitable_id
                into   _permitable_id
                from   _user
                where  id = _user_id;
                select get_permitable_explicit_actual_policy(_permitable_id, module_name, policy_name)
                into result;
                return result;
            end;',

            // Groups - Rights

            'create function get_group_actual_right(
                                _group_id   int(11),
                                module_name varchar(255),
                                right_name  varchar(255)
                             )
            returns tinyint
            DETERMINISTIC
            READS SQL DATA
            begin
                declare result tinyint;

                select get_group_explicit_actual_right (_group_id, module_name, right_name) |
                       get_group_inherited_actual_right(_group_id, module_name, right_name)
                into result;
                if (result & 2) = 2 then
                    return 2;
                end if;
                return result;
            end;',

            'create function get_group_explicit_actual_right(
                                _group_id   int(11),
                                module_name varchar(255),
                                right_name  varchar(255)
                             )
            returns tinyint
            DETERMINISTIC
            READS SQL DATA
            begin
                declare result tinyint;
                declare _permitable_id tinyint;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        return 0;
                    end;

                select permitable_id
                into   _permitable_id
                from   _group
                where  id = _group_id;
                select get_permitable_explicit_actual_right(_permitable_id, module_name, right_name)
                into result;
                return result;
            end;',

            'create function get_named_group_explicit_actual_right(
                                group_name  varchar(255),
                                module_name varchar(255),
                                right_name  varchar(255)
                             )
            returns tinyint
            DETERMINISTIC
            READS SQL DATA
            begin
                declare result tinyint;
                declare _permitable_id tinyint;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        return 0;
                    end;

                select permitable_id
                into   _permitable_id
                from   _group
                where  name = group_name;
                select get_permitable_explicit_actual_right(_permitable_id, module_name, right_name)
                into result;
                return result;
            end;',

            'create function get_group_inherited_actual_right(
                                _group_id   int(11),
                                module_name varchar(255),
                                right_name  varchar(255)
                             )
            returns tinyint
            DETERMINISTIC
            READS SQL DATA
            begin
                declare combined_right tinyint;

                call get_group_inherited_actual_right_ignoring_everyone(_group_id, module_name, right_name, combined_right);
                select combined_right |
                       get_named_group_explicit_actual_right(\'Everyone\', module_name, right_name)
                into combined_right;
                if (combined_right & 2) = 2 then
                    return 2;
                end if;
                return combined_right;
            end;',

            // Groups - Policies

            'create function get_group_explicit_actual_policy(
                                _group_id   int(11),
                                module_name varchar(255),
                                policy_name varchar(255)
                             )
            returns tinyint
            DETERMINISTIC
            READS SQL DATA
            begin
                declare result tinyint;
                declare _permitable_id tinyint;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        return 0;
                    end;

                select permitable_id
                into   _permitable_id
                from   _group
                where  id = _group_id;
                select get_permitable_explicit_actual_policy(_permitable_id, module_name, policy_name)
                into result;
                return result;
            end;',

            'create function get_named_group_explicit_actual_policy(
                                group_name  varchar(255),
                                module_name varchar(255),
                                policy_name varchar(255)
                             )
            returns varchar(255) # A policy value can be anything RedBean will do whatever it it needs to to store it,
            DETERMINISTIC
            READS SQL DATA
            begin                # but since PDO returns it as a string I am too, until I know if that is a bad thing.
                declare result tinyint;
                declare _permitable_id tinyint;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        return 0;
                    end;

                select permitable_id
                into   _permitable_id
                from   _group
                where  name = group_name;
                select get_permitable_explicit_actual_policy(_permitable_id, module_name, policy_name)
                into result;
                return result;
            end;',

            // Groups - Contains

            'create function named_group_contains_permitable(
                                group_name     varchar(255),
                                _permitable_id int(11)
                             )
            returns tinyint
            DETERMINISTIC
            READS SQL DATA
            begin
                declare result tinyint default 0;
                declare group_id_1 int(11);
                declare _user_id   int(11);
                declare group_id_2 int(11);
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        return 0;
                    end;

                if group_name = \'Everyone\' then
                    set result = 1;
                else                                                            # Not Coding Standard
                    select id
                    into   group_id_1
                    from   _group
                    where  _group.name = group_name;
                    set _user_id = get_permitable_user_id(_permitable_id);
                    if _user_id is not null then
                        call recursive_group_contains_user(group_id_1, _user_id, result);
                    else                                                        # Not Coding Standard
                        set group_id_2 = get_permitable_group_id(_permitable_id);
                        if group_id_2 is not null then
                            call recursive_group_contains_group(group_id_1, group_id_2, result);
                        end if;
                    end if;
                end if;
                return result;
            end;',

            'create function named_group_contains_user(
                                _group_name varchar(255),
                                _user_id    int(11)
                             )
            returns tinyint
            DETERMINISTIC
            READS SQL DATA
            begin
                declare result tinyint default 0;
                declare _group_id int(11);
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        return 0;
                    end;

                if _group_name = \'Everyone\' then
                    set result = 1;
                else                                                            # Not Coding Standard
                    select id
                    into   _group_id
                    from   _group
                    where  _group.name = _group_name;
                    call recursive_group_contains_user(_group_id, _user_id, result);
                end if;
                return result;
            end;',

            'create function group_contains_permitable(
                                _group_id      int(11),
                                _permitable_id int(11)
                             )
            returns tinyint
            DETERMINISTIC
            READS SQL DATA
            begin
                declare result tinyint default 0;
                declare _group_name varchar(255);
                declare is_everyone tinyint;
                declare _user_id int(11);
                declare group_id_2 int(11);
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        return 0;
                    end;

                select name
                into   _group_name
                from   _group
                where  _group.id = _group_id;
                if _group_name = \'Everyone\' then
                    set result = 1;
                else                                                            # Not Coding Standard
                    set _user_id = get_permitable_user_id(_permitable_id);
                    if _user_id is not null then
                        call recursive_group_contains_user(_group_id, _user_id, result);
                    else                                                        # Not Coding Standard
                        set group_id_2 = get_permitable_group_id(_permitable_id);
                        if group_id_2 is not null then
                            call recursive_group_contains_group(_group_id, group_id_2, result);
                        end if;
                    end if;
                end if;
                return result;
            end;',

            'create function group_contains_user(
                                _group_id int(11),
                                _user_id  int(11)
                             )
            returns tinyint
            DETERMINISTIC
            READS SQL DATA
            begin
                declare result tinyint default 0;

                call recursive_group_contains_user(_group_id, _user_id, result);
                return result;
            end;',

            // SecurableItems - Permissions

            'create function get_securableitem_actual_permissions_for_permitable(
                                _securableitem_id int(11),
                                _permitable_id    int(11),
                                class_name        varchar(255),
                                module_name       varchar(255),
                                caching_on        tinyint
                             )
            returns smallint
            DETERMINISTIC
            READS SQL DATA
            begin
                declare allow_permissions, deny_permissions smallint default 0;
                declare is_super_administrator, is_owner tinyint;

                select named_group_contains_permitable(\'Super Administrators\', _permitable_id)
                into is_super_administrator;
                if is_super_administrator then
                    set allow_permissions = 31;
                    set deny_permissions  = 0;
                else                                                            # Not Coding Standard
                    begin
                        declare continue handler for 1054, 1146 # Column, table doesn\'t exist.
                        begin                                   # RedBean hasn\'t created it yet.
                            set is_owner = 0;
                        end;
                        select _securableitem_id in
                            (select securableitem_id
                             from   _user, ownedsecurableitem
                             where  _user.id = ownedsecurableitem.owner__user_id and
                                    permitable_id = _permitable_id)
                        into is_owner;
                    end;
                    if is_owner then
                        set allow_permissions = 31;
                        set deny_permissions  = 0;
                    else                                                        # Not Coding Standard
                        if caching_on then
                            call get_securableitem_cached_actual_permissions_for_permitable(_securableitem_id, _permitable_id, allow_permissions, deny_permissions);
                            if allow_permissions is null then
                                call recursive_get_securableitem_actual_permissions_for_permitable(_securableitem_id, _permitable_id, class_name, module_name, allow_permissions, deny_permissions);
                                call cache_securableitem_actual_permissions_for_permitable(_securableitem_id, _permitable_id, allow_permissions, deny_permissions);
                            end if;
                        else                                                    # Not Coding Standard
                            call recursive_get_securableitem_actual_permissions_for_permitable(_securableitem_id, _permitable_id, class_name, module_name, allow_permissions, deny_permissions);
                        end if;
                    end if;
                end if;
                return (allow_permissions << 8) | deny_permissions;
            end;',

            'create function any_user_in_a_sub_role_has_read_permission(
                                securableitem_id int(11),
                                role_id          int(11),
                                class_name       varchar(255),
                                module_name      varchar(255)
                             )
            returns tinyint
            DETERMINISTIC
            READS SQL DATA
            begin
                declare has_read tinyint default 0;

                call any_user_in_a_sub_role_has_read_permission(securableitem_id, role_id, class_name, module_name, has_read);
                return has_read;
            end;',
        );

        // MySQL functions cannot be recursive so we have
        // to do recursive functions with procedures.

        private static $storedProcedures = array(

            // Users - Rights

            'create procedure recursive_get_user_actual_right(
                                in  _user_id    int(11),
                                in  module_name varchar(255),
                                in  right_name  varchar(255),
                                out result      tinyint
                              )
            begin
                declare _role_id tinyint;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        set result = 0;
                    end;

                set result = 0;
                begin
                    declare continue handler for 1054 # Column doesn\'t exist.
                    begin                             # RedBean hasn\'t created it yet.
                        set _role_id = null;
                    end;

                    select role_id
                    into   _role_id
                    from   _user
                    where  _user.id = _user_id;
                    if _role_id is not null then
                        call recursive_get_user_role_propagated_actual_allow_right(_role_id, module_name, right_name, result);
                        set result = result & 1;
                    end if;
                end;
                select get_user_explicit_actual_right (_user_id, module_name, right_name) |
                       get_user_inherited_actual_right(_user_id, module_name, right_name) |
                       result
                into result;

                if (result & 2) = 2 then
                    set result = 2;
                end if;
            end;',

            'create procedure recursive_get_user_role_propagated_actual_allow_right(
                                in  _role_id    int(11),
                                in  module_name varchar(255),
                                in  right_name  varchar(255),
                                out result      tinyint
                              )
            begin
                declare sub_role_id int(11);
                declare no_more_records tinyint default 0;
                declare sub_role_ids cursor for
                    select id
                    from   role
                    where  role.role_id = _role_id;
                declare continue handler for not found
                    set no_more_records = 1;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        set result = 0;
                    end;

                set result = 0;
                open sub_role_ids;
                fetch sub_role_ids into sub_role_id;
                while result = 0 and no_more_records = 0 do
                  begin
                      declare _user_id int(11);
                      declare _user_ids cursor for
                          select id
                          from   _user
                          where  _user.role_id = sub_role_id;

                      open _user_ids;
                      fetch _user_ids into _user_id;
                      while result = 0 and no_more_records = 0 do
                          call recursive_get_user_actual_right(_user_id, module_name, right_name, result);
                          fetch _user_ids into _user_id;
                      end while;
                      close _user_ids;
                      if result = 0 then
                          call recursive_get_user_role_propagated_actual_allow_right(sub_role_id, module_name, right_name, result);
                      end if;
                      set no_more_records = 0;
                      fetch sub_role_ids into sub_role_id;
                  end;
                end while;
                close sub_role_ids;
            end;',

            // Groups - Rights

            'create procedure get_group_inherited_actual_right_ignoring_everyone(
                                in  _group_id   int(11),
                                in  module_name varchar(255),
                                in  right_name  varchar(255),
                                out result      tinyint
                              )
            begin
                declare parent_group_id int(11);
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        set result = 0;
                    end;

                set result = 0;
                select _group._group_id
                into   parent_group_id
                from   _group
                where  id = _group_id;
                if parent_group_id is not null then
                    call get_group_inherited_actual_right_ignoring_everyone(parent_group_id, module_name, right_name, result);
                    select result |
                           get_group_explicit_actual_right(parent_group_id, module_name, right_name)
                    into result;
                    if (result & 2) = 2 then
                        set result = 2;
                    end if;
                end if;
            end;',

            // Groups - Other

            'create procedure recursive_group_contains_user(
                                in  _group_id int(11),
                                in  _user_id  int(11),
                                out result    tinyint
                              )
            begin
                declare child_group_id, count tinyint;
                declare no_more_records tinyint default 0;
                declare child_group_ids cursor for
                    select id
                    from   _group
                    where  _group._group_id = _group_id;
                declare continue handler for not found
                    set no_more_records = 1;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        set result = 0;
                    end;

                set result = 0;
                select count(*)
                into count
                from _group__user
                where _group__user._group_id = _group_id and
                      _group__user._user_id  = _user_id;

                if count > 0 then
                    set result = 1;
                else                                                            # Not Coding Standard
                    open child_group_ids;
                    fetch child_group_ids into child_group_id;
                    while result = 0 and no_more_records = 0 do
                        call recursive_group_contains_user(child_group_id, _user_id, result);
                        fetch child_group_ids into child_group_id;
                    end while;
                    close child_group_ids;
                end if;
            end;',

            'create procedure recursive_group_contains_group(
                                in  group_id_1 int(11),
                                in  group_id_2 int(11),
                                out result     tinyint
                              )
            begin
                declare group_2_parent_group_id, child_group_id int(11);
                declare no_more_records tinyint default 0;
                declare child_group_ids cursor for
                    select id
                    from   _group
                    where  _group._group_id = group_id_1;
                declare continue handler for not found
                    set no_more_records = 1;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        set result = 0;
                    end;

                set result = 0;
                if group_id_1 = group_id_2 then
                    set result = 1;
                else                                                            # Not Coding Standard
                    select _group_id
                    into   group_2_parent_group_id
                    from   _group
                    where  id = group_id_2;
                    if group_id_1 = group_2_parent_group_id then
                        set result = 1;
                    else                                                        # Not Coding Standard
                        open child_group_ids;
                        fetch child_group_ids into child_group_id;
                        while result = 0 and no_more_records = 0 do
                            call recursive_group_contains_user(child_group_id, group_id_2, result);
                            fetch child_group_ids into child_group_id;
                        end while;
                        close child_group_ids;
                    end if;
                end if;
            end;',

            // SecurableItems - Permissions

            'create procedure recursive_get_securableitem_actual_permissions_for_permitable(
                                in  _securableitem_id int(11),
                                in  _permitable_id    int(11),
                                in  class_name        varchar(255),
                                in  module_name       varchar(255),
                                out allow_permissions tinyint,
                                out deny_permissions  tinyint
                              )
            begin
                declare propagated_allow_permissions                            tinyint default 0;
                declare nameditem_allow_permissions, nameditem_deny_permissions tinyint default 0;
                declare is_owner tinyint;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        set allow_permissions = 0;
                        set deny_permissions  = 0;
                    end;
                begin
                    declare continue handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                                   # RedBean hasn\'t created it yet.
                        set is_owner = 0;
                    end;
                    select _securableitem_id in
                        (select securableitem_id
                         from   _user, ownedsecurableitem
                         where  _user.id = ownedsecurableitem.owner__user_id and
                                permitable_id = _permitable_id)
                    into is_owner;
                end;
                if is_owner then
                    set allow_permissions = 31;
                    set deny_permissions  = 0;
                else                                                            # Not Coding Standard
                    set allow_permissions = 0;
                    set deny_permissions  = 0;
                    call get_securableitem_explicit_inherited_permissions_for_permitable(_securableitem_id, _permitable_id, allow_permissions, deny_permissions);
                    call get_securableitem_propagated_allow_permissions_for_permitable  (_securableitem_id, _permitable_id, class_name, module_name, propagated_allow_permissions);
                    call get_securableitem_module_and_model_permissions_for_permitable  (_securableitem_id, _permitable_id, class_name, module_name, nameditem_allow_permissions, nameditem_deny_permissions);
                    set allow_permissions = allow_permissions | propagated_allow_permissions | nameditem_allow_permissions;
                    set deny_permissions  = deny_permissions                                 | nameditem_deny_permissions;
                end if;
            end;',

            'create procedure get_securableitem_explicit_actual_permissions_for_permitable(
                                in  _securableitem_id int(11),
                                in  _permitable_id    int(11),
                                out allow_permissions tinyint,
                                out deny_permissions  tinyint
                              )
            begin
                select bit_or(permissions)
                into   allow_permissions
                from   permission
                where  type = 1                          and
                       permitable_id    = _permitable_id and
                       securableitem_id = _securableitem_id;

                select bit_or(permissions)
                into   deny_permissions
                from   permission
                where  type = 2                       and
                       permitable_id = _permitable_id and
                securableitem_id = _securableitem_id;
            end;',

            'create procedure get_securableitem_explicit_inherited_permissions_for_permitable(
                                in  _securableitem_id int(11),
                                in  _permitable_id    int(11),
                                out allow_permissions tinyint,
                                out deny_permissions  tinyint
                              )
            begin
                declare permissions_permitable_id int(11);
                declare _type, _permissions, permission_applies tinyint;
                declare no_more_records tinyint default 0;
                declare permitable_id_type_and_permissions cursor for
                    select permitable_id, type, bit_or(permissions)
                    from   permission
                    where  securableitem_id = _securableitem_id
                    group  by permitable_id, type;
                declare continue handler for not found
                    set no_more_records = 1;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        set allow_permissions = 0;
                        set deny_permissions  = 0;
                    end;

                set allow_permissions = 0;
                set deny_permissions  = 0;
                open permitable_id_type_and_permissions;
                fetch permitable_id_type_and_permissions into
                            permissions_permitable_id, _type, _permissions;
                # The query will return at most one row with the allow bits and
                # one with the deny bits, so this loop will loop 0, 1, or 2 times.
                while no_more_records = 0 do
                    select permitable_contains_permitable(permissions_permitable_id, _permitable_id)
                    into   permission_applies;
                    if permission_applies then
                        if _type = 1 then
                            set allow_permissions = allow_permissions | _permissions;
                        else                                                    # Not Coding Standard
                            set deny_permissions  = deny_permissions  | _permissions;
                        end if;
                    end if;
                    fetch permitable_id_type_and_permissions into
                                permissions_permitable_id, _type, _permissions;
                end while;
                close permitable_id_type_and_permissions;
            end;',

            'create procedure get_securableitem_propagated_allow_permissions_for_permitable(
                                in  _securableitem_id int(11),
                                in  _permitable_id    int(11),
                                in  class_name        varchar(255),
                                in  module_name       varchar(255),
                                out allow_permissions tinyint
                              )
            begin
                declare user_id int(11);
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        set allow_permissions = 0;
                    end;

                set allow_permissions = 0;
                select get_permitable_user_id(_permitable_id)
                into   user_id;
                if user_id is not null then
                    call recursive_get_securableitem_propagated_allow_permissions_permit(_securableitem_id, _permitable_id, class_name, module_name, allow_permissions);
                end if;
            end;',

            // Name abbreviated - max is 64. Should end '_for permitable'.
            'create procedure recursive_get_securableitem_propagated_allow_permissions_permit(
                                in  _securableitem_id int(11),
                                in  _permitable_id    int(11),
                                in  class_name        varchar(255),
                                in  module_name       varchar(255),
                                out allow_permissions tinyint
                              )
            begin
                declare user_allow_permissions, user_deny_permissions, user_propagated_allow_permissions tinyint;
                declare user_role_id int(11);
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        set allow_permissions = 0;
                    end;

                set allow_permissions = 0;
                select role_id
                into   user_role_id
                from   _user
                where  permitable_id = _permitable_id;

                begin
                    declare sub_role_id int(11);
                    declare no_more_records tinyint default 0;
                    declare sub_role_ids cursor for
                        select id
                        from   role
                        where  role_id = user_role_id;
                    declare continue handler for not found
                        begin
                            set no_more_records = 1;
                        end;

                    open sub_role_ids;
                    fetch sub_role_ids into sub_role_id;
                    while no_more_records = 0 do
                        begin
                            declare propagated_allow_permissions tinyint;
                            declare user_in_role_id, permitable_in_role_id int(11);
                            declare permitable_in_role_ids cursor for
                                select permitable_id
                                from   _user
                                where  role_id = sub_role_id;

                            open permitable_in_role_ids;
                            fetch permitable_in_role_ids into permitable_in_role_id;
                            while no_more_records = 0 do
                                call recursive_get_securableitem_actual_permissions_for_permitable  (_securableitem_id, permitable_in_role_id, class_name, module_name, user_allow_permissions, user_deny_permissions);
                                call recursive_get_securableitem_propagated_allow_permissions_permit(_securableitem_id, permitable_in_role_id, class_name, module_name, propagated_allow_permissions);
                                set allow_permissions =
                                        allow_permissions                                 |
                                        (user_allow_permissions & ~user_deny_permissions) |
                                        propagated_allow_permissions;
                                fetch permitable_in_role_ids into permitable_in_role_id;
                            end while;
                        end;
                        set no_more_records = 0;
                        fetch sub_role_ids into sub_role_id;
                    end while;
                    close sub_role_ids;
                end;
            end;',

            'create procedure get_securableitem_module_and_model_permissions_for_permitable(
                                in  _securableitem_id int(11),
                                in  _permitable_id    int(11),
                                in  class_name        varchar(255),
                                in  module_name       varchar(255),
                                out allow_permissions tinyint,
                                out deny_permissions  tinyint
                               )
            begin
                declare permissions_permitable_id int(11);
                declare _type, _permissions, permission_applies tinyint;
                declare no_more_records                         tinyint default 0;
                declare permitable_id_type_and_permissions_for_namedsecurableitem cursor for
                    select permitable_id, type, bit_or(permissions)
                    from   permission, namedsecurableitem
                    where  permission.securableitem_id = namedsecurableitem.securableitem_id and
                           (name = class_name or name = module_name)
                           group by permitable_id, type;
                declare continue handler for not found
                    set no_more_records = 1;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                        set allow_permissions = 0;
                        set deny_permissions  = 0;
                    end;

                set allow_permissions = 0;
                set deny_permissions  = 0;
                open permitable_id_type_and_permissions_for_namedsecurableitem;
                fetch permitable_id_type_and_permissions_for_namedsecurableitem into
                            permissions_permitable_id, _type, _permissions;
                # The query will return at most one row with the allow bits and
                # one with the deny bits, so this loop will loop 0, 1, or 2 times.
                while no_more_records = 0 do
                    select permitable_contains_permitable(permissions_permitable_id, _permitable_id)
                    into   permission_applies;
                    if permission_applies then
                        if _type = 1 then
                            set allow_permissions = allow_permissions | _permissions;
                        else                                                    # Not Coding Standard
                            set deny_permissions  = deny_permissions  | _permissions;
                        end if;
                    end if;
                    fetch permitable_id_type_and_permissions_for_namedsecurableitem into
                                permissions_permitable_id, _type, _permissions;
                end while;
                close permitable_id_type_and_permissions_for_namedsecurableitem;
            end;',

            // Permissions - Caching
            'create procedure get_securableitem_cached_actual_permissions_for_permitable(
                                in  _securableitem_id  int(11),
                                in  _permitable_id     int(11),
                                out _allow_permissions tinyint,
                                out _deny_permissions  tinyint
                             )
            begin
                declare exit handler for 1146 # Table doesn\'t exist
                    begin                     # so nothing is cached.
                        set _allow_permissions = null;
                        set _deny_permissions  = null;
                    end;
                select allow_permissions, deny_permissions
                into   _allow_permissions, _deny_permissions
                from   actual_permissions_cache
                where  securableitem_id = _securableitem_id and
                       permitable_id    = _permitable_id;
            end;',

            'create procedure cache_securableitem_actual_permissions_for_permitable(
                                in _securableitem_id  int(11),
                                in _permitable_id     int(11),
                                in _allow_permissions tinyint,
                                in _deny_permissions  tinyint
                              )
            begin
                declare exit handler for 1146 # Table doesn\'t exist
                    begin                     # so thing is cached.
                        # Temporary communism. TODO: figure out the best
                        # place to create the table. It can\'t be done in
                        # stored routines, so it can\'t be done here and
                        # we only want to do it when it is necessary.
                    end;
                # Tables cannot be created inside stored routines
                # so this cannot automatically create the cache
                # table if it doesn\'t exist. So it is done when
                # the stored routines are created.
                insert into actual_permissions_cache
                values (_securableitem_id, _permitable_id, _allow_permissions, _deny_permissions);
            end;',

            'create procedure clear_cache_securableitem_actual_permissions(
                                in _securableitem_id int(11)
                              )
            begin
                declare continue handler for 1146 # Table doesn\'t exist.
                    begin
                        # noop - nothing to clear.
                    end;
                delete from actual_permissions_cache
                where securableitem_id = _securableitem_id;
            end;',

            'create procedure clear_cache_all_actual_permissions()
            READS SQL DATA
            begin
                declare continue handler for 1146 # Table doesn\'t exist.
                    begin
                        # noop - nothing to clear.
                    end;
                delete from actual_permissions_cache;
            end;',

            // Read Permissions (Munge)

            'create procedure rebuild(
                                in model_table_name varchar(255),
                                in munge_table_name varchar(255)
                              )
            begin
                call recreate_tables(munge_table_name);
                call rebuild_users  (munge_table_name);
                call rebuild_groups (munge_table_name);
                call rebuild_roles  (munge_table_name);
            end;',

            'create procedure recreate_tables(
                                in munge_table_name varchar(255)
                              )
            begin
                set @sql = concat("drop table if exists ", munge_table_name);
                prepare statement from @sql;
                execute statement;
                deallocate prepare statement;

                set @sql = concat("create table ", munge_table_name, " (",
                                        "securableitem_id      int(11)     unsigned not null, ",
                                        "munge_id              varchar(12)              null, ",
                                        "count                 int(8)      unsigned not null, ",
                                        "primary key (securableitem_id, munge_id))");
                prepare statement from @sql;
                execute statement;
                deallocate prepare statement;

                set @sql = concat("create index index_", munge_table_name, "_securableitem_id", " ",
                                        "on ", munge_table_name, " (securableitem_id)");
                prepare statement from @sql;
                execute statement;
                deallocate prepare statement;
            end;',

            'create procedure rebuild_users(
                                in munge_table_name varchar(255)
                              )
            begin
                declare _securableitem_id, __user_id, _permitable_id int(11);
                declare no_more_records tinyint default 0;
                declare securableitem_user_and_permitable_ids cursor for
                    select securableitem_id, _user.id, permission.permitable_id
                    from   permission, _user
                    where  permission.permitable_id = _user.permitable_id;
                declare continue handler for not found
                    set no_more_records = 1;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                    end;

                open securableitem_user_and_permitable_ids;
                fetch securableitem_user_and_permitable_ids into _securableitem_id, __user_id, _permitable_id;
                while no_more_records = 0 do
                    call rebuild_a_permitable(munge_table_name, _securableitem_id, __user_id, _permitable_id, "U");
                    fetch securableitem_user_and_permitable_ids into _securableitem_id, __user_id, _permitable_id;
                end while;
                close securableitem_user_and_permitable_ids;
            end;',

            // This procedure is largely duplication of the previous
            // procedure, but because variable names cannot be used
            // in cursors there isn't much to be done about it.
            'create procedure rebuild_groups(
                                in munge_table_name varchar(255)
                              )
            begin
                declare _securableitem_id, __group_id, _permitable_id int(11);
                declare no_more_records tinyint default 0;
                declare securableitem_group_and_permitable_ids cursor for
                    select securableitem_id, _group.id, permission.permitable_id
                    from   permission, _group
                    where  permission.permitable_id = _group.permitable_id;
                declare continue handler for not found
                    set no_more_records = 1;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                    end;

                open securableitem_group_and_permitable_ids;
                fetch securableitem_group_and_permitable_ids into _securableitem_id, __group_id, _permitable_id;
                while no_more_records = 0 do
                    call rebuild_a_permitable(munge_table_name, _securableitem_id, __group_id, _permitable_id, "G");
                    fetch securableitem_group_and_permitable_ids into _securableitem_id, __group_id, _permitable_id;
                end while;
                close securableitem_group_and_permitable_ids;
            end;',

            'create procedure rebuild_a_permitable(
                                in munge_table_name varchar(255),
                                in securableitem_id int(11),
                                in actual_id        int(11),
                                in _permitable_id   int(11),
                                in _type            char
                              )
            begin
                declare allow_permissions, deny_permissions, effective_explicit_permissions smallint default 0;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                    end;

                call get_securableitem_explicit_actual_permissions_for_permitable(securableitem_id, _permitable_id, allow_permissions, deny_permissions);
                set effective_explicit_permissions = allow_permissions & ~deny_permissions;
                if (effective_explicit_permissions & 1) = 1 then # Permission::READ
                    call increment_count(munge_table_name, securableitem_id, actual_id, _type);
                    if _type = "G" then
                        call rebuild_roles_for_users_in_group(munge_table_name, securableitem_id, actual_id);
                        call rebuild_sub_groups              (munge_table_name, securableitem_id, actual_id);
                    end if;
                end if;
            end;',

            'create procedure rebuild_roles_for_users_in_group(
                                in munge_table_name  varchar(255),
                                in _securableitem_id int(11),
                                in __group_id        int(11)
                              )
            begin
                declare _role_id int(11);
                declare no_more_records tinyint default 0;
                declare role_ids cursor for
                    select role_id
                    from   _group__user, _user
                    where  _group__user._group_id = __group_id and
                           _user.id = _group__user._user_id;
                declare continue handler for not found
                    set no_more_records = 1;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                    end;

                open role_ids;
                fetch role_ids into _role_id;
                while no_more_records = 0 do
                    call increment_parent_roles_counts(munge_table_name, _securableitem_id, _role_id);
                    fetch role_ids into _role_id;
                end while;
                close role_ids;
            end;',

            'create procedure rebuild_sub_groups(
                                in munge_table_name  varchar(255),
                                in _securableitem_id int(11),
                                in __group_id        int(11)
                              )
            begin
                declare sub_group_id int(11);
                declare no_more_records tinyint default 0;
                declare sub_group_ids cursor for
                    select id
                    from   _group
                    where  _group_id = __group_id;
                declare continue handler for not found
                    set no_more_records = 1;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                    end;

                open sub_group_ids;
                fetch sub_group_ids into sub_group_id;
                while no_more_records = 0 do
                    call increment_count                 (munge_table_name, _securableitem_id, sub_group_id, "G");
                    call rebuild_roles_for_users_in_group(munge_table_name, _securableitem_id, sub_group_id);
                    call rebuild_sub_groups              (munge_table_name, _securableitem_id, sub_group_id);
                    fetch sub_group_ids into sub_group_id;
                end while;
                close sub_group_ids;
            end;',

            'create procedure rebuild_roles(
                                in munge_table_name varchar(255)
                              )
            begin
                call rebuild_roles_owned_securableitems                         (munge_table_name);
                call rebuild_roles_securableitem_with_explicit_user_permissions (munge_table_name);
                call rebuild_roles_securableitem_with_explicit_group_permissions(munge_table_name);
            end;',

            'create procedure rebuild_roles_owned_securableitems(
                                in munge_table_name varchar(255)
                              )
            begin
                declare _role_id, _securableitem_id int(11);
                declare no_more_records tinyint default 0;
                declare role_and_securableitem_ids cursor for
                    select role_id, securableitem_id
                    from   _user, ownedsecurableitem
                    where  _user.id = ownedsecurableitem.owner__user_id and
                           role_id is not null;
                declare continue handler for not found
                    set no_more_records = 1;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                    end;

                open role_and_securableitem_ids;
                fetch role_and_securableitem_ids into _role_id, _securableitem_id;
                while no_more_records = 0 do
                    call increment_parent_roles_counts(munge_table_name, _securableitem_id, _role_id);
                    fetch role_and_securableitem_ids into _role_id, _securableitem_id;
                end while;
                close role_and_securableitem_ids;
            end;',

            'create procedure rebuild_roles_securableitem_with_explicit_user_permissions(
                                in munge_table_name varchar(255)
                              )
            begin
                declare _role_id, _securableitem_id int(11);
                declare no_more_records tinyint default 0;
                declare role_and_securableitem_ids cursor for
                    select role_id, securableitem_id
                    from   permission, _user
                    where  permission.permitable_id = _user.permitable_id and
                           ((permissions & 1) = 1)                        and
                           type = 1;
                declare continue handler for not found
                    set no_more_records = 1;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                    end;

                open role_and_securableitem_ids;
                fetch role_and_securableitem_ids into _role_id, _securableitem_id;
                while no_more_records = 0 do
                    call increment_parent_roles_counts(munge_table_name, _securableitem_id, _role_id);
                    fetch role_and_securableitem_ids into _role_id, _securableitem_id;
                end while;
                close role_and_securableitem_ids;
            end;',

            'create procedure rebuild_roles_securableitem_with_explicit_group_permissions(
                                in munge_table_name varchar(255)
                              )
            begin
                declare _role_id, _securableitem_id int(11);
                declare no_more_records tinyint default 0;
                declare role_and_securableitem_ids cursor for
                    select role.role_id, securableitem_id
                    from   _user, _group, _group__user, permission, role
                    where  _user.id = _group__user._user_id                and
                           permission.permitable_id = _group.permitable_id and
                           _group__user._group_id = _group.id              and
                           _user.role_id = role.role_id                    and
                           ((permissions & 1) = 1)                         and
                           type = 1;
                declare continue handler for not found
                    set no_more_records = 1;
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                    end;

                open role_and_securableitem_ids;
                fetch role_and_securableitem_ids into _role_id, _securableitem_id;
                while no_more_records = 0 do
                    call increment_count              (munge_table_name, _securableitem_id, _role_id, "R");
                    call increment_parent_roles_counts(munge_table_name, _securableitem_id, _role_id);
                    fetch role_and_securableitem_ids into _role_id, _securableitem_id;
                end while;
                close role_and_securableitem_ids;
            end;',

            'create procedure increment_count(
                                in munge_table_name varchar(255),
                                in securableitem_id int(11),
                                in item_id          int(11),
                                in _type            char
                              )
            begin
                # TODO: insert only if the row doesn\'t exist
                # in a way that doesn\'t ignore all errors.
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                    end;

                set @sql = concat("insert into ", munge_table_name,
                                  "(securableitem_id, munge_id, count) ",
                                  "values (", securableitem_id, ", \'", concat(_type, item_id), "\', 1) ",
                                  "on duplicate key ",
                                  "update count = count + 1");
                prepare statement from @sql;
                execute statement;
                deallocate prepare statement;
            end;',

            'create procedure decrement_count(
                                in munge_table_name  varchar(255),
                                in _securableitem_id int(11),
                                in item_id           int(11),
                                in _type             char
                             )
            begin
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                    end;

                update munge_table_name
                set count = count - 1
                where securableitem_id = _securableitem_id and
                      munge_id         = concat(_type, item_id);
            end;',

            'create procedure increment_parent_roles_counts(
                                in munge_table_name varchar(255),
                                in securableitem_id int(11),
                                in _role_id         int(11)
                              )
            begin
                declare parent_role_id int(11);
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                    end;

                select role_id
                into   parent_role_id
                from   role
                where  id = _role_id;
                if parent_role_id is not null then
                    call increment_count              (munge_table_name, securableitem_id, parent_role_id, "R");
                    call increment_parent_roles_counts(munge_table_name, securableitem_id, parent_role_id);
                end if;
            end;',

            'create procedure decrement_parent_roles_counts(
                                in munge_table_name varchar(255),
                                in securableitem_id int(11),
                                in role_id          int(11)
                              )
            begin
                declare parent_role_id int(11);
                declare exit handler for 1054, 1146 # Column, table doesn\'t exist.
                    begin                           # RedBean hasn\'t created it yet.
                    end;

                select role_id
                into   parent_role_id
                from   role
                where  id = role_id;
                if parent_role_id is not null then
                    call decrement_count              (munge_table_name, securableitem_id, parent_role_id);
                    call decrement_parent_roles_counts(munge_table_name, securableitem_id, parent_role_id);
                end if;
            end;',
        );

        public static function callFunction($sql)
        {
            try
            {
                return R::getCell("select $sql;");
            }
            catch (RedBean_Exception_SQL $e)
            {
                self::createStoredFunctionsAndProcedures();
                self::createActualPermissionsCacheTable();
                return R::getCell("select $sql;");
            }
        }

        public static function callProcedureWithoutOuts($sql)
        {
            try
            {
                return R::getCell("call $sql;");
            }
            catch (RedBean_Exception_SQL $e)
            {
                self::createStoredFunctionsAndProcedures();
                self::createActualPermissionsCacheTable();
                return R::getCell("call $sql;");
            }
        }

        public static function createStoredFunctionsAndProcedures()
        {
            assert('RedBeanDatabase::isSetup()');
            if (RedBeanDatabase::getDatabaseType() == 'mysql')
            {
                self::dropStoredFunctionsAndProcedures();
                try
                {
                    foreach (self::$storedFunctions as $sql)
                    {
                        $sql = self::stripNonFrozenModeErrorHandlersIfFrozen($sql);
                        R::exec($sql);
                    }
                    foreach (self::$storedProcedures as $sql)
                    {
                        $sql = self::stripNonFrozenModeErrorHandlersIfFrozen($sql);
                        R::exec($sql);
                    }
                    if (YII_DEBUG)
                    {
                        R::exec('create table if not exists log
                                 (timestamp timestamp, message varchar(255))');
                        R::exec('create procedure write_log(in message varchar(255))
                                 begin
                                     insert into log (timestamp, message)
                                     values (now(), message);
                                 end;');
                    }
                }
                catch (Exception $e)
                {
                    echo "Failed to create:\n$sql\n";
                    throw $e;
                }
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        // When run in unfrozen mode the stored function and procedures
        // must ignore tables and columns that don't exist because they
        // will be created as required, and the error handlers as coded
        // cater for this. In frozen mode that represents a real error
        // and must be allowed to escape. In frozen mode with debug off
        // the error handlers are stripped from the routines. In frozen
        // mode with debug on a write_log() call is added to the error
        // handler which writes to the log table.
        protected static function stripNonFrozenModeErrorHandlersIfFrozen($sql)
        {
            if (RedBeanDatabase::isFrozen())
            {
                // It is deliberate that these regexs are written to not catch
                // handlers that are not written consistently with the existing
                // ones. If the assertions blow up look at making your handler
                // match the expression rather than messing with the expression.
                if (!YII_DEBUG)
                {
                    $sql = preg_replace('/ +declare (continue|exit) handler for (1054|1146|1054, 1146).*?begin.*?end;\n/s',
                                        '',
                                        $sql, -1, $count);
                }
                else
                {
                    $matched = preg_match('/create (function|procedure) ([^( ]+)/', $sql, $matches); // Not Coding Standard
                    assert('$matched == 1');
                    $routineName = $matches[2];
                    $sql = preg_replace('/( +declare (continue|exit) handler for (1054|1146|1054, 1146).*?begin.*?)(end;\n)/s',
                                        "\\1    call write_log(\"$routineName failed! (\\3)\");\n                    \\4",
                                        $sql, -1, $count);
                }

                if (!YII_DEBUG)
                {
                        assert('strpos($sql, "1146") == false'); // Table  doesn't exist.
                        assert('strpos($sql, "1054") == false'); // Column doesn't exist.
                }
            }
            return $sql;
        }

        public static function createActualPermissionsCacheTable()
        {
            R::exec('
                create table if not exists actual_permissions_cache
                    (securableitem_id int(11) unsigned not null,
                     permitable_id     int(11) unsigned not null,
                     allow_permissions tinyint unsigned not null,
                     deny_permissions  tinyint unsigned not null,
                     primary key (securableitem_id, permitable_id)
                    ) engine = innodb
                      default charset = utf8
                              collate = utf8_unicode_ci');
        }

        public static function dropStoredFunctionsAndProcedures()
        {
            assert('RedBeanDatabase::isSetup()');
            if (RedBeanDatabase::getDatabaseType() == 'mysql')
            {
                try
                {
                    $rows = R::getAll("select routine_name, routine_type from information_schema.routines;");
                    foreach ($rows as $row)
                    {
                        R::exec("drop {$row['routine_type']} if exists {$row['routine_name']}");
                    }
                }
                catch (Exception $e)
                {
                    echo "Failed to drop {$row['routine_type']} {$row['routine_name']}.\n";
                    throw $e;
                }
                if (YII_DEBUG)
                {
                    R::exec("drop procedure if exists write_log");
                }
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>
