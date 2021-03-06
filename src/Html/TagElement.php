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

use \Degami\Basics\Traits\ToolsTrait;
use \Degami\Basics\Exceptions\BasicException;

/**
 * A class to render form fields tags
 */
class TagElement extends BaseElement implements TagInterface
{
    use ToolsTrait;

    /** @var array tags that do not need to be closed */
    public static $void_tags = [
        'area',
        'base',
        'br',
        'col',
        'command',
        'embed',
        'hr',
        'img',
        'input',
        'keygen',
        'link',
        'meta',
        'param',
        'source',
        'track',
        'wbr',
    ];

    /** @var array tags that have a value attribute */
    public static $with_value_tags = [
        'button',
        'input',
        'option',
        'progress',
        'param',
    ];

    /** @var string tag */
    protected $tag;

    /** @var string input type */
    protected $type;

    /** @var string input name */
    protected $name;

    /** @var string html id attribute */
    protected $id;

    /** @var mixed "value" attribute value */
    protected $value;

    /** @var string text */
    protected $text;

    /** @var array tag children */
    protected $children;

    /** @var array reserved attributes */
    protected $reserved_attributes = ['type','name', 'id','value'];

    /** @var null|boolean tag needs closing tag */
    protected $has_close = null;

    /** @var boolean tag needs value attribute */
    protected $value_needed = true;

    /**
     * Class constructor
     *
     * @param array $options build options
     */
    public function __construct($options = [])
    {
        $this->tag = '';

        $this->type = '';
        $this->name = '';
        $this->id = '';
        $this->value = '';

        $this->text = '';
        $this->children = [];

        if (isset($options['tag'])) {
            $this->tag = trim(strtolower($options['tag']));
            unset($options['tag']);
        }
        if (isset($options['reserved_attributes'])) {
            $this->reserved_attributes = $options['reserved_attributes'];
            unset($options['reserved_attributes']);
        }

        if (!in_array($this->tag, static::$with_value_tags)) {
            $this->value_needed = false;
        }

        if (!in_array($this->tag, static::$void_tags)) {
            $this->has_close = true;
        }

        foreach ($this->reserved_attributes as $key) {
            if (isset($options[$key])) {
                if (property_exists(get_class($this), $key)) {
                    $this->{$key} = $options[$key];
                    unset($options[$key]);
                }
            }
        }

        if (isset($options['children']) && !isset($options['has_close'])) {
            if (!empty($options['children'])) {
                $this->has_close = true;
            }
        }

        $this->setClassProperties($options);

        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = $this->getElementClassName();
        }
    }

    /**
     * Get css class name
     *
     * @return string css class name
     */
    public function getElementClassName()
    {
        return strtolower($this->tag == 'input' ? $this->type : $this->tag);
    }

    /**
     * Gets html tag string
     *
     * @return string tag html representation
     */
    public function renderTag()
    {
        if (method_exists($this, 'executeAlter')) {
            static::executeAlter("/.*?_before_render_".$this->tag."_alter$/i", [&$this]);
        }
        $reserved_attributes = "";
        foreach ($this->reserved_attributes as $key) {
            if (property_exists(get_class($this), $key)
                && (!empty($this->{$key}) || ($key == 'value' && $this->getValueNeeded()))
            ) {
                $reserved_attributes .= ' '.$key.'="'.$this->{$key}.'"';
            }
        }
        $attributes = $this->getAttributes($this->reserved_attributes);
        return "<{$this->tag}{$reserved_attributes}{$attributes}".($this->has_close ? ">" : "/>").
        $this->text.
        ($this->has_close ? $this->renderChildren()."</{$this->tag}>" : "");
    }

    /**
     * Adds a list of child to tag
     *
     * @param  TagInterface[] $children children to add
     * @return TagList
     */
    public function addChildren($children)
    {
        if (!is_array($children)) {
            $children = [$children];
        }

        foreach ($children as $child) {
            if ($child instanceof TagInterface) {
                $this->addChild($child);
            }
        }

        return $this;
    }

    /**
     * Add child to tag
     *
     * @param  TagElement|string $child child to add
     * @return TagElement
     */
    public function addChild($child)
    {
        $this->children[] = $child;
        $this->has_close = true;
        return $this;
    }

    /**
     * Gets tag children html representation
     *
     * @return string tag children html representation
     */
    private function renderChildren()
    {
        $out = "";
        foreach ($this->children as $key => $value) {
            if ($value instanceof TagInterface) {
                $out .= $value->renderTag();
            } elseif (is_scalar($value)) {
                $out .= $value;
            }
        }
        return $out;
    }

    /**
     * Return if value attribute is nneeded
     *
     * @return bool
     */
    private function getValueNeeded()
    {
        return $this->value_needed;
    }

    /**
     * toString magic method
     *
     * @return string the tag html
     */
    public function __toString()
    {
        try {
            return $this->renderTag();
        } catch (BasicException $e) {
            return $e->getMessage()."\n".$e->getTraceAsString();
        }
    }
}
