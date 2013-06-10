<?php
/**
 * Abstract phaAbsActiveColumn class file.
 * This class is base class for active grid column
 *
 * @author Vadim Kruchkov <long@phargo.net>
 * @link http://www.phargo.net/
 * @copyright Copyright &copy; 2011 phArgo Software
 * @license GPL & MIT
 */

Yii::import('zii.widgets.grid.CDataColumn');

abstract class phaAbsActiveColumn extends CDataColumn {

    /**
     * @var string name of models key
     */
    public $modelId = 'id';

    /**
     * @var mixed URL for update action. On this URL will be sent call to update value.
     *      If this value is string - value will be used as is.
     *      If it's array - will be called {@link CHtml::normalizeUrl}
     */
    public $actionUrl = array('.');

    /**
     * @return string Return action URL for processing data.
     */
    protected function buildActionUrl() {
        return (is_array($this->actionUrl) ? CHtml::normalizeUrl( $this->actionUrl ) : $this->actionUrl);
    }
}