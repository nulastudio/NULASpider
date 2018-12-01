<?php

namespace nulastudio\Networking\Rpc;

use nulastudio\Networking\Rpc\SimpleAria2JsonRpcClient;

class Aria2
{
    const POS_SET = 'POS_SET';
    const POS_CUR = 'POS_CUR';
    const POS_END = 'POS_END';

    private $aria2;
    private $token;
    public $savePath;

    private $last_error_code;
    private $last_error_message;

    public function __construct(string $url, string $token = null, string $savePath = '')
    {
        $this->aria2 = new SimpleAria2JsonRpcClient($url, $token);
        $this->token = $token;
    }

    /**
     * 投递一个或多个下载链接至aria2，支持HTTP/HTTPS/FTP/SFTP/Magnet链接，当投递Magnet链接时，一次只能投递一个链接
     * @param  mixed  $url                                                       投递链接
     * @param  string $savePath                                                  保存位置，当以“/”结尾时，将当前文件保存到指定目录，否则保存到指定的文件名
     * @param  array  $options                                                   额外参数，参见aria2文档
     * @param  int    $position                                                  插入位置，当大于队列长度或未指定时插入到队列尾部
     * @return mixed  成功返回最新下载链接的GID，否则返回false
     */
    public function addUri($url, string $savePath = '', array $options = [], int $position = null)
    {
        if ($this->savePath) {
            $options['dir'] = $this->savePath;
        }
        if ($savePath) {
            $savePath = str_replace('\\', '/', $savePath);
            if (substr($savePath, -1) === '/') {
                $options['dir'] = substr($savePath, 0, -1);
            } else {
                $options['out'] = $savePath;
            }
        }
        $params = [
            is_array($url) ? $url : [$url],
            $options ?: '{}',
        ];
        if ($position !== null) {
            $params[] = $position;
        }
        $response = json_decode($this->aria2->addUri(...$params), true);
        $this->handleError($response);
        return $response['result'] ?: false;
    }

    /**
     * 投递种子文件（种子需要经过base64编码）
     * @param string   $torrent  经base64编码后的种子文件
     * @param array    $urls     used for Web-seeding
     * @param string   $savePath 同addUri
     * @param array    $options  同addUri
     * @param int|null $position 同addUri
     */
    public function addTorrent(string $torrent, array $urls = [], string $savePath = '', array $options = [], int $position = null)
    {
        if ($this->savePath) {
            $options['dir'] = $this->savePath;
        }
        if ($savePath) {
            $savePath = str_replace('\\', '/', $savePath);
            if (substr($savePath, -1) === '/') {
                $options['dir'] = substr($savePath, 0, -1);
            } else {
                $options['out'] = $savePath;
            }
        }
        $params = [
            $torrent,
        ];
        if ($urls) {
            $params[] = $urls;
        }
        if ($options) {
            $params[] = $options;
        }
        if ($position !== null) {
            $params[] = $position;
        }
        $response = json_decode($this->aria2->addTorrent(...$params), true);
        $this->handleError($response);
        return $response['result'] ?: false;
    }

    /**
     * 投递一个metalink文件
     * @param string   $metalink metalink文件
     * @param string   $savePath 同addUri
     * @param array    $options  同addUri
     * @param int|null $position 同addUri
     */
    public function addMetalink(string $metalink, string $savePath = '', array $options = [], int $position = null)
    {
        if ($this->savePath) {
            $options['dir'] = $this->savePath;
        }
        if ($savePath) {
            $savePath = str_replace('\\', '/', $savePath);
            if (substr($savePath, -1) === '/') {
                $options['dir'] = substr($savePath, 0, -1);
            } else {
                $options['out'] = $savePath;
            }
        }
        $params = [
            $metalink,
            $options ?: '{}',
        ];
        if ($position !== null) {
            $params[] = $position;
        }
        $response = json_decode($this->aria2->addMetalink(...$params), true);
        $this->handleError($response);
        return $response['result'] ?: false;
    }

    /**
     * 移除指定的下载项
     * @param  string $gid 下载项的GID
     * @return mixed 成功移除的下载项的GID，失败返回false
     */
    public function remove(string $gid)
    {
        $response = json_decode($this->aria2->remove($gid), true);
        $this->handleError($response);
        return $response['result'] ?: false;
    }

    /**
     * 强制移除指定的下载项
     * @param  string $gid 下载项的GID
     * @return mixed 成功移除的下载项的GID，失败返回false
     */
    public function forceRemove(string $gid)
    {
        $response = json_decode($this->aria2->forceRemove($gid), true);
        $this->handleError($response);
        return $response['result'] ?: false;
    }

