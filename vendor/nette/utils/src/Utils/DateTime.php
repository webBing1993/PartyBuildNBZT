<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Utils;

use Nette;


/**
 * DateTime.
 */
class DateTime extends \DateTime implements \JsonSerializable
{
	use Nette\SmartObject;

	/** minute in seconds */
	const MINUTE = 60;

	/** hour in seconds */
	const HOUR = 60 * self::MINUTE;

	/** day in seconds */
	const DAY = 24 * self::HOUR;

	/** week in seconds */
	const WEEK = 7 * self::DAY;

	/** average month in seconds */
	const MONTH = 2629800;

	/** average year in seconds */
	const YEAR = 31557600;


	/**
	 * DateTime object factory.
	 * @param  string|int|\DateTimeInterface
	 * @return self
	 */
	public static function from($time)
	{
		if ($time instanceof \DateTimeInterface) {
			return new static($time->format('Y-m-d H:i:s'), $time->getTimezone());

		} elseif (is_numeric($time)) {
			if ($time <= self::YEAR) {
				$time += time();
			}
			return (new static('@' . $time))->setTimeZone(new \DateTimeZone(date_default_timezone_get()));

		} else { // textual or NULL
			return new static($time);
		}
	}


	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->format('Y-m-d H:i:s');
	}


	/**
	 * @param  string
	 * @return self
	 */
	public function modifyClone($modify = '')
	{
		$dolly = clone $this;
		return $modify ? $dolly->modify($modify) : $dolly;
	}


	/**
	 * @param  int
	 * @return self
	 */
	public function setTimestamp($timestamp)
	{
		$zone = $this->getTimezone();
		$this->__construct('@' . $timestamp);
		return $this->setTimeZone($zone);
	}


	/**
	 * @return int|string
	 */
	public function getTimestamp()
	{
		$ts = $this->format('U');
		return is_float($tmp = $ts * 1) ? $ts : $tmp;
	}


	/**
	 * Returns new DateTime object formatted according to the specified format.
	 * @param string The format the $time parameter should be in
	 * @param string String representing the time
	 * @param string|\DateTimeZone desired timezone (default timezone is used if NULL is passed)
	 * @return self|FALSE
	 */
	public static function createFromFormat($format, $time, $timezone = NULL)
	{
		if ($timezone === NULL) {
			$timezone = new \DateTimeZone(date_default_timezone_get());

		} elseif (is_string($timezone)) {
			$timezone = new \DateTimeZone($timezone);

		} elseif (!$timezone instanceof \DateTimeZone) {
			throw new Nette\InvalidArgumentException('Invalid timezone given');
		}

		$date = parent::createFromFormat($format, $time, $timezone);
		return $date ? static::from($date) : FALSE;
	}


	/**
	 * Returns JSON representation in ISO 8601 (used by JavaScript).
	 * @return string
	 */
	public function jsonSerialize()
	{
		return $this->format('c');
	}

}
