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
    
        public function testExample() {
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

### 创建测试用例Action
ThinkPHPUnit需要独立的Action文件，继承ThinkPHPUnitAction类。

类似这样：
```
class TestAction extends ThinkPHPUnitAction {}
```

类中的方法，只有以 **test** 开头的方法，才会被认为是包含测试用例的可执行的测试方法。
通过URL访问查看测试用例的执行结果，无需逐一访问每个方法， **ThinkPHPUnitAction::index()** 能够自动地逐一运行每个以*test*开头的方法并返回结果。


### 使用哪种断言错误记录方式

定义记录错误的方式使用下面代码：
    
    class TestAction extends ThinkPHPUnitAction {
    
        protected $_message_render = self::MESSAGE_RENDER_ECHO;
    }


ThinkPHPUnit支持4种断言错误输出常量：
* ThinkPHPUnitAction::MESSAGE_RENDER_EXCEPTION：断言错误时以使用抛出异常（默认）
* ThinkPHPUnitAction::MESSAGE_RENDER_ERROR_LOG：断言错误时以PHP日志方式记录错误
* ThinkPHPUnitAction::MESSAGE_RENDER_VARDUMP：断言错误时以使用var_dump()函数输出
* ThinkPHPUnitAction::MESSAGE_RENDER_ECHO：断言错误时以使用文本输出方式（推荐）

在测试环境中，推荐使用**ThinkPHPUnitAction::MESSAGE_RENDER_ECHO** 方式输出错误。
如果是生产环境，那么建议：
* 如果有安全屏蔽测试用的Action，那么仍然可以采用 **ThinkPHPUnitAction::MESSAGE_RENDER_ECHO** 方式；
* 如果没有，那么建议采用**ThinkPHPUnitAction::MESSAGE_RENDER_ERROR_LOG** 。