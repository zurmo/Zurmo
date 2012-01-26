<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * This program is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * This program is distributed in the hope that it will be useful, but WITHOUT
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
     *
     * The interactive user interfaces in modified source and object code versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the "Powered by
     * Zurmo" logo. If the display of the logo is not reasonably feasible for
     * technical reasons, the Appropriate Legal Notices must display the words
     * "Powered by Zurmo".
     ********************************************************************************/

    define('MAJOR_VERSION', 0);                           // Update for marketing purposes.
    define('MINOR_VERSION', 5);                           // Update when functionality changes.
    define('PATCH_VERSION', 7);                           // Update when fixes are made that does not change functionality.
    define('REPO_ID',       '$Revision$');                // Updated by Mercurial. Numbers like 3650 have no meaning across
                                                          // clones. This tells us the actual changeset that is universally
                                                          // meaningful.

    define('VERSION', join('.', array(MAJOR_VERSION, MINOR_VERSION, PATCH_VERSION)) . ' (' . substr(REPO_ID, strlen('$Revision: '), -2) . ')');
?>
