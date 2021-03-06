<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-09-21 17:46:39 +0800
 */
namespace fwkit\Wechat\Message;

abstract class MessageBase
{
    protected static $types = [
        'image'                 => Image::class,
        'link'                  => Link::class,
        'location'              => Location::class,
        'shortvideo'            => ShortVideo::class,
        'text'                  => Text::class,
        'video'                 => Video::class,
        'voice'                 => Voice::class,
        'file'                  => File::class,
    ];

    protected static $events = [
        'click'                 => Event\Click::class,
        'location'              => Event\Location::class,
        'location_select'       => Event\LocationSelect::class,
        'pic_photo_or_album'    => Event\PicPhotoOrAlbum::class,
        'pic_sysphoto'          => Event\PicSysPhoto::class,
        'pic_weixin'            => Event\PicWeixin::class,
        'scan'                  => Event\Scan::class,
        'scancode_push'         => Event\ScanCodePush::class,
        'scancode_waitmsg'      => Event\ScanCodeWaitMsg::class,
        'subscribe'             => Event\Subscribe::class,
        'unsubscribe'           => Event\Unsubscribe::class,
        'view'                  => Event\View::class,
        'view_miniprogram'      => Event\ViewMiniProgram::class,
    ];

    protected static $replies = [
        'image'                 => Reply\Image::class,
        'music'                 => Reply\Music::class,
        'news'                  => Reply\News::class,
        'text'                  => Reply\Text::class,
        'video'                 => Reply\Video::class,
        'voice'                 => Reply\Voice::class,
        'raw'                   => Reply\Raw::class,
    ];

    protected $attributes = [];

    protected $rawXml;

    protected $data;

    protected $cryptor;

    public $id;

    public $type;

    public $accountId;

    public $openId;

    public $createTime;

    public function __construct(string $rawXml, array $data)
    {
        $this->rawXml = $rawXml;

        $this->setData($data);
        $this->initialize();
    }

    public function __get(string $property)
    {
        $method = 'get' . ucfirst($property);
        if (method_exists($this, $method)) {
            return $this->{$method}();
        } else {
            return isset($this->attributes[$property]) ? $this->attributes[$property] : null;
        }
    }

    public function setCryptor($cryptor)
    {
        $this->cryptor = $cryptor;
        return $this;
    }

    public function get($key, $default = null)
    {
        $key = strtolower($key);
        return array_get($this->data, $key, $default);
    }

    public function rawXml()
    {
        return $this->rawXml;
    }

    public function reply(string $type = 'text')
    {
        $className = static::$replies[$type] ?? Reply\Unknown::class;
        $reply = new $className($this->accountId, $this->openId);
        $reply->setCryptor($this->cryptor);
        return $reply;
    }

    public static function factory(string $message)
    {
        $data = (array) @simplexml_load_string($message, 'SimpleXMLElement', LIBXML_NOCDATA);
        $data = array_change_key_case_recursive($data);

        if (empty($data) || !isset($data['msgtype'])) {
            return null;
        }

        $msgType = strtolower($data['msgtype']);
        if ($msgType === 'event') {
            $event = strtolower($data['event']);
            $className = static::$events[$event] ?? Event\Unknown::class;
        } else {
            $className = static::$types[$msgType] ?? Unknown::class;
        }

        return new $className($message, $data);
    }

    public function withAttribute($name, $value): self
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    public function withoutAttribute($name): self
    {
        unset($this->attributes[$name]);
        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttribute($name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    public function isEvent(...$types): bool
    {
        return false;
    }

    protected function setData(array $data)
    {
        $this->data = $data;

        $this->id = $data['msgid'] ?? null;
        $this->type = isset($data['msgtype']) ? strtolower($data['msgtype']) : null;
        $this->accountId = $data['tousername'] ?? null;
        $this->openId = $data['fromusername'] ?? null;
        $this->createTime = (int) $data['createtime'] ?? 0;
    }

    protected function initialize()
    {
    }
}
