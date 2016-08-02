<?php

namespace Srigi\Webloader\Filters;

use diacronos\Lilo\Lilo;
use WebLoader;


class RequireFilter
{
	/** @var Lilo */
	private $lilo;


	/**
	 * @param string|array $extensions
	 */
	public function __construct($extensions = array('js'))
	{
		if (!is_array($extensions)) {
			$extensions = array($extensions);
		}

		$this->lilo = $lilo = new Lilo($extensions);;
	}


	/**
	 * @param string $code
	 * @param WebLoader\Compiler $loader
	 * @param string $file
	 * @return string
	 */
	public function __invoke($code, WebLoader\Compiler $loader, $file)
	{
		$root = $loader->getFileCollection()->getRoot();
		$this->lilo->appendLoadPath($root);

		$pathInfo = pathinfo($file);
		$fileName = $pathInfo['basename'];
		$this->lilo->scan($fileName);
		$fileDeps = $this->lilo->getFileChain($fileName);

		$code = array_reduce($fileDeps, function($memo, $dep) {
			return $memo . $dep['content'];
		}, '');

		return $code;
	}
}
