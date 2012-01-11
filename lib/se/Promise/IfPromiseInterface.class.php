<?php

namespace se\Promise;

interface IfPromiseInterface extends PromiseInterface
{
	public function _elseif($condition, $block);
	public function _else($block);
}