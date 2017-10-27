<?php

namespace Binable\Tests;

use PHPUnit\Framework\TestCase;
use Binable\Classifier;

class ClassifierTest extends TestCase
{
	protected $classifier;

	/**
	 * @var array
	 */
	protected $input = [0.1, 3.4, 3.5, 3.6, 7.0, 9.0, 6.0, 4.4, 2.5, 3.9, 4.5, 2.8];

	public function setUp(): void
	{
		$this->classifier = new Classifier;
	}

	public function testClassifierCanGroupWithoutFilters(): void
	{

	}
}
