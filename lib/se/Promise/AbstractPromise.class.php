<?php
namespace se\Promise;

abstract class AbstractPromise implements PromiseInterface
{
	protected static $_CLASS_IF_PROMISE 	= 'se\Promise\IfPromise';
	protected static $_CLASS_DO_PROMISE 	= 'se\Promise\DoPromise';
	protected static $_CLASS_WHILE_PROMISE 	= 'se\Promise\WhilePromise';
	protected static $_CLASS_FOR_PROMISE 	= 'se\Promise\IfPromise';

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
			throw new InvalidArgumentException(sprintf('%s class does not implement PromiseInterface', $class));
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
		$class 			= self::getIfPromiseClass($class);
		$if 			= new $class($this, $condition, $block, $fail);
		$this->chain->append($if);
		return $if;
	}

	public function _do($block, $while, $fail = null, $class = null)
	{
		$class 		= self::getDoPromiseClass($class);
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

	protected function getIfPromiseClass($class = null)
	{
		return null === $class ? self::$_CLASS_IF_PROMISE : $class;
	}

	public static function setIfPromiseClass($class)
	{
		$refl = new \ReflectionClass($class);
		if(!$refl->implementsInterface('se\Promise\IfPromiseInterface'))
		{
			throw new \InvalidArgumentException(sprintf('The class "%s" does not implement the interface se\Promise\IfPromiseInterface'));
		}
		self::$_CLASS_IF_PROMISE = $class;
	}

	protected function getDoPromiseClass($class = null)
	{
		return null === $class ? self::$_CLASS_DO_PROMISE : $class;
	}

	public static function setDoPromiseClass($class)
	{
		$refl = new \ReflectionClass($class);
		if(!$refl->implementsInterface('se\Promise\DoPromiseInterface'))
		{
			throw new \InvalidArgumentException(sprintf('The class "%s" does not implement the interface se\Promise\DoPromiseInterface'));
		}
		self::$_CLASS_DO_PROMISE = $class;
	}

	protected function getWhilePromiseClass($class = null)
	{
		return null === $class ? self::$_CLASS_WHILE_PROMISE : $class;
	}

	public static function setWhilePromiseClass($class)
	{
		$refl = new \ReflectionClass($class);
		if(!$refl->implementsInterface('se\Promise\WhilePromiseInterface'))
		{
			throw new \InvalidArgumentException(sprintf('The class "%s" does not implement the interface se\Promise\WhilePromiseInterface'));
		}
		self::$_CLASS_WHILE_PROMISE = $class;
	}

	protected function getForPromiseClass($class = null)
	{
		return null === $class ? self::$_CLASS_FOR_PROMISE : $class;
	}

	public static function setForPromiseClass($class)
	{
		$refl = new \ReflectionClass($class);
		if(!$refl->implementsInterface('se\Promise\ForPromiseInterface'))
		{
			throw new \InvalidArgumentException(sprintf('The class "%s" does not implement the interface se\Promise\ForPromiseInterface'));
		}
		self::$_CLASS_FOR_PROMISE = $class;
	}
}