<?php

namespace Binable;

use InputValidationException;

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
				throw new InputValidationException;
			}
		}

		// Determine if the input can be equally distributed across the bins.
		if ($this->equalFrequency === true && ! $this->canEquallyDistribute($values)) {
			throw new InputValidationException;
		}

		// Calculate the ranges.
		$ranges = $this->getRanges($values);

		// Group the value into each range, then normalize.
		$values = $this->groupIntoRange($values, $ranges);

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
	 * Get the equal width divisible by the group size.
	 *
	 * @return int
	 */
	protected function getEqualWidth($highestValue): int
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
	protected function getRanges(array $values): array
	{
		// Determine the highest value.
		$highestValue = $this->determineHighestValue($values);
		$lowestValue = $this->determineLowestValue($values);

		// When grouping width should be equal, determine the width.
		if ($this->equalWidth === true) {
			$highestValue = $this->getEqualWidth($highestValue);
		}

		$width = $highestValue / $this->getGroupSize();

		$ranges = range($lowestValue, $highestValue, $width);

		return $ranges;
	}

	/**
	 * Normalize the output.
	 *
	 * @return array
	 */
	protected function normalize($values): array
	{
		return array_combine(self::GROUPS, $values);
	}
}

