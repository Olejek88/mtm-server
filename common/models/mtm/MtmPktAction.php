<?php

namespace common\models\mtm;

/**
 *
 * @property mixed $base64Data
 * @property array $binaryData
 */
class MtmPktAction extends MtmPktHeader
{
    public $device;
    public $data;

    // TODO: добавить константы для уже зафиксированных команд для устройств MTM_DEVICE_LIGHT и их параметров

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device'], 'integer', 'min' => 0x00, 'max' => 0x0f],
            [['data'], 'integer', 'min' => 0x00, 'max' => 0xffff],
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