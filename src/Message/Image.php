<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-02-14 17:19:03 +0800
 */
namespace fwkit\Wechat\Message;

class Image extends MessageBase
{
    public $picUrl;

    public $mediaId;

    protected function initialize(array $data)
    {
        $this->setAttributes($data, [
            'picurl' => 'picUrl',
            'mediaid' => 'mediaId',
        ]);
    }
}