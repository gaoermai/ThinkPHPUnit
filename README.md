ThinkPHPUnit
============

在[ThinkPHP](http://www.thinkphp.cn/)开源框架上，使用[PHPUnit](https://github.com/sebastianbergmann/phpunit/)作为测试框架。

##安装和样例代码

###安装方法
```
shell> cd /path/to/thinkphp/app/Lib/ORG/
shell> git clone https://github.com/gaoermai/ThinkPHPUnit.git
```

###创建TestAction
在 **/path/to/thinkphp/app/Lib/Action** 下（如有分组请自行决定放在哪个分组下，或者可以创建一个专门的test分组），创建 **TestAction.class.php** ，代码如下：
    
    import('@.ORG.ThinkPHPUnit.ThinkPHPUnitAction');

    class TestAction extends ThinkPHPUnitAction {
    
        protected $_message_render = self::MESSAGE_RENDER_ECHO;
    
        protected function _testExample() {
            $this->assertLessThan(1, 2, '可以使用中文作为错误提示');
            $this->assertLessThan(2, 1);
            $this->assertContainsOnly('string', array('1', '2', 3));
        }
    }

访问 http://example.com/thinkphpunit/test （注意：无需把URL指向特定的方法），如果一切顺利的话，就可以看到下面提示：

    [Failure]: 可以使用中文作为错误提示
    Failed asserting that 2 is less than 1.
        CLASS: TestAction
        METHOD: testExample
        LINE: 10

    [Failure]: Failed asserting that Array (
        0 => '1'
        1 => '2'
        2 => 3
    ) contains only values of type "string".
        CLASS: TestAction
        METHOD: testExample
        LINE: 12

##使用帮助

###创建测试用例Action
ThinkPHPUnit需要独立的Action文件，继承ThinkPHPUnitAction类。

类似这样：
```
class TestAction extends ThinkPHPUnitAction {}
```

类中的方法，只有类似 **public testFuncName()** 或者 **protected _testFuncName()** 这样命名的，才会被认为是包含测试用例的测试方法。 **ThinkPHPUnitAction::index()** 能够自动地逐一执行这些方法并返回结果。


###使用哪种断言错误记录方式

定义记录错误的方式使用下面代码：
    
    class TestAction extends ThinkPHPUnitAction {
    
        protected $_message_render = self::MESSAGE_RENDER_ECHO;
    }


ThinkPHPUnit支持4种断言错误输出常量：
* ThinkPHPUnitAction::MESSAGE_RENDER_EXCEPTION：断言错误时以使用抛出异常（默认）
* ThinkPHPUnitAction::MESSAGE_RENDER_ERROR_LOG：断言错误时以PHP日志方式记录错误
* ThinkPHPUnitAction::MESSAGE_RENDER_VARDUMP：断言错误时以使用var_dump()函数输出
* ThinkPHPUnitAction::MESSAGE_RENDER_ECHO：断言错误时以使用文本输出方式（推荐）

在测试环境中，推荐使用 **ThinkPHPUnitAction::MESSAGE_RENDER_ECHO** 方式输出错误。

如果是生产环境，那么建议：
* 如果有安全屏蔽测试用的Action，那么仍然可以采用 **ThinkPHPUnitAction::MESSAGE_RENDER_ECHO** 方式；
* 如果没有，那么建议采用 **ThinkPHPUnitAction::MESSAGE_RENDER_ERROR_LOG** 。

###如何测试异常？

在[PHPUnit](http://phpunit.de/manual/current/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.exceptions)的官方文档中，有描述通过类似下面的注释代码来测试异常：
```
class ExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testException()
    {
    }
}
```

但是，在Web环境中，是没有办法通过注释标注的方式来测试异常的。从ThinkPHPUnit v0.2版本开始，提供了新的方法来对异常进行输出：
```
	protected function _testException() {
		try {
			throw new InvalidArgumentException();
		}catch (InvalidArgumentException) {
		    
		}catch (Exception $e) {
			$this->render_exception($e);
		}
	}
```
使用这种方法，可以输出预期异常并输出。

###生产环境部署须知

在生产环境中，为了限制普通用户访问测试用例，可以采用以下几种安全措施。

####设置HTTP Authentication

HTTP Authentication是一种HTTP基础的用户名密码验证方式。从ThinkPHPUnit v0.2版本开始，内置了这种验证方式。配置方法：
    
    class TestAction extends ThinkPHPUnitAction {
    
        protected $_http_auth_username = 'YOUR USERNAME HERE';
        protected $_http_auth_password = 'YOUR PASSWORD HERE';
    }

为安全起见，建议将用户名和密码都设置成超过16位的大小写数字混合的字符串形式。

默认的，当常量 **APP_DEBUG** 为true时，HTTP验证不开启。

####采用随机的Action名

不采用常见的如 **TestAction** 这样的名称，能够有效的避免非法用户猜测测试用例的网址。

####在Web服务器上限定IP等措施

更加安全的做法，可以在Web服务（如Apache、Nginx）上，对测试用例所在的网址进行IP过滤等。这些方法属于Web服务的讨论范围，请阅读相关文档。