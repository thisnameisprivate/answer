<?php

namespace Workerman\Protocols;
use \Workerman\Connection\ConnectionInterface;

/**
 * Protocol interface
 * @author kexin <kexinfrdy@gmail.com>
 */

interface Protocollnterface {
    /**
     * 用于在接收到的recv_buffer中分包
     *
     * 如果可以在$recv_buffer中得到请求包的长度则返回整个包的长度
     * 否则返回0，表示需要更多的数据才能得到当前请求包的长度
     * 如果返回false或者负数，则代表错误的请求，则连接会断开
     *
     * @param ConnectionInterface $connection
     * @param string $recv_buffer
     * @return int|false
     */
    public static function input ($recv_buffer, ConnectionInterface $connection);
    /**
     * 用于请求解包
     *
     * input返回值大于0，并且WorkerMan收到了足够的数据，则自动调用decode
     * 然后触发onMessage回调，并将decode解码后的数据传递给onMessage回调的第二个参数
     * 也就是说当收到完整的客户端请求时，会自动调用decode解码，无需业务代码中手动调用
     * @param ConnectionInterface $connection
     * @param string $recv_buffer
     */
    public static function decode ($recv_buffer, ConnectionInterface $connection);
    /**
     * 用于请求打包
     *
     * 当需要向客户端发送数据即调用$connection->send($data);时
     * 会自动把$data用encode打包一次，变成符合协议的数据格式，然后再发送给客户端
     * 也就是说发送给客户端的数据会自动encode打包，无需业务代码中手动调用
     * @param ConnectionInterface $connection
     * @param mixed $data
     */
    public static function encode ($data, ConnectionInterface $connection);
}

$http_worker = new Worker("http://0.0.0.0:2345");
$http_worker->count = 4;
$http_worker->onMessage = function ($connection, $data) {
    $connection->send("Worker it work!");
};
worker::All();
function handle_connection ($connection) {
    global $text_worker, $global_uid;
    $connection->uid = ++$global_uid;
}
function handle_message ($connection, $data) {
    global $text_worker;
    foreach ($text_worker->connection as $conn) {
        $conn->send("user[{$connection->uid}] said: $data");
    }
}
function handle_close ($connection) {
    global $text_worker;
    foreach ($text_worker->connections as $conn) {
        $conn->send("user[{$connection->uid}] logout");
    }
}
$text_worker = new Worker("text://0.0.0.0:2347");
$text_worker->count = 1;
$text_worker->onConnect = "handle_connection";
$text_worker->onMessage = "handle_message";
$text_worker->onClose   = "handle_close";
Worker::runAll();

class JsonNL {
    public static function input ($buffer) {
        $pos = strpos($buffer, "\n");
        if ($pos === false) {
            return 0;
        }
        return $pos + 1;
    }
    public static function encode ($buffer) {
        return json_encode($buffer) . "\n";
    }
    public static function decode ($buffer) {
        return json_decode(trim($buffer), true);
    }
}
$json_worker = new Worker("JsonNL://0.0.0.0:1234");
$json_worker->onMessage = function ($connection, $data) {
    echo $data;
    $connection->send(['code' => 0, 'msg' => 'ok']);
};
Worker::runAll();
class XmlProtocol {
    public static function input ($recv_buffer) {
        if (strlen($recv_buffer) < 10) {
            return 0;
        }
        $total_len = base_canvert(substr($recv_buffer, 0, 10), 10, 10);
        return $total_len;
    }
    public static function decode ($recv_buffer) {
        $body = substr($recv_buffer, 10);
        return simplexml_load_string($body);
    }
    public static function encode ($xml_string) {
        $total_length = strlen($xml_string) + 10;
        $total_length_str = str_pad($total_length, 10, '0', STR_PAD_LEFT);
        return $total_length_str . $xml_string;
    }
}
class JsonInt {
    public static function input ($recv_buffer) {
        if (strlen($recv_buffer) < 4) {
            return 0;
        }
        $unpack_data = unpack('Ntotal_length', $recv_buffer);
        return $unpack_data['total_length'];
    }
    public static function decode ($recv_buffer) {
        $body_json_str = substr($recv_buffer, 4);
        return json_decode($body_json_str, true);
    }
    public static function encode ($data) {
        $body_json_str = json_encode($data);
        $total_length = 4 + strlen($body_json_str);
        return pack('N', $total_length) . $body_json_str;
    }
}
class BinaryTransfer {
    const PACKAGE_HEAD_LEN = 5;
    public static function input ($recv_buffer) {
        if (strlen($recv_buffer) < self::PACKAGE_HEAD_LEN) {
            return 0;
        }
        $package_data = unpack("Ntotal_len/Cname_len", $recv_buffer);
        return $package_data['total_len'];
    }
    public static function decode ($recv_buffer) {
        $package_data = unpack("Ntotal_len/Cname_len", $recv_buffer);
        $name_len = $package_data['name_len'];
        $file_name = substr($recv_buffer, self::PACKAGE_HEAD_LEN, $name_len);
        $file_data = substr($recv_buffer, self::PACKAGE_HEAD_LEN + $name_len);
        return ['file_name' => $file_name, 'file_data' => $file_data];
    }
    public static function encode ($data) {
        return $data;
    }
}
$worker->onMessage = function ($connection, $data) {
    $save_path = '/tmp/' . $data['file_name'];
    file_put_contents($save_path, $data['file_data']);
    $connection->send("upload success . save path $save_path");
};
Worker::runAll();
$address = "127.0.0.1:8333";
if (!isset($argv[1])) {
    exit("use php client.php \$file_path\n");
}
$file_to_transfer = trim([$argv[1]]);
if (!is_file($file_to_transfer)) {
    exit("$file_to_transfer not exist\n");
}
$client = stream_socket_client($address, $errno, $errmsg);
if (!$client) {
    exit("$errmsg\n");
}
stream_set_blocking($client, 1);
$file_name = basename($file_to_transfer);
$name_len = strlen($file_name);
$file_data = file_get_contents($file_to_transfer);
$PACKAGE_HEAD_LEN = 5;
$package = pack('NC', $PACKAGE_HEAD_LEN + strlen($file_name) + strlen($file_data), $name_len) . $file_name . $file_data;
fwrite($client, $package);
echo fread($client, 8192), "\n";
// 客户测试用例
class TexteTransfer {
    public static function input ($recv_buffer) {
        $recv_len = strlen($recv_buffer);
        if ($recv_buffer[$recv_len - 1] !== "\n") {
            return 0;
        }
        return strlen($recv_buffer);
    }
    public static function decode ($recv_buffer) {
        $package_data = json_decode(trim($recv_buffer), true);
        $file_name = $package_data['file_name'];
        $file_data = $package_data['file_data'];
        $file_data = base64_decode($file_data);
        return ["file_name" => $file_name, "file_data" => $file_data];
    }
    public static function encode ($data) {
        return $data;
    }
}
$worker = new Worker("TextTransfer://0.0.0.0:8333");
$worker->onMessage = function ($connection, $data) {
    $save_path = '/tmp/' . $data['file_name'];
    file_put_contents($save_path, $data['file_name']);
    $connection->send("upload success . save path $save_path");
};
Worker::runAll();

