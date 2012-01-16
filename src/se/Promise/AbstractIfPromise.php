<?php

namespace se\Promise;

abstract class AbstractIfPromise extends AbstractPromise implements IfPromiseInterface
{

	protected static $_CLASS_ELSEIF_PROMISE		= 'se\Promise\ElseIfPromise';
	protected static $_CLASS_ELSE_PROMISE		= 'se\Promise\ElsePromise';

	protected $_class_elseif_promise;
	protected $_class_else_promise;
	
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
		$this->if			= self::makeSuperClosure($if);
		$this->block 		= self::makeSuperClosure($block);
		$this->fail			= $fail ? self::makeSuperClosure($fail) : $fail;
	}

	public function __invoke()
	{
		if($this->parent && $this->parent->state() == self::__STATE_NOT_EXECUTING__)
		{
			$this->_state = self::__STATE_EXECUTING_PARENT__;
			return call_user_func_array($this->parent, func_get_args());
		}
		try{
			$this->_state = self::__STATE_EXECUTING__;
			$condition = call_user_func_array($this->if, $args = func_get_args());

			if($condition)
			{
				$result = call_user_func_array($this->block, $args);
			}
			elseif($this->elseif)
			{
				try{
					$result = call_user_func_array($this->elseif, $args);
				}catch(ElseIfPromiseConditionException $e)
				{
					$elseIfConditionNotPassed = false;
				}
			}
			
			if((isset($elseIfConditionNotPassed) && !$elseIfConditionNotPassed) || $this->else)
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
		$class			= $this->getElseIfPromiseClass($class);
		$this->elseif	= new $class($this, $condition, $block);
		return $this->elseif;
	}

	public function _else($block, $class = null)
	{
		$class			= $this->getElsePromiseClass($class);
		$this->else		= new $class($this, $block);
		return $this->else;
	}
	
	/****************************************************************
	*
	* 				CONDITION PROMISES CLASSES
	*
	***************************************************************/
	
	/***************************************
	*
	* 				ELSE
	*
	**************************************/
	
	public function getElsePromiseClass($class = null)
	{
		if(null === $class)
		{
			if(null === $this->_class_else_promise)
			{
				return self::$_CLASS_ELSE_PROMISE;
			}
			return $this->_class_else_promise;
		}
		self::checkClassImplementsCorrectInterfaceOrThrowException($class, 'se\Promise\ElsePromiseInterface');
		return $class;
	}
	
	public static function setStaticElsePromiseClass($class)
	{
		self::checkClassImplementsCorrectInterfaceOrThrowException($class, 'se\Promise\ElsePromiseInterface');
		self::$_CLASS_ELSE_PROMISE = $class;
	}
	
	public function setElsePromiseClass($class)
	{
		self::checkClassImplementsCorrectInterfaceOrThrowException($class, 'se\Promise\ElsePromiseInterface');
		$this->_class_else_promise = $class;
		return $this;
	}
	
	/***************************************
	*
	* 				ELSE IF
	*
	**************************************/
	
	
	protected function getElseIfPromiseClass($class = null)
	{
				if(null === $class)
		{
			if(null === $this->_class_elseif_promise)
			{
				return self::$_CLASS_ELSEIF_PROMISE;
			}
			return $this->_class_elseif_promise;
		}
		self::checkClassImplementsCorrectInterfaceOrThrowException($class, 'se\Promise\ElseIfPromiseInterface');
		return $class;
	}

	public static function setStaticElseIfPromiseClass($class)
	{
		self::checkClassImplementsCorrectInterfaceOrThrowException($class, 'se\Promise\ElseIfPromiseInterface');
		self::$_CLASS_ELSEIF_PROMISE = $class;
	}
	
	public function setElseIfPromiseClass($class)
	{
		self::checkClassImplementsCorrectInterfaceOrThrowException($class, 'se\Promise\ElseIfPromiseInterface');
		$this->_class_elseif_promise = $class;
		return $this;
	}
}