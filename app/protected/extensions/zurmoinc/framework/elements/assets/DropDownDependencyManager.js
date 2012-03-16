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

DropDownDependencyManager = function(name_var) {
      this.init(name_var);
    }
$.extend(DropDownDependencyManager.prototype, {
    dependencyData: {},
    startingOptionsData: {},

   init: function(dependencyData) {
     this.dependencyData = jQuery.parseJSON(dependencyData);
     var context = this;
     $.each(this.dependencyData, function() {
         var position = 0;
         var inputId  = this.inputId;
         context.startingOptionsData[this.inputId] = {};
         $('#' + this.inputId + ' > option').each(function() {
             var options   = {};
             options.value = this.value;
             options.text  = this.text;
             position ++;
             context.startingOptionsData[inputId][position] = options;
         });
     });
   },

   resolveOptions: function() {
       this.resetOptions();
       $.each(this.dependencyData, function() {
           if(this.parentInputId != null)
           {
               var inputId				= this.inputId;
               var parentSelectedValue  = $('#' + this.parentInputId).val();
               var selectedValue		= $('#' + this.inputId).val();
               var valueToAlwaysShow    = this.valueToAlwaysShow;
               var valuesToParentValues = this.valuesToParentValues;
               var notReadyToSelectText = this.notReadyToSelectText;
               $('#' + this.inputId + ' > option').each(function(){
                   if(this.value != valueToAlwaysShow && this.value != '')
                   {
                       if(valuesToParentValues[this.value] != parentSelectedValue)
                       {
                           $("#" + inputId + " option[value='" + this.value + "']").remove();
                       }
                   }
                   else if(this.value == '' && parentSelectedValue == '' && valueToAlwaysShow == null)
                   {
                       $("#" + inputId + " option[value='']").text(notReadyToSelectText);
                   }
               });

           }
       });
   },

   resetOptions: function() {
       $.each(this.startingOptionsData, function(inputId, optionsData) {
           var selectedValue = $('#' + inputId).val();
           $('#' + inputId).empty()
           var inputId = inputId;
           $.each(optionsData, function() {
               $('#' + inputId).append('<option value="' + this.value + '">' + this.text + '</option>');
           });
           $('#' + inputId).val(selectedValue);
       });
   }
});
