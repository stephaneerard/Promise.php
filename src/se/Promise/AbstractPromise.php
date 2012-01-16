<?php
namespace se\Promise;

abstract class AbstractPromise implements PromiseInterface
{
	protected static $_CLASS_IF_PROMISE 	= 'se\Promise\IfPromise';
	protected static $_CLASS_DO_PROMISE 	= 'se\Promise\DoPromise';
	protected static $_CLASS_WHILE_PROMISE 	= 'se\Promise\WhilePromise';
	protected static $_CLASS_FOR_PROMISE 	= 'se\Promise\IfPromise';

	protected $_class_if_promise;
	protected $_class_do_promise;
	protected $_class_while_promise;
	protected $_class_for_promise;

	const __STATE_NOT_EXECUTING__			= 0;
	const __STATE_EXECUTING__				= 1;
	const __STATE_EXECUTING_PARENT__		= 2;
	const __STATE_EXECUTING_CHILDREN__		= 3;
	const __STATE_EXECUTED__				= 4;

	protected $_state = self::__STATE_NOT_EXECUTING__;

	/**
	 * @var SuperClosure
	 */
	protected $fulfill = null;

	/**
	 * @var SuperClosure
	 */
	protected $fail = null;

	/**
	 * @var ArrayObject
	 */
	protected $chain = array();

	protected $result;

	/**
	 * @var PromiseInterface
	 */
	protected $parent = null;

	/**
	 * @param Closure $closure
	 */
	public function __construct($fulfill, $fail = null, PromiseInterface $parent = null)
	{
		$this->fulfill		= self::makeSuperClosure($fulfill);
		$this->fail			= $fail ? self::makeSuperClosure($fail) : $fail;
		$this->parent		= $parent;
		$this->chain		= new \ArrayObject();
	}

	public function state($state = null)
	{
		return $state ? $this->_state = $state : $this->_state;
	}

	/**
	 * @param mixed $closure
	 * @param mixed $class
	 * @return PromiseInterface
	 */
	static public function instanciate($fulfill, $fail = null, $class = null, $parent = null)
	{
		$class		= null === $class ? 'se\Promise\Promise' : $class;
		$object 	= new $class($fulfill, $fail, $parent);

		if(!$object instanceof PromiseInterface)
		{
			throw new \InvalidArgumentException(sprintf('%s class does not implement PromiseInterface', $class));
		}

		return $object;
	}

	public function __invoke()
	{
		$this->_state = self::__STATE_EXECUTING__;
		try{
			$result = call_user_func_array($this->fulfill, func_get_args());

			$this->_state = self::__STATE_EXECUTING_CHILDREN__;
			foreach($this->chain as $promise)
			{
				$result = $promise($result);
			}

			if($this->parent)
			{
				return $result;
			}
			else
			{
				$this->result = $result;
				$this->_state = self::__STATE_NOT_EXECUTING__;
				return $this;
			}
		}catch(\Exception $e)
		{
			if($this->fail)
			{
				$result = call_user_func_array($this->fail, array($e, func_get_args()));
				return $this;
			}
			else
			{
				throw $e;
			}
		}
		$this->_state = self::__STATE_NOT_EXECUTING__;
	}

