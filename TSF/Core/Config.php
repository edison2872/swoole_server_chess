<?php
/**
 * Created by IntelliJ IDEA.
 * User: roketyyang
 * Date: 2016/8/22
 * Time: 21:54
 */
namespace TSF\Core;

use TSF\Facade\App as App;

class Config
{
    protected $metas;
    protected $appName = "app";
    protected $fromApp = false;

    public function __construct()
    {
        $this->metas = [];
        $dir = App::facade()->getBasePath() . '/Config';
        foreach (scandir($dir) as $file) {
            if ($file[0] == '.') {
                continue;
            }
            $key = strchr($file, '.php', true);
            if ($key == false) {
                continue;
            }
            $file = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_file($file)) {
                $this->metas[$key] = require $file;
            }
        }
    }

    public function get($name, $default = null)
    {
        if ($this->appName != 'app' && strpos($name, 'app.') === 0) {
            $name = substr_replace($name, $this->appName, 0, 3);
        }
        $metas = $this->metas;
        foreach (explode('.', $name) as $key) {
            if (is_array($metas) && isset($metas[$key])) {
                $metas = $metas[$key];
            } else {
                return $default;
            }
        }

        return $metas;
    }

    public function set($name, $value)
    {
        if ($this->appName != 'app' && strpos($name, 'app.') === 0) {
            $name = substr_replace($name, $this->appName, 0, 3);
        }
        $keys = explode('.', $name);
        $metas = &$this->metas;
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($metas[$key]) || !is_array($metas[$key])) {
                $metas[$key] = [];
            }

            $metas = &$metas[$key];
        }

        $metas[array_shift($keys)] = $value;
    }

    public function setAppName($value)
    {
        $this->appName = $value;
    }
}