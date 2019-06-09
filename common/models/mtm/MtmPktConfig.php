<?php

namespace common\models\mtm;

/**
 *
 * @property mixed $base64Data
 * @property array $binaryData
 */
class MtmPktConfig extends MtmPktHeader
{
    public $device;
    public $min;
    public $max;

    // TODO: добавить константы для уже зафиксированных команд конфигурирования устройств MTM_DEVICE_LIGHT

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device', 'min', 'max'], 'required'],
            [['device'], 'integer', 'min' => 0x00, 'max' => 0x0f],
            [['min', 'max'], 'integer', 'min' => 0x00, 'max' => 0xffff],
        ];
    }

    public function getBinaryData()
    {
        // TODO: реализовать упаковку пакета в массив
        $binary = [];
        return $binary;
    }

    public function getBase64Data() {
        $string = '';
        foreach ($this->getBinaryData() as $value) {
            $string .= chr($value);
        }

        return base64_encode($string);
    }

}