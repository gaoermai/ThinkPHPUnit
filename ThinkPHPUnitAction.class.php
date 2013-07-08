<?php

/**
 * 基于PHPUnit的ThinkPHP Action测试基类
 * 
 * @author Mike.G
 */


$path = dirname(__FILE__).'/PEAR';
set_include_path($path.PATH_SEPARATOR.get_include_path());

require_once 'PHPUnit/Autoload.php';

class ThinkPHP_PHPUnit_Framework_TestCase extends PHPUnit_Framework_TestCase {}

abstract class ThinkPHPUnitAction extends Action
{
    
    private $__phpunit_framework_testcase;
    
    /**
     * 指定断言错误时信息输出的方式
     * @var int
     */
    protected $_message_render = 0;
    
    /**
     * HTTP密码验证用户名
     * 
     * 在生产环境中，强烈建议配置用户名和密码来限制用户访问测试用例。
     * 如果有可能，还可以把包含测试
     * 
     * @var string
     */
    protected $_http_auth_username = 'YOUR USERNAME HERE';
    
    /**
     * 是否启用HTTP密码控制
     * 在生产环境中，强烈建议配置用户名和密码来
     * @var string
     */
    protected $_http_auth_password = 'YOUR PASSWORD HERE';
    
    /**
     * 断言错误时以使用抛出异常
     * @var int
     */
    const MESSAGE_RENDER_EXCEPTION = 0;
    
    /**
     * 断言错误时以PHP日志方式记录错误
     * @var int
     */
    const MESSAGE_RENDER_ERROR_LOG = 1;
    
    /**
     * 断言错误时以使用var_dump()函数输出
     * @var int
     */
    const MESSAGE_RENDER_VARDUMP   = 2;
    
    /**
     * 断言错误时以使用文本输出方式（推荐）
     * @var int
     */
    const MESSAGE_RENDER_ECHO      = 3;
    
    /**
     * 定义断言输出结果的方式
     * 
     * @param string $render
     */
    public function setMessageRender($render=null)
    {
        if ($render == self::MESSAGE_RENDER_ERROR_LOG) {
            $this->_message_render = self::MESSAGE_RENDER_ERROR_LOG;
        }elseif ($render == self::MESSAGE_RENDER_VARDUMP) {
            $this->_message_render = self::MESSAGE_RENDER_VARDUMP;
        }elseif ($render == self::MESSAGE_RENDER_ECHO) {
            $this->_message_render = self::MESSAGE_RENDER_ECHO;
        }else {
            $this->_message_render = self::MESSAGE_RENDER_EXCEPTION;
        }
    }
    
    public function __construct()
    {
        parent::__construct();
        
        $this->__phpunit_framework_testcase = new ThinkPHP_PHPUnit_Framework_TestCase;
    }
    
    private function __render($trace)
    {
        if ($this->_message_render == self::MESSAGE_RENDER_VARDUMP) {
            $this->__render_vardump($trace);
        }elseif ($this->_message_render == self::MESSAGE_RENDER_ERROR_LOG) {
            $this->__render_error_log($trace);
        }elseif ($this->_message_render == self::MESSAGE_RENDER_ECHO) {
            $this->__render_echo($trace);
        }
    }
    
    private function __render_echo($trace)
    {
        $log = <<<DATA
[Failure]: {$trace['message']}
    CLASS: {$trace['class']}
    METHOD: {$trace['method']}
    LINE: {$trace['line']}
\n
DATA;
        echo $log;
    }
    
    private function __render_error_log($trace)
    {
        $log = <<<DATA
{$trace['message']}
\tCLASS: {$trace['class']}
\tMETHOD: {$trace['method']}
\tLINE: {$trace['line']}
DATA;
        error_log($log);
    }
    
    private function __render_vardump($trace)
    {
        var_dump($trace);
    }

    public function __call($method, $args)
    {
        if ($this->_message_render>0 && preg_match('/^assert/', $method)) {
            try {
                call_user_method_array($method, $this->__phpunit_framework_testcase, $args);
            }catch (PHPUnit_Framework_ExpectationFailedException $e) {
                $this_action_class_name = get_class($this);
                
                $action_trace = null;
                $assert_trace = null;
                foreach ($e->getTrace() as $trace) {
                    if ($trace['class'] == $this_action_class_name) {
                        if ($method == $trace['function']) {
                            $assert_trace = $trace;
                        }else {
                            $action_trace = $trace;
                        }
                    }
                }
                
                $test_trace = array(
                    'message' => $e->toString(),
                    'method'  => $action_trace['function'],
                    'class'   => $action_trace['class'],
                    'line'    => $assert_trace['line'],
                );
                
                $this->__render($test_trace);
            }
        }else {
            $this->__phpunit_framework_testcase->$method($args[0]);
        }
    }
    
    protected function _authenticate()
    {
        header('WWW-Authenticate: Basic realm="ThinkPHPUnit Authentication"');
        header('HTTP/1.0 401 Unauthorized');
        echo "You must enter a valid login ID and password to access this resource\n";
        exit;
    }
    
    /**
     * 负责调用所有测试方法并执行
     * 
     */
    public function index()
    {
        header('Content-Type: text/plain; charset=utf-8');
        
        if (!defined('APP_DEBUG') || !APP_DEBUG) {
            if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
                $this->_authenticate();
            }elseif ($_SERVER['PHP_AUTH_USER'] != $this->_http_auth_username || $_SERVER['PHP_AUTH_PW'] != $this->_http_auth_password) {
                $this->_authenticate();
            }
        }
        
        $methods = get_class_methods($this);
        foreach($methods as $method) {
            if (preg_match('/^_?test/', $method) && is_callable(array($this, $method))) {
                call_user_method($method, $this);
            }
        }
    }
}