<?php

import('@.ORG.ThinkPHPUnit.ThinkPHPUnitAction');

class TestAction extends ThinkPHPUnitAction {
	
	protected $_message_render = self::MESSAGE_RENDER_ECHO;
	
	protected function _testExample() {
		$this->assertLessThan(1, 2, '可以使用中文作为错误提示');
		$this->assertLessThan(2, 1);
		$this->assertContainsOnly('string', array('1', '2', 3));
	}
}
?>