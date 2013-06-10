/*********************************************************************************
 * Zurmo is a customer relationship management program developed by
 * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
 *
 * Zurmo is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * Zurmo is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
 * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
 *
 * The interactive user interfaces in original and modified versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the Zurmo
 * logo and Zurmo copyright notice. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display the words
 * "Copyright Zurmo Inc. 2013. All rights reserved".
 ********************************************************************************/

$.widget(
    "ui.dialog", $.ui.dialog, {
        _size: function() {
            /* If the user has resized the dialog, the .ui-dialog and .ui-dialog-content
             * divs will both have width and height set, so we need to reset them
             */
            var nonContentHeight, minContentHeight, autoHeight,
                options = this.options,
                isVisible = this.uiDialog.is( ":visible" );

            // reset content sizing
            this.element.show().css({
                width: "auto",
                minHeight: 0,
                height: 0
            });

            if ( options.minWidth > options.width ) {
                options.width = options.minWidth;
            }

            // reset wrapper sizing
            // determine the height of all the non-content elements
            nonContentHeight = this.uiDialog.css({
                minHeight: '100px',
                //minWidth: '75%',
                //height: '94%',
                //width: 'auto',
                height: 'auto',
                position : 'absolute'
            }).outerHeight();

            minContentHeight = Math.max( 0, options.minHeight - nonContentHeight );

            if ( options.height === "auto" ) {
                // only needed for IE6 support
                if ( $.support.minHeight ) {
                    this.element.css({
                        minHeight: minContentHeight,
                        height: "auto"
                    });
                } else {
                    this.uiDialog.show();
                    autoHeight = this.element.css( "height", "auto" ).height();
                    if ( !isVisible ) {
                        this.uiDialog.hide();
                    }
                    this.element.height( Math.max( autoHeight, minContentHeight ) );
                }
            } else {
                this.element.height( Math.max( options.height - nonContentHeight, 0 ) );
            }

            if (this.uiDialog.is( ":data(resizable)" ) ) {
                this.uiDialog.resizable( "option", "minHeight", this._minHeight() );
            }
        }
    }
);