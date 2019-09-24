<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-09-24 16:47:06 +0800
 */
namespace fwkit\Wechat\Concerns;

use fwkit\Wechat\Utils\Cache;

trait HasAccessToken
{
    protected static $tokenGetter;

    protected $tokenComponent = 'token';

    protected $accessToken;

    protected $expiresIn = 0;

    public function getAccessToken(bool $forceUpdate = false)
    {
        if ($this->accessToken && $this->expiresIn > time() && !$forceUpdate) {
            return $this->accessToken;
        }

        $accessToken = null;
        $expiresIn = 0;
        if (!$forceUpdate) {
            $accessToken = Cache::get($this->appId, 'accessToken');
            $expiresIn = (int) Cache::get($this->appId, 'accessToken_expiresIn');
        }

        if (empty($accessToken)) {
            if (static::$tokenGetter && is_callable(static::$tokenGetter)) {
                $accessToken = call_user_func(static::$tokenGetter, $this->appId);
            } else {
                $tokenComponent = $this->component($this->tokenComponent);
                try {
                    $res = $tokenComponent->getAccessToken();
                    $accessToken = $res->get('accessToken', null);

                    $ttl = (int) max(1, $res->get('expiresIn', 0) - 600);
                    $expiresIn = time() + $ttl;
                    Cache::set($this->appId, 'accessToken', $accessToken, $ttl);
                    Cache::set($this->appId, 'accessToken_expiresIn', $expiresIn, $ttl);
                } catch (\Exception $e) {
                }
            }
        }

        $this->accessToken = $accessToken;
        $this->expiresIn = $expiresIn;
        return $accessToken;
    }

    public function getAccessTokenExpiresIn(): int
    {
        $expiresIn = $this->expiresIn ?: 0;
        if (!$expiresIn) {
            $expiresIn = (int) Cache::get($this->appId, 'accessToken_expiresIn');
            $this->expiresIn = $expiresIn;
        }

        return $expiresIn;
    }

    public static function setTokenGetter(callable $func)
    {
        static::$tokenGetter = $func;
    }
}
