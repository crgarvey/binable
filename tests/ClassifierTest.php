<?php

namespace Binable\Tests;

use PHPUnit\Framework\TestCase;
use Binable\Classifier;
use InvalidArgumentException;

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

	public function testClassifierCanGroupWithEqualFrequency(): void
	{
		$expected = [
			'Low'	=> [
				0.1,
				2.5,
				2.8,
				3.4
			],
			'Medium' => [
				3.5,
				3.6,
				3.9,
				4.4
			],
			'High'	=> [
				4.5,
				6,
				7,
				9
			]
		];
		$this->assertEquals($this->classifier->shouldHaveEqualFrequency()->classify($this->input), $expected);
	}

	public function testClassifierCanGroupEqualWidth(): void
	{
		$expected = [
			'Low'	=> [
				0.1,
				2.5,
				2.8
			],
			'Medium' => [
				3.4,
				3.5,
				3.6,
				3.9,
				4.4,
				4.5,
				6,
			],
			'High'	=> [
				7,
				9
			]
		];

		$this->assertEquals($this->classifier->shouldHaveEqualWidth()->classify($this->input), $expected);
	}

	public function testClassifierThrowsExceptionEqualFrequency(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$values = $this->input;
		array_splice($values, 1);

		$this->classifier->shouldHaveEqualFrequency()->classify($values);
	}

	public function testClassifierThrowsExceptionInvalidValue(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->classifier->shouldHaveEqualFrequency()->classify([1, 'a', 'b']);
	}
}
