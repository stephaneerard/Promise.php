<?php
namespace se\Promise;

abstract class AbstractElsePromise extends AbstractPromise implements ElsePromiseInterface
{
	public function __construct(PromiseInterface $parent, $block)
	{
		$this->parent		= $parent;
		$this->block		= self::makeSuperClosure($block);
	}
	
	public function __invoke()
	{
		if($this->parent && $this->parent->state() == AbstractPromise::__STATE_NOT_EXECUTING__)
		{
			$this->_state = self::__STATE_EXECUTING_PARENT__;
			return call_user_func_array($this->parent, func_get_args());
		}
		$this->_state = self::__STATE_EXECUTING__;
		$result = call_user_func_array($this->block, func_get_args());
		$this->_state = self::__STATE_NOT_EXECUTING__;
		
		return $result;
	}
}