	public function result()
	{
		if($this->parent)
		{
			return $this->parent->result();
		}
		return $this->result;
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function getRoot()
	{
		return $this->parent ? $this->parent->getRoot() : $this;
	}

	public function then($fulfill, $fail = null, $class = null)
	{
		$new 		= self::instanciate($fulfill, $fail, $class, $this);
		$this->chain->append($new);
		return $this;
	}

	public function _if($condition, $block, $fail = null, $class = null)
	{
		$class 			= $this->getIfPromiseClass($class);
		$if 			= new $class($this, $condition, $block, $fail);
		$this->chain->append($if);
		return $if;
	}

	public function _do($block, $while, $fail = null, $class = null)
	{
		$class 		= $this->getDoPromiseClass($class);
		$do 		= new $class($this, $block, $while, $fail);
		$this->then($do);
		return $do;
	}

	public function _while($condition, $block, $fail = null, $class = null)
	{

	}
	public function _for($start, $block, $fail = null, $class = null)
	{

	}

	/****************************************************************
	 *
	*
	* 							HELPERS
	*
	*
	***************************************************************/

	static public function makeSuperClosure($closure = null)
	{
		if($closure instanceof \Closure)
		{
			$closure = SuperClosure::create($closure);
		}
		elseif(!$closure instanceof SuperClosure)
		{
			throw new \InvalidArgumentException('$closure must be an instance of Closure or SuperClosure');
		}
		return $closure;
	}


	public static function checkClassImplementsCorrectInterfaceOrThrowException($class, $interface)
	{
		$refl = new \ReflectionClass($class);
		if(!$refl->implementsInterface($interface))
		{
			throw new \InvalidArgumentException(sprintf('The class "%s" does not implement the interface "%s"', $class, $interface));
		}
	}

	/****************************************************************
	 *
	* 				CONDITION/LOOP PROMISES CLASSES
	*
	***************************************************************/

	/***************************************
	 *
	* 				IF
	*
	**************************************/

	public function getIfPromiseClass($class = null)
	{
		if(null === $class)
		{
			if(null === $this->_class_if_promise)
			{
				return self::$_CLASS_IF_PROMISE;
			}
			return $this->_class_if_promise;
		}
		self::checkClassImplementsCorrectInterfaceOrThrowException($class, 'se\Promise\IfPromiseInterface');
		return $class;
	}

	public static function setStaticIfPromiseClass($class)
	{
		self::checkClassImplementsCorrectInterfaceOrThrowException($class, 'se\Promise\IfPromiseInterface');
		self::$_CLASS_IF_PROMISE = $class;
	}

	/**
	 *
	 * @param string $class
	 * @return AbstractPromise
	 */
	public function setIfPromiseClass($class)
	{
		self::checkClassImplementsCorrectInterfaceOrThrowException($class, 'se\Promise\IfPromiseInterface');
		$this->_class_if_promise = $class;
		return $this;
	}


	/***************************************
	 *
	* 				DO
	*
	**************************************/

	/**
	 * @param string $class
	 * @return string
	 */
	public function getDoPromiseClass($class = null)
	{
		if(null === $class)
		{
			if(null === $this->_class_do_promise)
			{
				return self::$_CLASS_DO_PROMISE;
			}
			return $this->_class_do_promise;
		}
		self::checkClassImplementsCorrectInterfaceOrThrowException($class, 'se\Promise\DoPromiseInterface');
		return $class;
	}

	/**
	 * @param string $class
	 */
	public static function setStaticDoPromiseClass($class)
	{
		self::checkClassImplementsCorrectInterfaceOrThrowException($class, 'se\Promise\DoPromiseInterface');
		self::$_CLASS_DO_PROMISE = $class;
	}

	/**
	 *
	 * @param string $class
	 * @return AbstractPromise
	 */
	public function setDoPromiseClass($class)
	{
		self::checkClassImplementsCorrectInterfaceOrThrowException($class, 'se\Promise\DoPromiseInterface');
		$this->_class_do_promise = $class;
		return $this;
	}


	/***************************************
	 *
	* 				WHILE
	*
	**************************************/

	/**
	 *
	 * @param string $class
	 * @return string
	 */
	public function getWhilePromiseClass($class = null)
	{
		if(null === $class)
		{
			if(null === $this->_class_do_promise)
			{
				return self::$_CLASS_WHILE_PROMISE;
			}
			return $this->_class_while_promise;
		}
		self::checkClassImplementsCorrectInterfaceOrThrowException($class, 'se\Promise\WhilePromiseInterface');
		return $class;
	}

	/**
	 * @param string $class
	 */
	public static function setStaticWhilePromiseClass($class)
	{
		self::checkClassImplementsCorrectInterfaceOrThrowException($class, 'se\Promise\WhilePromiseInterface');
		self::$_CLASS_WHILE_PROMISE = $class;
	}

	/**
	 *
	 * @param string $class
	 * @return AbstractPromise
	 */
	public function setWhilePromiseClass($class)
	{
		self::checkClassImplementsCorrectInterfaceOrThrowException($class, 'se\Promise\WhilePromiseInterface');
		$this->_class_while_promise = $class;
		return $this;
	}


	/***************************************
	 *
	* 				FOR
	*
	**************************************/

	/**
	 * 
	 * @param string $class
	 * @return string
	 */
	public function getForPromiseClass($class = null)
	{
		if(null === $class)
		{
			if(null === $this->_class_for_promise)
			{
				return self::$_CLASS_FOR_PROMISE;
			}
			return $this->_class_for_promise;
		}
		self::checkClassImplementsCorrectInterfaceOrThrowException($class, 'se\Promise\ForPromiseInterface');
		return $class;
	}
	
	/**
	* @param string $class
	*/
	public static function setStaticForPromiseClass($class)
	{
		self::checkClassImplementsCorrectInterfaceOrThrowException($class, 'se\Promise\ForPromiseInterface');
		self::$_CLASS_FOR_PROMISE = $class;
	}
	
	/**
	 *
	 * @param string $class
	 * @return AbstractPromise
	 */
	public function setForPromiseClass($class)
	{
		self::checkClassImplementsCorrectInterfaceOrThrowException($class, 'se\Promise\ForPromiseInterface');
		$this->_class_for_promise = $class;
		return $this;
	}
}