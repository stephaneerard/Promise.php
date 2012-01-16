<?php

namespace se\Promise;

interface IfPromiseInterface extends PromiseInterface
{
	/**
	 * @param mixed $condition
	 * @param mixed $block
	 * @return ElseIfPromiseInterface
	 */
	public function _elseif($condition, $block, $class = null);
	
	/**
	* @param mixed $block
	* @return ElsePromiseInterface
	*/
	public function _else($block, $class = null);
	
}