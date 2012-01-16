<?php
namespace se\Promise;

use se\Promise\Exceptions\ElseIfPromiseConditionException;

abstract class AbstractElseIfPromise extends AbstractPromise implements ElseIfPromiseInterface
{
	protected $condition;

	protected $block;

	protected $elseif;

	protected $else;

	public function __construct(PromiseInterface $parent, $condition, $block)
	{
		$this->parent			= $parent;
		$this->condition		= self::makeSuperClosure($condition);
		$this->block			= self::makeSuperClosure($block);
	}

	public function __invoke()
	{
		if($this->parent && $this->parent->state() == AbstractPromise::__STATE_NOT_EXECUTING__)
		{
			$this->_state = self::__STATE_EXECUTING_PARENT__;
			return call_user_func_array($this->parent, func_get_args());
		}
		$this->_state = self::__STATE_EXECUTING__;

		$condition = call_user_func_array($this->condition, $args = func_get_args());

		if($condition)
		{
			$result = call_user_func_array($this->block, $args);
		}
		elseif($this->elseif)
		{
			try{
				$result = call_user_func_array($this->elseif, $args);
				$elseIfConditionPassed = true;
			}catch(ElseIfPromiseConditionException $e)
			{
				$elseIfConditionPassed = false;
			}
		}
		
		if($this->else && !$condition && (!$this->elseif || !$elseIfConditionPassed))
		{
			$result = call_user_func_array($this->else, $args);
		}
		elseif(!$condition && !$elseIfConditionPassed)
		{
			throw new ElseIfPromiseConditionException();
		}

		$this->_state = self::__STATE_NOT_EXECUTING__;
		return $result;
	}

	public function _elseif($condition, $block, $class = null)
	{
		$class			= $this->parent->getElseIfPromiseClass($class);
		$this->elseif	= new $class($this, $condition, $block);
		return $this->elseif;
	}

	public function _else($block, $class = null)
	{
		$class			= $this->parent->getElsePromiseClass($class);
		$this->else		= new $class($this, $block);
		return $this->else;
	}
}