<?php
/**
 * Degami Basics
 * PHP Version 7.0
 *
 * @category CMS / Framework
 * @package  Degami\Basics
 * @author   Mirko De Grandis <degami@github.com>
 * @license  MIT https://opensource.org/licenses/mit-license.php
 * @link     https://github.com/degami/basics
 */
namespace Degami\Basics\Html;

/**
 * tag interface
 */
interface TagInterface
{
    /**
     * Add child to tag
     *
     * @param mixed $child tag to add, can be a tag object or a string
     */
    public function addChild($child);

    /**
     * render tag html
     *
     * @return string tag html
     */
    public function renderTag();
}
