<?php

namespace Binable;

use InvalidArgumentException;

class Classifier
{
	/**
	 * @var array
	 */
	const GROUPS = ['Low', 'Medium', 'High'];

	/**
	 * @var bool
	 */
	protected $equalWidth = false;

	/**
	 * @var bool
	 */
	protected $equalFrequency = false;

	/**
	 * Requires the grouping to be of equal width.
	 *
	 * @return Classifier
	 */
	public function shouldHaveEqualWidth(bool $equal = true): Classifier
	{
		$this->equalWidth = $equal;
		$this->equalFrequency = $equal === true ? false : $this->equalFrequency;

		return $this;
	}

	/**
	 * Requires the grouping to be of equal frequency.
	 *
	 * @return bool
	 */
	public function shouldHaveEqualFrequency(bool $equal = true): Classifier
	{
		$this->equalFrequency = $equal;
		$this->equalWidth = $equal === true ? false : $this->equalWidth;

		return $this;
	}

	/**
	 * Classifies the input into an array.
	 *
	 * @param array $values
	 *
	 * @return array
	 */
	public function classify(array $values): array
	{
		// Validate the input.
		foreach ($values as $value) {
			if (! $this->isValidValue($value)) {
				throw new InvalidArgumentException;
			}
		}

		// Determine if the input can be equally distributed across the bins.
		if ($this->equalFrequency === true && ! $this->canEquallyDistribute($values)) {
			throw new InvalidArgumentException;
		}

		$value = asort($values);
		$values = ($this->equalWidth === true ? $this->groupIntoRange($values, $this->getEqualRanges($values)) : $this->getEqualValues($values));

		return $this->normalize($values);
	}

	/**
	 * Get the group size.
	 *
	 * @return int
	 */
	protected function getGroupSize(): int
	{
		return sizeof(self::GROUPS);
	}

	/**
	 * Determines whether the value is valid or not.
	 *
	 * @return bool
	 */
	protected function isValidValue($value): bool
	{
		return is_numeric($value) === true;
	}

	/**
	 * Determine whether the grouping can equally distribute across bins.
	 *
	 * @return bool
	 */
	protected function canEquallyDistribute(array $values): bool
	{
		return sizeof($values) % $this->getGroupSize() === 0;
	}

	/**
	 * Determine the highest value from a set of values.
	 *
	 * @param array $values
	 *
	 * @return int|double
	 */
	protected function determineHighestValue(array $values)
	{
		$previous = 0;

		foreach ($values as $value) {
			if ($value > $previous) {
				$previous = $value;
			}
		}

		return $previous;
	}

	/**
	 * Determine the lowest value from a set of values.
	 *
	 * @param array $values
	 *
	 * @return int|double
	 */
	protected function determineLowestValue(array $values)
	{
		foreach ($values as $value) {
			if (isset($previous) === false) {
				$previous = $value;
				continue;
			}

			if ($value < $previous) {
				$previous = $value;
			}
		}

		return $previous;
	}
	/**
	 * Get the equal width divisible by the size.
	 *
	 * @param int $highestValue
	 *
	 * @return int
	 */
	protected function getHighestValueForWidth($highestValue): int
	{
		$highestValue = ceil($highestValue);

		while (($highestValue % $this->getGroupSize()) !== 0) {
			$highestValue++;
		}

		return $highestValue;
	}

	/**
	 * Groups the values into the corresponding range.
	 *
	 * @return array
	 */
	protected function groupIntoRange(array $values, array $ranges): array
	{
		$groupedValues = [];

		foreach ($values as $value) {
			$key = 0;
			for ($i = 0; $i < sizeof($ranges); $i++) {
				$firstValue = $ranges[$i];
				$secondValue = null;

				if (array_key_exists($i + 1, $ranges) === true) {
					$secondValue = $ranges[$i + 1];
				}

				if ($secondValue === null) {
					$key = $i;
					break;
				}

				if ($value >= $firstValue && $value < $secondValue) {
					$key = $i;
					break;
				}
			}

			if (! isset($groupedValues[$key]) || ! is_array($groupedValues[$key])) {
				$groupedValues[$key] = [];
			}

			array_push($groupedValues[$key], $value);
		}

		return $groupedValues;
	}

	/**
	 * Get the ranges based on filters.
	 *
	 * @return array
	 */
	protected function getEqualRanges(array $values): array
	{
		// Determine the highest value.
		$highestValue = $this->determineHighestValue($values);
		$lowestValue = $this->determineLowestValue($values);

		// When grouping width should be equal, determine the width.
		if ($this->equalWidth === true) {
			$highestValue = $this->getHighestValueForWidth($highestValue);
		}

		$width = $highestValue / $this->getGroupSize();

		$ranges = range($lowestValue, $highestValue, $width);

		return $ranges;
	}

	/**
	 * Get the values organized into buckets equally.
	 *
	 * @param array $values
	 *
	 * @return array
	 */
	protected function getEqualValues(array $values): array
	{
		return array_chunk($values, sizeof($values) / $this->getGroupSize());
	}

	/**
	 * Normalize the output.
	 *
	 * @return array
	 */
	protected function normalize($values): array
	{
		$shouldSort = false;

		for ($index = 0; $index < sizeof(self::GROUPS); $index++) {
			if (! empty($values[$index])) {
				continue;
			}

			$shouldSort = true;
			$values[$index] = [];
		}

		if ($shouldSort === true) {
			ksort($values);
		}

		return array_combine(self::GROUPS, $values);
	}
}

