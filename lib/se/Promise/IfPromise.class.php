<?php

namespace se\Promise;

class IfPromise extends AbstractPromise implements IfPromiseInterface
{

	/**
	 * @var SuperClosure
	 */
	protected $if = null;

	/**
	 * @var SuperClosure
	 */
	protected $block = null;

	/**
	 * @var SuperClosure
	 */
	protected $elseif = null;

	/**
	 * @var SuperClosure
	 */
	protected $else = null;

	public function __construct($parent, $if, $block, $fail = null)
	{
		$this->parent		= $parent;
		$this->if			= $if;
		$this->block 		= $block;
		$this->fail			= $fail ? self::makeSuperClosure($fail) : $fail;
	}

	public function __invoke()
	{
		if($this->parent && $this->parent->state() == AbstractPromise::__STATE_NOT_EXECUTING__)
		{
			return call_user_func_array($this->parent, func_get_args());
		}
		try{
			$condition = call_user_func_array($this->if, $args = func_get_args());
			
			if($condition)
			{
				$result = call_user_func_array($this->block, $args);
			}
			elseif($this->elseif)
			{
				$result = call_user_func_array($this->elseif, $args);
			}
			elseif($this->else)
			{
				$result = call_user_func_array($this->else, $args);
			}
			
			return $result;
		}catch(\Exception $e)
		{
			if($this->fail)
			{
				$result = call_user_func_array($this->fail, array($e, $args));
				return $this;
			}
			else
			{
				throw $e;
			}
		}

	}

	public function _elseif($condition, $block, $class = null)
	{

	}

	public function _else($block)
	{

	}
}