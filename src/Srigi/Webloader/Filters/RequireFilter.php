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
		$ree = '~\\/~';

		$root = $loader->getFileCollection()->getRoot();
		$rootSegments = preg_split($ree, $root);

		$pathInfo = pathinfo($file);
		$pathInfoSegments = preg_split($ree, $pathInfo['dirname']);

		$fileRelativePath = array_diff($pathInfoSegments, $rootSegments);
		$fileRelativePath[] = $pathInfo['basename'];
		$fileRelativePath = join(DIRECTORY_SEPARATOR, $fileRelativePath);

		$this->lilo->appendLoadPath($root);
		$this->lilo->scan($fileRelativePath);
		$fileDeps = $this->lilo->getFileChain($fileRelativePath);

		$code = array_reduce($fileDeps, function($memo, $dep) {
			return $memo . $dep['content'];
		}, '');

		return $code;
	}
}
