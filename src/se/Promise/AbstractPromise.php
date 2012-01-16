<?php
namespace se\Promise;

abstract class AbstractPromise implements PromiseInterface
{
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
	
	protected $parent;

	/**
	 * @param Closure $closure
	 */
	public function __construct($fulfill, $fail = null, PromiseInterface $parent = null)
	{
		$this->fulfill		= self::makeSuperClosure($fulfill);
		$this->fail			= $fail ? self::makeSuperClosure($fail) : $fail;
		$this->chain		= new \ArrayObject();
		$this->parent		= $parent;
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
		try{
			$result = call_user_func_array($this->fulfill, func_get_args());

			foreach($this->chain as $promise)
			{
				$result = $promise($result);
			}if($this->parent)
			{
				return $result;
			}

			$this->result = $result;
			return $this;
		}catch(\Exception $e)
		{
			if($this->fail)
			{
				$result = call_user_func_array($this->fail, array($e, func_get_args()));
			}
			else
			{
				throw $e;
			}
		}

		if($this->parent)
		{
			return $result;
		}

		$this->result = $result;
		return $this;
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
}