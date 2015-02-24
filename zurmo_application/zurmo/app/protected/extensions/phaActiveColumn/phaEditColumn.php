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
     * @var array Additional HTML attributes for decoration .
     */
    public $htmlEditDecorationOptions = array();

    /**
     * Renders the data cell content.
     * This method evaluates {@link value} or {@link name} and renders the result.
     *
     * @param integer $row the row number (zero-based)
     * @param mixed $data the data associated with the row
     */
    protected function renderDataCellContent($row,$data) {
        $value = CHtml::value($data, $this->name);
        $valueId = $data->{$this->modelId};

        $this->htmlEditFieldOptions['itemId'] = $valueId;
        $fieldUID = $this->getViewDivClass();

        echo CHtml::tag('div', array(
            'valueid' => $valueId,
            'id' => $fieldUID.'-'.$valueId,
            'class' => $fieldUID,
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
        phaACActionUrls["'.$this->grid->id.'"]="' . $this->buildActionUrl() . '";
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

            $("#viewValue-" + gridUID + "-"+id).hide();
            $("#field-" + gridUID + "-" + id).show();
            $("#field-" + gridUID + "-" + id+" input")
                .focus()
                .keydown(function(event) {
                    switch (event.keyCode) {
                       case 27:
                          phaACHideEditField( phaACOpenEditItem, gridUID );
                       break;
                       case 13:
                          phaACEditFieldSend( itemValue );
                       break;
                       default: break;
                    }
                });

            phaACOpenEditItem = id;
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
        function phaACEditFieldSend( itemValue ) {
            var id = $(itemValue).parents(".grid-view").attr("id");
            $.ajax({
                type: "POST",
                dataType: "json",
                cache: false,
                url: phaACActionUrls[id],
                data: {
                    item: phaACOpenEditItem,
                    value: $("#field-"+phaACOpenEditGrid+"-"+phaACOpenEditItem+" input").val()
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