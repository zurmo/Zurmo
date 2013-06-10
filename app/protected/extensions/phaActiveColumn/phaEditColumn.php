<?php

/**
 * phaEditColumn class file.
 *
 * @author Vadim Kruchkov <long@phargo.net>
 * @link http://www.phargo.net/
 * @copyright Copyright &copy; 2011 phArgo Software
 * @license GPL & MIT
 */
class phaEditColumn extends phaAbsActiveColumn {

    /**
     * @var array Additional HTML attributes. See details {@link CHtml::inputField}
     */
    public $htmlEditFieldOptions = array();

    /**
     * Renders the data cell content.
     * This method evaluates {@link value} or {@link name} and renders the result.
     *
     * @param integer $row the row number (zero-based)
     * @param mixed $data the data associated with the row
     */
    protected function renderDataCellContent($row,$data) {

        if($this->value!==null)
            $value=$this->evaluateExpression($this->value,array('data'=>$data,'row'=>$row));
        elseif($this->name!==null)
            $value = CHtml::value($data, $this->name);

        $valueId = $data->{$this->modelId};
        $this->htmlEditFieldOptions['itemId'] = $valueId;
        $this->htmlEditFieldOptions['style']  = 'width:50px;';
        $fieldUID = $this->getViewDivClass();

        echo CHtml::tag('div', array(
            'valueid' => $valueId,
            'id' => $fieldUID  .'-' . $valueId,
            'class' => $fieldUID  . ' editable-cell'
        ), $value);

        echo CHtml::openTag('div', array(
            'style' => 'display: none;',
            'id' => $this->getFieldDivClass() . $data->{$this->modelId},
        ));
        echo CHtml::textField($this->name.'[' . $valueId . ']', $value, $this->htmlEditFieldOptions);
        echo CHtml::closeTag('div');
    }

    /**
     * @return string Name of div's class for view value
     */
    protected function getViewDivClass( ) {
        return 'viewValue-' . $this->id;
    }

    /**
     * @return string Name of div's class for edit field
     */
    protected function getFieldDivClass( ) {
        return 'field-' . $this->id . '-';
    }

    /**
     * Initializes the column.
     *
     * @see CDataColumn::init()
     */
    public function init() {

        parent::init();

        $cs=Yii::app()->getClientScript();

        $liveClick ='
        phaACActionUrls["'.$this->id.'"]="' . $this->buildActionUrl() . '";
        jQuery(".'. $this->getViewDivClass() . '").live("click", function(e){
            phaACOpenEditField(this, "' . $this->id . '");
            return false;
        });';

        $script ='
        var phaACOpenEditItem = 0;
        var phaACOpenEditGrid = "";
        var phaACActionUrls = [];
        function phaACOpenEditField(itemValue, gridUID, grid ) {
            phaACHideEditField( phaACOpenEditItem, phaACOpenEditGrid );
            var id   = $(itemValue).attr("valueid");
            phaACOpenEditItem = id;
            $("#viewValue-" + gridUID + "-"+id).hide();
            var inputValue = $("#field-" + gridUID + "-" + phaACOpenEditItem+" input").val();
            var matches;
            if(!$.isNumeric(inputValue.charAt(0)))
            {
                matches = inputValue.match(/([0-9]+.[0-9]*)/);
                inputValue = matches[1];
            }
            $("#field-" + gridUID + "-" + phaACOpenEditItem+" input").val(inputValue);
            $("#field-" + gridUID + "-" + id).show();
            $("#field-" + gridUID + "-" + id+" input")
                .focus()
                .keydown(function(event) {
                    switch (event.keyCode) {
                       case 27:
                       case 9:
                          //phaACHideEditField(phaACOpenEditItem, gridUID);
                          phaACEditFieldSend(itemValue, gridUID);
                          break;
                       case 13:
                          phaACEditFieldSend(itemValue, gridUID);
                          break;
                       default: break;
                    }
                })
                .blur(function(){
                    //phaACHideEditField(phaACOpenEditItem, gridUID);
                    phaACEditFieldSend(itemValue, gridUID);
                });


            phaACOpenEditGrid = gridUID;
        }
        function phaACHideEditField( itemId, gridUID ) {
            var clearVal = $("#viewValue-" + gridUID + "-"+itemId).text();
            $("#field-" + gridUID + "-" + itemId+" input").val( clearVal );
            $("#field-" + gridUID + "-" + itemId).hide();
            $("#field-" + gridUID + "-" + itemId+" input").unbind("keydown");
            $("#viewValue-" + gridUID + "-" + itemId).show();
            phaACOpenEditItem=0;
            phaACOpenEditGrid = "";
        }
        function phaACEditFieldSend( itemValue, gridUID ) {
            var passedValue = $("#field-"+phaACOpenEditGrid+"-"+phaACOpenEditItem+" input").val();
            $("#viewValue-" + gridUID + "-"+phaACOpenEditItem).html(passedValue);
            $("#field-" + gridUID + "-" + phaACOpenEditItem).hide();
            $("#field-" + gridUID + "-" + phaACOpenEditItem+" input").unbind("keydown");
            $("#viewValue-" + gridUID + "-" + phaACOpenEditItem).show();
            var id = $(itemValue).parents(".cgrid-view").attr("id");
            $.ajax({
                    type: "GET",
                    dataType: "json",
                    url: phaACActionUrls[gridUID],
                    cache: false,
                    data: {
                        item: phaACOpenEditItem,
                        value: passedValue
                    },
                    success: function(data){
                      $("#"+id).yiiGridView.update( id );
                    }
                });
        }
        ';

        $cs->registerScript(__CLASS__.'#active_column-edit', $script);
        $cs->registerScript(__CLASS__.$this->grid->id.'#active_column_click-'.$this->id, $liveClick);
    }
}