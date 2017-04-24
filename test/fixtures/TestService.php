<?php declare(strict_types=1);
namespace SoapMiddlewareTest\fixtures;

/**
 * Class TestService
 *
 * This service is built to demonstrate how to easily setup a webservice based on zend-expressive 2.0.
 *
 * @author Daniel Wendrich <daniel.wendrich@gmail.com>
 */
class TestService
{
    const VERSION = "1.0.0";

    /**
     * TestService constructor.
     */
    public function __construct()
    {

    }

    public function doNothing()
    {
        // does absolutely nothing
    }

    public function testMethod(string $testValue = 'test')
    {
        // does absolutely nothing, too
    }

    public function testReturnString(): string
    {
        return 'constant string value';
    }

    /**
     * Create a random positive integer value between min and max.
     *
     * @param int $min The minimum value.
     * @param int $max The maximum value. Has to be greater than the minimum value.
     * @return int
     * @throws \SoapFault A soap fault will be raised if the minimum value exceeds the maximum value.
     */
    public function getRandomInt(int $min = 0, int $max = PHP_INT_MAX): int
    {
        if ( $min < 0 || $min > PHP_INT_MAX ) {
            $min = 0;
        }

        if ( empty($max) || $max < 0 || $max > PHP_INT_MAX ) {
            $max = PHP_INT_MAX;
        }

        if ( $min > $max ) {
            throw new \SoapFault('Client', "A value for 'min' greater than 'max' is not allowed.");
        }

        return mt_rand($min, $max);
    }

    /**
     * Get the current version number.
     *
     * @return string Returns the current version number as string.
     */
    public function getVersion(): string
    {
        return self::VERSION;
    }

    /**
     * Reverses the given string input.
     *
     * @param string $input The string to be reversed.
     * @return string The reversed string input.
     */
    public function reverseString(string $input): string
    {
        $encoding = mb_detect_encoding($input);
        $len = mb_strlen($input, $encoding);
        $rev = '';

        while ( $len-- > 0 ) {
            $rev .= mb_substr($input, $len, 1, $encoding);
        }

        return $rev;
    }
}