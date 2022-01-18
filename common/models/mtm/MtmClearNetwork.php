<?php

namespace common\models\mtm;

/**
 *
 * @property mixed $base64Data
 * @property array $binaryData
 */
class MtmClearNetwork extends MtmPktHeader
{
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->type = self::$MTM_CMD_TYPE_CLEAR_NETWORK;
    }

    public function getBase64Data(): string
    {
        $string = '';
        foreach ($this->getBinaryData() as $value) {
            $string .= chr($value);
        }

        return base64_encode($string);
    }

    public function getBinaryData(): array
    {
        $binary = [];
        $binary[0] = $this->type;
        $binary[1] = $this->protoVersion;
        return $binary;
    }
}