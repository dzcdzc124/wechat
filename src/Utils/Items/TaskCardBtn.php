<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-17 14:40:13 +0800
 */
namespace fwkit\Wechat\Utils\Items;

use fwkit\Wechat\Traits\HasOptions;
use JsonSerializable;

class TaskCardBtn implements JsonSerializable
{
    use HasOptions;

    public $key;

    public $name;

    public $replaceName;

    public $color = 'blue';

    public $bold = false;

    public function __construct(array $options)
    {
        $this->setOptions($options);
    }

    public function jsonSerialize()
    {
        return [
            'key'           => $this->key,
            'name'          => $this->name,
            'replace_name'  => $this->replaceName,
            'color'         => $this->color,
            'is_bold'       => $this->bold,
        ];
    }
}