    /**
     * 暂停指定的下载项
     * @param  string $gid 下载项的GID
     * @return mixed 成功暂停的下载项的GID，失败返回false
     */
    public function pause(string $gid)
    {
        $response = json_decode($this->aria2->pause($gid), true);
        $this->handleError($response);
        return $response['result'] ?: false;
    }

    /**
     * 暂停所有下载
     * @return bool 成功返回true，否则返回false
     */
    public function pauseAll()
    {
        $response = json_decode($this->aria2->pauseAll(), true);
        $this->handleError($response);
        return $response['result'] === 'OK';
    }

    /**
     * 强制暂停指定的下载项
     * @param  string $gid 下载项的GID
     * @return mixed 成功暂停的下载项的GID，失败返回false
     */
    public function forcePause(string $gid)
    {
        $response = json_decode($this->aria2->forcePause($gid), true);
        $this->handleError($response);
        return $response['result'] ?: false;
    }

    /**
     * 强制暂停所有下载
     * @return bool 成功返回true，否则返回false
     */
    public function forcePauseAll()
    {
        $response = json_decode($this->aria2->forcePauseAll(), true);
        $this->handleError($response);
        return $response['result'] === 'OK';
    }

    /**
     * 查询指定下载的状态，当指定keys时，仅查询指定的状态，否则查询所有状态
     * @param  string $gid  要查询的下载项的gid
     * @param  array  $keys 要查询的项，具体请看 https://aria2.github.io/manual/en/html/aria2c.html#aria2.tellStatus
     * @return array 返回查询结果字典
     */
    public function tellStatus(string $gid, array $keys = [])
    {
        $params = [
            $gid,
        ];
        if ($keys) {
            $params[] = $keys;
        }
        $response = json_decode($this->aria2->tellStatus(...$params), true);
        $this->handleError($response);
        return $response['result'] ?? [];
    }

    /**
     * 获取指定下载项中的链接
     * @param  string $gid 下载项的GID
     * @return array 链接数组，具体结构请参考 https://aria2.github.io/manual/en/html/aria2c.html#aria2.getUris
     */
    public function getUris(string $gid)
    {
        $response = json_decode($this->aria2->getUris($gid), true);
        $this->handleError($response);
        return $response['result'] ?? [];
    }

    /**
     * 获取指定下载项中的文件
     * @param  string $gid 下载项的GID
     * @return array 文件数组，具体结构请参考 https://aria2.github.io/manual/en/html/aria2c.html#aria2.getFiles
     */
    public function getFiles(string $gid)
    {
        $response = json_decode($this->aria2->getFiles($gid), true);
        $this->handleError($response);
        return $response['result'] ?? [];
    }

    /**
     * 获取指定种子下载项中的种子信息
     * @param  string $gid 下载项的GID
     * @return array 种子信息数组，具体结构请参考 https://aria2.github.io/manual/en/html/aria2c.html#aria2.getPeers
     */
    public function getPeers(string $gid)
    {
        $response = json_decode($this->aria2->getPeers($gid), true);
        $this->handleError($response);
        return $response['result'] ?? [];
    }

    /**
     * 获取指定下载项的服务器信息
     * @param  string $gid 下载项的GID
     * @return array 服务器信息数组，具体请参考 https://aria2.github.io/manual/en/html/aria2c.html#aria2.getServers
     */
    public function getServers(string $gid)
    {
        $response = json_decode($this->aria2->getServers($gid), true);
        $this->handleError($response);
        return $response['result'] ?? [];
    }

    /**
     * 获取正在下载的下载项信息
     * @param  array  $keys 要获取的下载项信息keys，同tellStatus
     * @return array 下载项信息，同tellStatus
     */
    public function tellActive(array $keys = [])
    {
        $params = [];
        if ($keys) {
            $params[] = $keys;
        }
        $response = json_decode($this->aria2->tellActive(...$params), true);
        $this->handleError($response);
        return $response['result'] ?? [];
    }

    /**
     * 获取等待中的下载项信息（包括暂停）
     * @param  int    $offset 从队列的指定位置取起，从1开始，支持负数，指定负数时表示从倒数第N个取起，注意此时的返回列表是倒序的！
     * @param  int    $num    获取指定个数的下载项
     * @param  array  $keys   要获取的下载项信息keys，同tellStatus
     * @return array 下载项信息，同tellStatus
     */
    public function tellWaiting(int $offset, int $num, array $keys = [])
    {
        $params = [
            $offset,
            $num,
        ];
        if ($keys) {
            $params[] = $keys;
        }
        $response = json_decode($this->aria2->tellActive(...$params), true);
        $this->handleError($response);
        return $response['result'] ?? [];
    }

