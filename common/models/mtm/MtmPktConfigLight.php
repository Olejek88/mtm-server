<?php

namespace common\models\mtm;

/**
 *
 * @property mixed $base64Data
 * @property array $binaryData
 */
class MtmPktConfigLight extends MtmPktHeader
{
    const MAX_LIGHT_CONFIG = 5;
    public $device;
    public $config;

    public function __construct($config = [])
    {
        parent::__construct($config);
        for ($i = 0; $i < self::MAX_LIGHT_CONFIG; $i++) {
            $config[$i] = ['time' => 0, 'value' => 0];
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device'], 'integer', 'min' => 0x00, 'max' => 0x0f],
            [['config'], 'each', 'rule' => ['checkConfigElement']],
        ];
    }

    private function checkConfigElement($attr, $param)
    {
        $configValues = $this->attributes[$attr];

        if (!is_array($configValues)) {
            $this->addError($attr, 'Должен быть список элементов (min, max)');
            return;
        }

        foreach ($configValues as $value) {
            if (!is_array($value)) {
                $this->addError($attr, 'Должен быть массив с элементами min, max');
                return;
            }

            if (!array_key_exists('min', $value) || !array_key_exists('max', $value)) {
                $this->addError($attr, 'Должен быть элементы min, max');
                return;
            }

            if ($value['min'] > 0xffff || $value['max'] > 0xffff) {
                $this->addError($attr, 'min, max должны быть в диапазоне 0x0000 - 0xffff');
                return;
            }
        }
    }

    public function getBinaryData()
    {
        // TODO: реализовать упаковку пакета в массив
        $binary = [];
        return $binary;
    }

    public function getBase64Data()
    {
        $string = '';
        foreach ($this->getBinaryData() as $value) {
            $string .= chr($value);
        }

        return base64_encode($string);
    }

}