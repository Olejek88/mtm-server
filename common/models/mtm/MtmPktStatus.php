<?php

namespace common\models\mtm;

class MtmPktStatus extends MtmPktHeader
{
    public $mac;
    public $alert;
    public $data;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['mac', 'string', 'length' => [16]],
            ['alert', 'integer', 'min' => 0x00, 'max' => 0xff],
            ['data', 'each', 'rule' => ['integer', 'min' => 0x00, 'max' => 0xffff]],
            ['data', 'each', 'rule' => ['checkDataSize']],
        ];
    }

    private function checkDataSize($attr, $param)
    {
        $statusValues = $this->attributes[$attr];

        if (!is_array($statusValues)) {
            $this->addError($attr, 'Должен быть список элементов ');
            return;
        }

        $count = count($statusValues);
        if ($count == 0 || $count > 16) {
            $this->addError($attr, 'Список элементов должен быть больше 0 и меньше 17');
            return;
        }
    }

}