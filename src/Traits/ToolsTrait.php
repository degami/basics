<?php
/**
 * Degami Basics
 * PHP Version 7.0
 *
 * @category CMS / Framework
 * @package  Degami\Basics
 * @author   Mirko De Grandis <degami@github.com>
 * @license  MIT https://opensource.org/licenses/mit-license.php
 * @link     https://github.com/degami/sitebase
 */
namespace Degami\Basics\Traits;

use \Traversable;

/**
 * utils Trait
 */
trait ToolsTrait
{
    /**
     * Set class properties. Used on constructors
     *
     * @param array $options values to set
     */
    private function setClassProperties($options)
    {
        foreach ($options as $name => $value) {
            $name = trim($name);
            if (property_exists(get_class($this), $name)) {
                $this->{$name} = $value;
            }
        }
    }

    /**
     * Checks if variable is suitable for use with foreach
     *
     * @param  mixed $var element to check
     * @return bool
     */
    public static function isForeacheable($var)
    {
        return (is_array($var) || ($var instanceof Traversable));
    }

    /**
     * Take a string_like_this and return a StringLikeThis
     *
     * @param  string
     * @return string
     */
    public static function snakeCaseToPascalCase($input)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $input)));
    }

    /**
     * Take a StringLikeThis and return string_like_this
     *
     * @param  string
     * @return string
     */
    public static function pascalCaseToSnakeCase($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

    /**
     * slugify string
     *
     * @param  string $text
     * @return string
     */
    public function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d\/]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w\/]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    /**
     * format byte size
     *
     * @param  integer $size size in bytes
     * @return string       formatted size
     */
    public static function formatBytes($size)
    {
        $units = [' B', ' KB', ' MB', ' GB', ' TB'];
        for ($i = 0; $size >= 1024 && $i < 4; $i++) {
            $size /= 1024;
        }
        return round($size, 2).$units[$i];
    }

    /**
     * applies plain_text to text
     *
     * @param  string $text text to encode
     * @return string       plain version of $text
     */
    public static function processPlain($text)
    {
        // if using PHP < 5.2.5 add extra check of strings for valid UTF-8
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