    /**
     * 获取暂停中的下载项信息（不包括未开始过下载等待中的）
     * @param  int    $offset 同tellWaiting
     * @param  int    $num    同tellWaiting
     * @param  array  $keys   同tellStatus
     * @return array 下载项信息，同tellStatus
     */
    public function tellStopped(int $offset, int $num, array $keys = [])
    {
        $params = [
            $offset,
            $num,
        ];
        if ($keys) {
            $params[] = $keys;
        }
        $response = json_decode($this->aria2->tellStopped(...$params), true);
        $this->handleError($response);
        return $response['result'] ?? [];
    }

    /**
     * 移动指定下载项的位置
     * @param  string $gid 下载项的GID
     * @param  int    $pos 要移动的位置量，与$how参数关联
     * @param  string $how 可用位置关系 “POS_SET”、“POS_CUR”、“POS_END”，具体请参考 https://aria2.github.io/manual/en/html/aria2c.html#aria2.changePosition
     * @return int 返回移动后的队列位置（从0开始），当指定错误的$how位置关系或发生错误时返回false
     */
    public function changePosition(string $gid, int $pos, string $how)
    {
        if ($how !== self::POS_SET && $how !== self::POS_CUR && $how !== self::POS_END) {
            trigger_error('unrecognized position string.');
            return false;
        }
        $response = json_decode($this->aria2->tellStopped($gid, $pos, $how), true);
        $this->handleError($response);
        return $response['result'] ?? false;
    }

    /**
     * 移除或添加指定下载项中的文件的下载连接
     * @param  string   $gid       下载项的GID
     * @param  int      $fileIndex 文件在下载项中的位置，从1开始
     * @param  array    $delUris   要删除的url数组，注意：当文件存在N个相同的下载链接时，如果你想把他们都移除掉，你就要指定N次该链接
     * @param  array    $addUris   要添加的url数组
     * @param  int|null $position  添加url的位置，从0开始，当未指定时将插入到最后。当同时指定了删除数组以及添加数组时，先执行删除操作，再执行添加操作，所以position应当是删除过后的位置，而不是删除前的。
     * @return array 返回一个存在两个元素的数组，第一个元素为删除的链接数，第二个元素为添加的链接数
     */
    public function changeUri(string $gid, int $fileIndex, array $delUris = [], array $addUris = [], int $position = null)
    {
        $params = [
            $gid,
            $fileIndex,
            $delUris,
            $addUris,
        ];
        if ($position !== null) {
            $params[] = $position;
        }
        $response             = json_decode($this->aria2->changeUri(...$params), true);
        $counts               = $response['result'] ?? [];
        @list($deled, $added) = $counts;
        return [$deled ?? 0, $added ?? 0];
    }

    /**
     * 获取指定下载项的配置信息（不包括没默认配置以及没手动配置的）
     * @param  string $gid 下载项的GID
     * @return array 配置信息字典
     */
    public function getOption(string $gid)
    {
        $response = json_decode($this->aria2->getOption($gid), true);
        $this->handleError($response);
        return $response['result'] ?? [];
    }

    /**
     * 更改指定下载项的配置
     * @param  string $gid     下载项的GID
     * @param  array  $options 要更改的配置项，有部分配置无法修改，具体请参考 https://aria2.github.io/manual/en/html/aria2c.html#aria2.changeOption
     * @return bool
     */
    public function changeOption(string $gid, array $options)
    {
        $response = json_decode($this->aria2->changeOption($gid, $options), true);
        $this->handleError($response);
        return $response['result'] === 'OK';
    }

    /**
     * 获取所有全局配置
     * @return array 返回所有已配置过的全局配置
     */
    public function getGlobalOption()
    {
        $response = json_decode($this->aria2->getGlobalOption(), true);
        $this->handleError($response);
        return $response['result'] ?? [];
    }

    /**
     * 更改全局的配置项
     * @param  array  $options 要更改的配置项，有部分配置无法修改，具体请参考 https://aria2.github.io/manual/en/html/aria2c.html#aria2.changeGlobalOption
     * @return bool
     */
    public function changeGlobalOption(array $options)
    {
        $response = json_decode($this->aria2->changeGlobalOption($options), true);
        $this->handleError($response);
        return $response['result'] === 'OK';
    }

