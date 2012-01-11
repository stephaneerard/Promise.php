<?php

namespace se\Promise;

class Autoloader
{
	public function register()
	{
		spl_autoload_register(function($name){
			$name = str_replace('\\', '/', $name);
			if(file_exists($file = __DIR__ . '/lib/' . $name . '.class.php'))
			{
				require $file;
				return true;
			}
			return false;
		});
	}
	
	/**
	 * 
	 * @return Autoloader
	 */
	public static function create()
	{
		return new self();
	}
}
