<?php
/**
 * Math class from Taylor Otwell.
 * http://blog.jgrossi.com/2013/generating-ids-like-youtube-or-bit-ly-using-php/
 */
namespace App;

class Math
{

    /**
     * The base.
     *
     * @var string
     */
    private static $base = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Convert a from a given base to base 10.
     *
     * @param  string $value
     * @param  int    $base
     * @return int
     */
    public static function toBase10($value, $b = 62)
    {
        $limit = strlen($value);
        $result = strpos(static::$base, $value[0]);

        for ($i = 1; $i < $limit; $i++) {
            $result = $b * $result + strpos(static::$base, $value[$i]);
        }

        return $result;
    }

    /**
     * Convert from base 10 to another base.
     *
     * @param  int $value
     * @param  int $base
     * @return string
     */
    public static function toBase($value, $b = 62)
    {
        $r = $value % $b;
        $result = static::$base[$r];
        $q = floor($value / $b);

        while ($q) {
            $r = $q % $b;
            $q = floor($q / $b);
            $result = static::$base[$r] . $result;
        }

        return $result;
    }
}