    /**
     * 获取全局的状态
     * @return array 全局状态字典，具体请参考 https://aria2.github.io/manual/en/html/aria2c.html#aria2.getGlobalStat
     */
    public function getGlobalStat()
    {
        $response = json_decode($this->aria2->getGlobalStat(), true);
        $this->handleError($response);
        return $response['result'] ?? [];
    }

    /**
     * 清除已完成/出错/已移除的下载项
     * @return bool
     */
    public function purgeDownloadResult()
    {
        $response = json_decode($this->aria2->purgeDownloadResult(), true);
        $this->handleError($response);
        return $response['result'] === 'OK';
    }

    /**
     * 移除指定已完成/出错/已移除的下载项
     * @param  string $gid 下载项的GID
     * @return bool
     */
    public function removeDownloadResult(string $gid)
    {
        $response = json_decode($this->aria2->removeDownloadResult($gid), true);
        $this->handleError($response);
        return $response['result'] === 'OK';
    }

    /**
     * 获取aria2的版本号以及启用的特性
     * @return array 版本以及特性字典，{"version":"x.y.z","enabledFeatures":["feature1","feature2"]}
     */
    public function getVersion()
    {
        $response = json_decode($this->aria2->getVersion(), true);
        $this->handleError($response);
        return $response['result'] ?? [];
    }

    /**
     * 获取session信息
     * @return array 具体请参考 https://aria2.github.io/manual/en/html/aria2c.html#aria2.getSessionInfo
     */
    public function getSessionInfo()
    {
        $response = json_decode($this->aria2->getSessionInfo(), true);
        $this->handleError($response);
        return $response['result'] ?? [];
    }

    /**
     * 关闭aria2
     * @return bool
     */
    public function shutdown()
    {
        $response = json_decode($this->aria2->shutdown(), true);
        $this->handleError($response);
        return $response['result'] === 'OK';
    }

    /**
     * 强制关闭aria2
     * @return bool
     */
    public function forceShutdown()
    {
        $response = json_decode($this->aria2->forceShutdown(), true);
        $this->handleError($response);
        return $response['result'] === 'OK';
    }

    /**
     * 保存当前session信息，具体请参考 https://aria2.github.io/manual/en/html/aria2c.html#aria2.saveSession
     * @return bool
     */
    public function saveSession()
    {
        $response = json_decode($this->aria2->saveSession(), true);
        $this->handleError($response);
        return $response['result'] === 'OK';
    }

    /**
     * 批量调用，不支持调用system.*方法
     * @param  array  $methods [{"method":"xxx","args":["xxx"]},...]
     * @return array 返回每个方法返回数据的数组
     */
    public function multicall(array $methods)
    {
        $formated = [];
        foreach ($methods as $method) {
            $formated[] = [
                'methodName' => "aria2.{$method['method']}",
                'params'     => $method['args'] ?? [],
            ];
        }
        $response = json_decode($this->aria2->system_call('multicall', $formated), true);
        $this->handleError($response);
        return $response['result'] ?? [];
    }

    /**
     * 列举aria2 RPC 服务支持的方法名（包含“aria2.”前缀）
     * @return array 方法列表
     */
    public function listMethods()
    {
        $response = json_decode($this->aria2->system_call('listMethods'), true);
        $this->handleError($response);
        return $response['result'] ?? [];
    }

    /**
     * 列举aria2 RPC 服务支持的通知回调（包含“aria2.”前缀）
     * @return array 通知列表
     */
    public function listNotifications()
    {
        $response = json_decode($this->aria2->system_call('listNotifications'), true);
        $this->handleError($response);
        return $response['result'] ?? [];
    }

    private function handleError($response)
    {
        if (!$response || !is_array($response)) {
            $this->last_error_code    = -999;
            $this->last_error_message = 'Unknown Error';
        } else {
            if (isset($response['error'])) {
                $this->last_error_code    = (int) $response['error'];
                $this->last_error_message = (string) ($response['message'] ?? '');
            } else {
                $this->last_error_code    = null;
                $this->last_error_message = null;
            }
        }
    }

    /**
     * 最近的一个JSON-RPC请求是否有错误发生
     * @return bool
     */
    public function hasError()
    {
        return $this->last_error_code !== null;
    }

    /**
     * 获取最近一次请求的错误码，不存在返回null
     * @return mixed
     */
    public function lastErrorCode()
    {
        return $this->last_error_code;
    }

    /**
     * 获取最近一次请求的错误信息，不存在返回null
     * @return mixed
     */
    public function lastErrorMessage()
    {
        return $this->last_error_message;
    }
}