$address = '127.0.0.1: 8333';
if (!isset($argv[1])) {
    exit("use php client.php \$file_path\n");
}
$file_to_transfer = trim($argv[1]);
if (!is_file($file_to_transfer)) {
    exit("$file_to_transfer not exist \n");
}
$client = stream_socket_client($address, $errno, $errmsg);
if (!$client) {
    exit("$errmsg\n");
}
stream_set_blocking($client, 1);
$file_name = basename($file_to_transfer);
$file_data = file_get_contents($file_to_transfer);
$file_data = base64_encode($file_data);
$package_data = ["file_name" => $file_name, "file_data" => $file_data];
$package = json_encode($package_data) . "\n";
fwrite($client, $package);
echo fread($client, 8192), "\n";
class ClassLoader {
    private $prefixLengthPsr4 = [];
    private $prefixDirsPsr4 = [];
    private $fallbackDirsPsr4 = [];
    private $prefixsPsr0 = [];
    private $fallbackDirsPsrr0 = [];
    private $useIncludePath = [];
    private $classMap = [];
    private $classMapAuthoritative = [];
    private $missingClasses = [];
    private $apcuPrefix = [];

    public function getPrefixes () {
        if (!empty($this->prefixesPsr0)) {
            return call_user_func_array('array_merge', $this->prefixesPsr0);
        }
        return [];
    }
    public function getPrefixesPsr4 () {
        return $this->prefixDirsPsr4;
    }
    public function getFallbackDirs () {
        return $this->fallbackDirsPsr0;
    }
    public function getFallBackDirsPsr4 () {
        return $this->fallbackDirsPsr4;
    }
    public function getClassMap () {
        return $this->classMap;
    }
    public function addClassMap (array $classMap) {
        if ($this->classMap) {
            $this->classMap = array_merge($this->classMap, $classMap);
        } else {
            $this->classMap = $classMap;
        }
    }
    public function add ($prefix, $paths, $prepend = false) {
        if (!$prefix) {
            if ($prepend) {
                $this->fallbackDirsPsr0 = array_merge($this->fallbackDirsPsr0, (array) $paths);
            }
            return;
        }
        $first = $prefix[0];
        if (!isset($this->profixesPsr0[$first][$prefix])) {
            $this->prefixesPsr0[$first][$prefix] = (array) $paths;
            return;
        }
        if ($prepend) {
            $this->prefixesPsr0[$first][$prefix] = array_merge((array) $paths, $this->prefixesPsr0[$first][$prefix]);
        } else {
            $this->prefixesPsr0[$first][$prefix] = array_merge((array) $this->prefixesPsr0[$first][$prefix], (array) $paths);
        }
    }

}
// PHP实现Redis分布锁
$redis = new \Redis('127.0.0.1', 6379);
$lockKey = 'query_chace_lock';
$cacheKey = 'query_cahce';
$result = $redis->get($cacheKey);
if ($result) {
    return $result;
} else {
    if ($redis->setNx($lockKey)) {
        throw new \Exception("service not found file;");
    } else {
        $mysqlResult = [];
        $redis->set($cacheKey, json_encode($mysqlResult), 3600);
        $redis->delete($lockKey);
        return $mysqlResult;
    }
}
// Redis分布式锁Instance.
class RedisMuteLock {
    public static function getRedis () {
        return YCache::getRedisClient();
    }
    public static function lock ($lock, $timeout = 0, $lockSecond = 20, $sleep = 100000) {
        if (strlen($key) === 0) {
            YCore::exception(500, 'cache KEY not set!');
        }
        $start = self::getMicroTime();
        $redis = self::getRedis();
        do {
            $acquired = $redis->set("Lock:{$key}", 1, ['NX', 'EX' => $lockSecond]);
            if ($acquired) {
                break;
            }
            if ($timeout === 0) {
                break;
            }
            usleep($sleep);
        } while (!is_numeric($timeout) || (self::getMicroTime()) < ($start + ($timeout * 1000000)));
        return $acquired ? true : false;
    }
    public static function release ($key) {
        if (strlen($key) === 0) {
            YCore::exception(500, 'Cache KEY not set!');
        }
        $redis = self::getRedis();
        $redis->del("Lock:{$key}");
    }
    protected static function getMicroTime () {
        return bcmul(microtime(true), 1000000);
    }
}
// 无限替换文件内容和文件名并清除以前的文件
class searchReplace {
    private $search = null;
    private $matchStr = null;
    public function __construct ($search, $matchStr) {
        if ($search == null || $matchStr == null) return false;
        $this->search = $search;
        $this->matchStr = $matchStr;
    }
    private function modifyFile ($filename) {
        $fileHandler = fopen($filename, 'r+');
        $modify = '';
        while (!feof($fileHandler)) {
            $source = fgets($fileHandler);
            $modify .= str_replace($this->search, $this->matchStr, $source);
        }
        if (!@unlink($filename)) {
            echo "delete file " . $filename . " success<br>";
        }
        $newFileHandler = fopen($filename, 'w');
        if (!feof($newFileHandler)) {
            fwrite($newFileHandler, $modify);
        }
        if (file_exists($filename)) {
            echo "create filel " . $filename . " success<br>";
        }
    }
    public function sourceDir ($dir) {
        $files = array();
        if ($dir != ".idea" && $dir != 'searchReplaceStr') {
            if (@$handler = opendir($dir)) {
                while (($file = readdir($handler)) !== false) {
                    if ($file != ".." && $file != "." && $file != "searchReplaceStr.php" && $file != 'index.html') {
                        if (is_dir($dir . "/" . $file)) {
                            $files[$file] = $this->sourceDir($dir . "/" . $file);
                        } else {
                            $this->modifyFile($dir . "/" . $file);
                        }
                    }
                }
            }
        }
    }
}
if (!is_null($_POST)) {
    $searchReplaceStr = new searchReplaceStr($_POST['target'], $_POST['strReplace']);
    print_r($searchReplaceStr->sourceDir('../'));
} else {
    echo "<script>
alert ('please input value'),
location.href = './index.html';
</script>";
}
function callback () {
    echo "execute no parameters callback. <br/>";
}
function main ($callback) {
    echo "execute main start.<br/>";
    $callback();
    echo "execute main end.<br/>";
}
main('callback');
// 全局回调函数
function callback2 ($a, $b) {
    echo "$a<====>$b.<br/>";
}
$func = "callback";
call_user_func($func, 1, 2);
call_user_func_array($func, [1, 2]);
// 类方法及静态方法回调
class Test {
    function callback ($a, $b) {
        echo "callback $a<====>$b<br/>";
    }
    public static function staticCallback ($a, $b) {
        echo "staticCallback $a<====>$b.<br/>";
    }
}
$test = new Test();
call_user_func([$test, 'callback'], 1, 2);
call_user_func_array([$test, 'callback'], [1, 2]);
$func = 'callback';
$test->func(7, 9);
call_user_func(['Test', 'staticCallback'], 4, 6);
call_user_func_array(['Test', 'staticCallback'], [4, 6]);
call_user_func_array("Test::staticCallback", [4, 6]);

// 匿名函数
$closureFunc = function ($str) {
    echo $str;
};
$closureFunc("Hello, World.");
$closureFunc = function ($name) {
    $sex = "man";
    $func = function ($age) use ($name, $sex) {
        echo "$name--$sex--$age<br/>";
    };
    $func(23);
};
$func = $closureFunc('lvfk');
// 闭包实现
function printStr () {
    $closure = function ($str) {
        echo $str;
    };
    $closure("printStr");
}
printStr();
// 闭包实现2
