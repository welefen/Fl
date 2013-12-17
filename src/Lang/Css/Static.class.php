<?php
/**
 * 
 * CSS static class
 * @author welefen
 *
 */
class Fl_Css_Static {

    /**
     * 
     * @ detail type
     * @var array
     */
    public static $atType = array(
        '@import ' => FL_TOKEN_CSS_AT_IMPORT, 
        '@charset ' => FL_TOKEN_CSS_AT_CHARSET, 
        '/^\@media[^\w]/i' => FL_TOKEN_CSS_AT_MEDIA, 
        '@namespace ' => FL_TOKEN_CSS_AT_NAMESPACE, 
        '@font-face' => FL_TOKEN_CSS_AT_FONTFACE, 
        '@page' => FL_TOKEN_CSS_AT_PAGE, 
        '/^\@(?:\-(?:webkit|moz|o|ms)\-)?keyframes/i' => FL_TOKEN_CSS_AT_KEYFRAMES, 
        '@-moz' => FL_TOKEN_CSS_AT_MOZILLA
    );

    /**
     * 
     * special tokens
     * @var array
     */
    public static $specialTokens = array(
        array(
            '[;', 
            ';]', 
            FL_TOKEN_CSS_HACK
        )
    );

    /**
     * 
     * prefix and suffix in inline style
     * @var array
     */
    public static $stylePrefixAndSuffix = array(
        array(
            "<!--", 
            "-->"
        )
    );

    /**
     * 
     * css comment pattern
     * @var RegexIterator
     */
    public static $commentPattern = '/\/\*.*?\*\//';

    /**
     * 
     * hack chars in property
     * @var array
     */
    public static $propertyHack = array(
        '*', 
        '!', 
        '$', 
        '&', 
        '*', 
        '(', 
        ')', 
        '=', 
        '%', 
        '+', 
        '@', 
        ',', 
        '.', 
        '/', 
        '`', 
        '[', 
        ']', 
        '#', 
        '~', 
        '?', 
        ':', 
        '<', 
        '>', 
        '|', 
        '_', 
        '-', 
        '£', 
        '¬', 
        '¦'
    );

    /**
     * 
     * selector token split char
     * @var array
     */
    public static $selectorCharUtil = array(
        '#' => 1, 
        '.' => 1, 
        ':' => 1, 
        '[' => 1, 
        '>' => 1, 
        '+' => 1, 
        '~' => 1, 
        '*' => 1, 
        ',' => 1, 
        '/' => 1, 
        " " => 1
    );

    /**
     * 
     * namespace pattern
     * foo|div
     * @var RegexIterator
     */
    public static $namespacePattern = "/^[\w\*]+\|/";

    /**
     * 
     * selector token check pattern
     * @var array
     */
    public static $selectorTokenPattern = array(
        FL_TOKEN_CSS_SELECTOR_ID => "/^\#[\w\-]+$/ies", 
        FL_TOKEN_CSS_SELECTOR_CLASS => "/^\.[\w\-]+$/ies", 
        FL_TOKEN_CSS_SELECTOR_TYPE => "/^(?:[\-a-z][\w]*)|(?:[\d\.]+\%)$/ies"
    );

    /**
     * 
     * regular property pattern
     * @var RegexIterator
     */
    public static $propertyPattern = "/^[a-z\-]+$/";

    /**
     * 
     * css3 property prefix pattern
     * -webkit, -moz, -o, -ms
     * @var RegexIterator
     */
    public static $css3PropertyPrefixPattern = "/^\-(webkit|moz|o|ms)\-/";

    /**
     * 
     * css hack char in property
     * @var RegexIterator
     */
    public static $propertyCharHackPattern = "/[^a-z\-]/i";

    /**
     * 
     * important in css value
     * @var string
     */
    public static $importantPattern = '/\!\s*important/i';

    /**
     * 
     * ie hack in value, eg: color: red\9;
     * @var RegexIterator
     */
    public static $ieValueHackPattern = "/\\\\\d+$/i";

    /**
     * 
     * multi same property pattern
     * @var regexp
     */
    public static $multiSamePropertyPattern = "/background/i";

    /**
     * 
     * 属性名包含这些则不进行排序
     * @var array
     */
    public static $unSortNames = array(
        'padding', 
        'margin', 
        'font', 
        'background', 
        'border', 
        'list', 
        'outline'
    );

    /**
     * 
     * multi same property in a selector
     * @var array
     */
    public static $multiSameProperty = array(
        "background" => 1, 
        "background-image" => 1, 
        "background-color" => 1, 
        "display" => 1, 
        "clip" => 1
    );

    /**
     * 
     * 颜色关键字
     * @var array
     */
    public static $colorKeywords = array(
        'black' => '#000000', 
        'silver' => '#C0C0C0', 
        'gray' => '#808080', 
        'white' => '#FFFFFF', 
        'maroon' => '#800000', 
        'red' => '#FF0000', 
        'purple' => '#800080', 
        'fuchsia' => '#FF00FF', 
        'green' => '#008000', 
        'lime' => '#00FF00', 
        'olive' => '#808000', 
        'yellow' => '#FFFF00', 
        'navy' => '#000080', 
        'blue' => '#0000FF', 
        'teal' => '#008080', 
        'aqua' => '#00FFFF',  //Extended color keywords
        'aliceblue' => '#f0f8ff', 
        'antiquewhite' => '#faebd7', 
        'aqua' => '#00ffff', 
        'aquamarine' => '#7fffd4', 
        'azure' => '#f0ffff', 
        'beige' => '#f5f5dc', 
        'bisque' => '#ffe4c4', 
        'black' => '#000000', 
        'blanchedalmond' => '#FFEBCD', 
        'blue' => '#0000FF', 
        'blueviolet' => '#8A2BE2', 
        'brown' => '#A52A2A', 
        'burlywood' => '#DEB887', 
        'cadetblue' => '#5F9EA0', 
        'chartreuse' => '#7FFF00', 
        'chocolate' => '#D2691E', 
        'coral' => '#FF7F50', 
        'cornflowerblue' => '#6495ED', 
        'cornsilk' => '#FFF8DC', 
        'crimson' => '#DC143C', 
        'cyan' => '#00FFFF', 
        'darkblue' => '#00008B', 
        'darkcyan' => '#008B8B', 
        'darkgoldenrod' => '#B8860B', 
        'darkgray' => '#A9A9A9', 
        'darkgreen' => '#006400', 
        'darkgrey' => '#A9A9A9', 
        'darkkhaki' => '#BDB76B', 
        'darkmagenta' => '#8B008B', 
        'darkolivegreen' => '#556B2F', 
        'darkorange' => '#FF8C00', 
        'darkorchid' => '#9932CC', 
        'darkred' => '#8B0000', 
        'darksalmon' => '#E9967A', 
        'darkseagreen' => '#8FBC8F', 
        'darkslateblue' => '#483D8B', 
        'darkslategray' => '#2F4F4F', 
        'darkslategrey' => '#2F4F4F', 
        'darkturquoise' => '#00CED1', 
        'darkviolet' => '#9400D3', 
        'deeppink' => '#FF1493', 
        'deepskyblue' => '#00BFFF', 
        'dimgray' => '#696969', 
        'dimgrey' => '#696969', 
        'dodgerblue' => '#1E90FF', 
        'firebrick' => '#B22222', 
        'floralwhite' => '#FFFAF0', 
        'forestgreen' => '#228B22', 
        'fuchsia' => '#FF00FF', 
        'gainsboro' => '#DCDCDC', 
        'ghostwhite' => '#F8F8FF', 
        'gold' => '#FFD700', 
        'goldenrod' => '#DAA520', 
        'gray' => '#808080', 
        'green' => '#008000', 
        'greenyellow' => '#ADFF2F', 
        'grey' => '#808080', 
        'honeydew' => '#F0FFF0', 
        'hotpink' => '#FF69B4', 
        'indianred' => '#CD5C5C', 
        'indigo' => '#4B0082', 
        'ivory' => '#FFFFF0', 
        'khaki' => '#F0E68C', 
        'lavender' => '#E6E6FA', 
        'lavenderblush' => '#FFF0F5', 
        'lawngreen' => '#7CFC00', 
        'lemonchiffon' => '#FFFACD', 
        'lightblue' => '#ADD8E6', 
        'lightcoral' => '#F08080', 
        'lightcyan' => '#E0FFFF', 
        'lightgoldenrodyellow' => '#FAFAD2', 
        'lightgray' => '#D3D3D3', 
        'lightgreen' => '#90EE90', 
        'lightgrey' => '#D3D3D3', 
        'lightpink' => '#FFB6C1', 
        'lightsalmon' => '#FFA07A', 
        'lightseagreen' => '#20B2AA', 
        'lightskyblue' => '#87CEFA', 
        'lightslategray' => '#778899', 
        'lightslategrey' => '#778899', 
        'lightsteelblue' => '#B0C4DE', 
        'lightyellow' => '#FFFFE0', 
        'lime' => '#00FF00', 
        'limegreen' => '#32CD32', 
        'linen' => '#FAF0E6', 
        'magenta' => '#FF00FF', 
        'maroon' => '#800000', 
        'mediumaquamarine' => '#66CDAA', 
        'mediumblue' => '#0000CD', 
        'mediumorchid' => '#BA55D3', 
        'mediumpurple' => '#9370DB', 
        'mediumseagreen' => '#3CB371', 
        'mediumslateblue' => '#7B68EE', 
        'mediumspringgreen' => '#00FA9A', 
        'mediumturquoise' => '#48D1CC', 
        'mediumvioletred' => '#C71585', 
        'midnightblue' => '#191970', 
        'mintcream' => '#F5FFFA', 
        'mistyrose' => '#FFE4E1', 
        'moccasin' => '#FFE4B5', 
        'navajowhite' => '#FFDEAD', 
        'navy' => '#000080', 
        'oldlace' => '#FDF5E6', 
        'olive' => '#808000', 
        'olivedrab' => '#6B8E23', 
        'orange' => '#FFA500', 
        'orangered' => '#FF4500', 
        'orchid' => '#DA70D6', 
        'palegoldenrod' => '#EEE8AA', 
        'palegreen' => '#98FB98', 
        'paleturquoise' => '#AFEEEE', 
        'palevioletred' => '#DB7093', 
        'papayawhip' => '#FFEFD5', 
        'peachpuff' => '#FFDAB9', 
        'peru' => '#CD853F', 
        'pink' => '#FFC0CB', 
        'plum' => '#DDA0DD', 
        'powderblue' => '#B0E0E6', 
        'purple' => '#800080', 
        'red' => '#FF0000', 
        'rosybrown' => '#BC8F8F', 
        'royalblue' => '#4169E1', 
        'saddlebrown' => '#8B4513', 
        'salmon' => '#FA8072', 
        'sandybrown' => '#F4A460', 
        'seagreen' => '#2E8B57', 
        'seashell' => '#FFF5EE', 
        'sienna' => '#A0522D', 
        'silver' => '#C0C0C0', 
        'skyblue' => '#87CEEB', 
        'slateblue' => '#6A5ACD', 
        'slategray' => '#708090', 
        'slategrey' => '#708090', 
        'snow' => '#FFFAFA', 
        'springgreen' => '#00FF7F', 
        'steelblue' => '#4682B4', 
        'tan' => '#D2B48C', 
        'teal' => '#008080', 
        'thistle' => '#D8BFD8', 
        'tomato' => '#FF6347', 
        'turquoise' => '#40E0D0', 
        'violet' => '#EE82EE', 
        'wheat' => '#F5DEB3', 
        'white' => '#FFFFFF', 
        'whitesmoke' => '#F5F5F5', 
        'yellow' => '#FFFF00', 
        'yellowgreen' => '#9ACD32'
    );

    /**
     * 
     * 属性值里可能含有颜色的属性名
     * @var array
     */
    public static $mayHasColorProperties = array(
        'color' => 1, 
        'background' => 1, 
        'background-color' => 1, 
        'background-image' => 1, 
        'box-shadow' => 1, 
        'text-shadow' => 1, 
        'border' => 1, 
        'border-color' => 1
    );

    /**
     * 
     * short colors
     * @var array
     */
    public static $shortColor = array(
        "black" => "#000", 
        "fuchsia" => "#F0F", 
        "white" => "#FFF", 
        "yellow" => "#FF0", 
        "#800000" => "maroon", 
        "#ffa500" => "orange", 
        "#808000" => "olive", 
        "#800080" => "purple", 
        "#008000" => "green", 
        "#000080" => "navy", 
        "#008080" => "teal", 
        "#c0c0c0" => "silver", 
        "#808080" => "gray", 
        "#f00" => "red", 
        "#ff0000" => "red"
    );

    /**
     * 
     * short font-weight
     * @var array
     */
    public static $shortFontWeight = array(
        "normal" => "400", 
        "bold" => "700"
    );

    /**
     * 
     * rgb pattern
     * @var RegexIterator
     */
    public static $rgbPattern = "/rgb\s*\(\s*(\d+)\s*\,\s*(\d+)\s*\,\s*(\d+)\s*\)/e";

    /**
     * 
     * @ type list
     * @var array
     */
    public static $atTypeList = array(
        FL_TOKEN_CSS_AT => 1, 
        FL_TOKEN_CSS_AT_CHARSET => 1, 
        FL_TOKEN_CSS_AT_FONTFACE => 1, 
        FL_TOKEN_CSS_AT_IMPORT => 1, 
        FL_TOKEN_CSS_AT_KEYFRAMES => 1, 
        FL_TOKEN_CSS_AT_MEDIA => 1, 
        FL_TOKEN_CSS_AT_MOZILLA => 1, 
        FL_TOKEN_CSS_AT_OTHER => 1, 
        FL_TOKEN_CSS_AT_PAGE => 1
    );

    /**
     * 
     * padding 4 children
     * @var array
     */
    public static $paddingChildren = array(
        "padding-top", 
        "padding-right", 
        "padding-bottom", 
        "padding-left"
    );

    /**
     * 
     * margin 4 children
     * @var array
     */
    public static $marginChildren = array(
        'margin-top', 
        'margin-right', 
        'margin-bottom', 
        'margin-left'
    );

    /**
     * 
     * http://www.w3.org/TR/CSS2/propidx.html
     * regular property list
     * @var array
     */
    public static $propertyList = array(
        'azimuth' => true, 
        'background-attachment' => array(
            'scroll', 
            'fixed', 
            'inherit'
        ), 
        'background-color' => array(
            'color', 
            'transparent', 
            'inherit'
        ), 
        'background-image' => array(
            'uri', 
            'none', 
            'inherit'
        ), 
        'background-position' => true, 
        'background-repeat' => array(
            'repeat', 
            'repeat-x', 
            'repeat-y', 
            'no-repeat', 
            'inherit'
        ), 
        'background' => true, 
        'border-collapse' => array(
            'collapse', 
            'separate', 
            'inherit'
        ), 
        'border-color' => true, 
        'border-spacing' => true, 
        'border-style' => true, 
        'border-top' => true, 
        'border-left' => true, 
        'border-right' => true, 
        'border-bottom' => true, 
        'border-top-color' => array(
            'color', 
            'transparent', 
            'inherit'
        ), 
        'border-left-color' => array(
            'color', 
            'transparent', 
            'inherit'
        ), 
        'border-right-color' => array(
            'color', 
            'transparent', 
            'inherit'
        ), 
        'border-bottom-color' => array(
            'color', 
            'transparent', 
            'inherit'
        ), 
        'border-top-style' => true, 
        'border-left-style' => true, 
        'border-right-style' => true, 
        'border-bottom-style' => true, 
        'border-top-width' => true, 
        'border-left-width' => true, 
        'border-right-width' => true, 
        'border-bottom-width' => true, 
        'border-width' => true, 
        'border' => true, 
        'bottom' => true, 
        'caption-side' => array(
            'top', 
            'bottom', 
            'inherit'
        ), 
        'clear' => array(
            'none', 
            'left', 
            'right', 
            'both', 
            'inherit'
        ), 
        'clip' => true, 
        'color' => array(
            'color', 
            'inherit'
        ), 
        'content' => true, 
        'counter-increment' => true, 
        'counter-reset' => true, 
        'cue-after' => array(
            'uri', 
            'none', 
            'inherit'
        ), 
        'cue-before' => array(
            'uri', 
            'none', 
            'inherit'
        ), 
        'cue' => true, 
        'cursor' => true, 
        'direction' => array(
            'ltr', 
            'rtl', 
            'inherit'
        ), 
        'display' => array(
            'inline', 
            'block', 
            'list-item', 
            'inline-block', 
            'table', 
            'inline-table', 
            'table-row-group', 
            'table-header-group', 
            'table-footer-group', 
            'table-row', 
            'table-column-group', 
            'table-column', 
            'table-cell', 
            'table-caption', 
            'none', 
            'inherit'
        ), 
        'elevation' => true, 
        'empty-cells' => array(
            'show', 
            'hide', 
            'inherit'
        ), 
        'float' => array(
            'left', 
            'right', 
            'none', 
            'inherit'
        ), 
        'font-family' => true, 
        'font-size' => true, 
        'font-style' => array(
            'normal', 
            'italic', 
            'oblique', 
            'inherit'
        ), 
        'font-variant' => array(
            'normal', 
            'small-caps', 
            'inherit'
        ), 
        'font-weight' => array(
            'normal', 
            'bold', 
            'bolder', 
            'lighter', 
            100, 
            200, 
            300, 
            400, 
            500, 
            600, 
            700, 
            800, 
            900, 
            'inherit'
        ), 
        'font' => true, 
        'height' => true, 
        'left' => true, 
        'letter-spacing' => true, 
        'line-height' => true, 
        'list-style-image' => array(
            'uri', 
            'none', 
            'inherit'
        ), 
        'list-style-position' => array(
            'inside', 
            'outside', 
            'inherit'
        ), 
        'list-style-type' => array(
            "disc", 
            "circle", 
            "square", 
            "decimal", 
            "decimal-leading-zero", 
            "lower-roman", 
            "upper-roman", 
            "lower-greek", 
            "lower-latin", 
            "upper-latin", 
            "armenian", 
            "georgian", 
            "lower-alpha", 
            "upper-alpha", 
            "none", 
            "inherit"
        ), 
        'list-style' => true, 
        'margin-right' => true, 
        'margin-left' => true, 
        'margin-top' => true, 
        'margin-bottom' => true, 
        'margin' => true, 
        'max-height' => true, 
        'max-width' => true, 
        'min-width' => true, 
        'min-height' => true, 
        'orphans' => true, 
        'outline-color' => array(
            'color', 
            'invert', 
            'inherit'
        ), 
        'outline-style' => true, 
        'outline-width' => true, 
        'outline' => true, 
        'overflow' => array(
            "visible", 
            "hidden", 
            "scroll", 
            "auto", 
            "inherit"
        ), 
        'padding-top' => true, 
        'padding-left' => true, 
        'padding-right' => true, 
        'padding-bottom' => true, 
        'padding' => true, 
        'page-break-after' => array(
            "auto", 
            "always", 
            "avoid", 
            "left", 
            "right", 
            "inherit"
        ), 
        'page-break-before' => array(
            "auto", 
            "always", 
            "avoid", 
            "left", 
            "right", 
            "inherit"
        ), 
        'page-break-inside' => array(
            "avoid", 
            "auto", 
            "inherit"
        ), 
        'pause-after' => true, 
        'pause-before' => true, 
        'pause' => true, 
        'pitch-range' => true, 
        'pitch' => true, 
        'play-during' => true, 
        "position" => array(
            "static", 
            "relative", 
            "absolute", 
            "fixed", 
            "inherit"
        ), 
        'quotes' => true, 
        'richness' => true, 
        'right' => true, 
        'speak-header' => array(
            "once", 
            "always", 
            "inherit"
        ), 
        'speak-numeral' => array(
            "digits", 
            "continuous", 
            "inherit"
        ), 
        'speak-punctuation' => array(
            "code", 
            "none", 
            "inherit"
        ), 
        'speak' => array(
            "normal", 
            "none", 
            "spell-out", 
            "inherit"
        ), 
        'speech-rate' => true, 
        'stress' => true, 
        'table-layout' => array(
            "auto", 
            "fixed", 
            "inherit"
        ), 
        'text-align' => array(
            "left", 
            "right", 
            "center", 
            "justify", 
            "inherit"
        ), 
        'text-decoration' => array(
            "none", 
            "underline", 
            "overline", 
            "line-through", 
            "blink", 
            "inherit"
        ), 
        'text-indent' => true, 
        'text-transform' => array(
            "capitalize", 
            "uppercase", 
            "lowercase", 
            "none", 
            "inherit"
        ), 
        'top' => true, 
        'unicode-bidi' => array(
            "normal", 
            "embed", 
            "bidi-override", 
            "inherit"
        ), 
        'vertical-align' => true, 
        'visibility' => array(
            "visible", 
            "hidden", 
            "collapse", 
            "inherit"
        ), 
        'voice-family' => true, 
        'volume' => true, 
        'white-space' => array(
            "normal", 
            "pre", 
            "nowrap", 
            "pre-wrap", 
            "pre-line", 
            "inherit"
        ), 
        'widows' => true, 
        'width' => true, 
        'word-spacing' => true, 
        'z-index' => array(
            "auto", 
            "integer", 
            "inherit"
        )
    );

    public static $valueKeywords = array(
        "color" => true, 
        "uri" => "", 
        "integer" => ""
    );

    /**
     * 
     * remove comment from text
     * @param string $text
     */
    public static function removeComment ($text = '') {
        $text = preg_replace ( self::$commentPattern, '', $text );
        return $text;
    }

    /**
     * 
     * get @ detail type
     * @param string $text
     */
    public static function getAtDetailType ($text = '', Fl_Base $instance) {
        $text = self::removeComment ( $text );
        foreach (self::$atType as $key => $type) {
            if ($key{0} === '/') {
                if (preg_match ( $key, $text )) {
                    return $type;
                }
            } else {
                if (strpos ( $text, $key ) === 0) {
                    return $type;
                }
            }
        }
        return FL_TOKEN_CSS_AT_OTHER;
    }

    /**
     * 
     * check has break for selector tokenizar
     * @param char $char
     */
    public static function isSelectorCharUtil ($char = '') {
        return isset ( self::$selectorCharUtil[$char] );
    }

    /**
     * 
     * check namespace
     * @param string $text
     */
    public static function checkNamespace ($text = '') {
        return preg_match ( self::$namespacePattern, $text );
    }

    /**
     * 
     * check selector token
     * @param string $type
     * @param string $value
     */
    public static function checkSelectorToken ($type, $value) {
        if (! isset ( self::$selectorTokenPattern[$type] )) {
            return true;
        }
        return preg_match ( self::$selectorTokenPattern[$type], $value );
    }

    /**
     * 
     * compare two selector specificity
     * @param string $score1
     * @param string $score2
     */
    public static function compareSelectorSpecificity ($score1 = array(), $score2 = array()) {
        if (is_string ( $score1 )) {
            $score1 = self::getSelectorSpecificity ( $score1 );
        }
        if (is_string ( $score2 )) {
            $score2 = self::getSelectorSpecificity ( $score2 );
        }
        if (! is_array ( $score1 ) && ! is_array ( $score2 )) {
            if ($score1 === $score2) {
                return 0;
            } else 
                if ($score1 > $score2) {
                    return 1;
                }
            return - 1;
        }
        for ($i = 0, $count = count ( $score1 ); $i < $count; $i ++) {
            $item1 = $score1[$i];
            $item2 = $score2[$i];
            if ($item1 < $item2) {
                return - 1;
            }
            if ($item1 > $item2) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * 
     * Calculating a selector's specificity
     * see more at:
http://www.w3.org/TR/selectors/#specificity
     * @param array $selectorTokens
     */
    public static function getSelectorSpecificity ($selectorTokens = array(), $number = false) {
        if (! is_array ( $selectorTokens )) {
            throw new Fl_Exception ( "selectorTokens must be array", - 1 );
        }
        $score = array(
            0, 
            0, 
            0
        );
        $notPattern = '/^\:not\(/ies';
        foreach ($selectorTokens as $item) {
            $type = $item['type'];
            switch ($type) {
                case FL_TOKEN_CSS_SELECTOR_ID:
                    $score[0] ++;
                    break;
                case FL_TOKEN_CSS_SELECTOR_TYPE:
                case FL_TOKEN_CSS_SELECTOR_PSEUDO_ELEMENT:
                    $score[2] ++;
                    break;
                case FL_TOKEN_CSS_SELECTOR_CLASS:
                case FL_TOKEN_CSS_SELECTOR_ATTRIBUTES:
                    $score[1] ++;
                    break;
                case FL_TOKEN_CSS_SELECTOR_PSEUDO_CLASS:
                    $value = $item['value'];
                    //:not(xxx)
                    if (preg_match ( $notPattern, $value )) {
                        $value = trim ( preg_replace ( $notPattern, "", $value ) );
                        $value = substr ( $value, 0, strlen ( $value ) - 1 );
                        Fl::loadClass ( 'Fl_Css_SelectorToken' );
                        $instance = new Fl_Css_SelectorToken ( $value );
                        $tokens = $instance->run ();
                        $notScore = Fl_Css_Static::getSelectorSpecificity ( $tokens[0] );
                        $score[0] += $notScore[0];
                        $score[1] += $notScore[1];
                        $score[2] += $notScore[2];
                    } else {
                        $score[1] ++;
                    }
                    break;
            }
        }
        if ($number) {
            return $score[0] * 10000 + $score[1] * 100 + $score[2];
        }
        return $score;
    }

    /**
     * 
     * check property valid
     * @param string $property
     */
    public static function checkPropertyPattern ($property = '') {
        return preg_match ( self::$propertyPattern, $property );
    }

    /**
     * 
     * multi same property in a selector
     * @param string $property
     */
    public static function isMultiSameProperty ($property = '') {
        return isset ( self::$multiSameProperty[$property] );
    }

    /**
     * 
     * css selector token to text
     * @param array $tokens
     * @param boolean $embedExtInfo
     */
    public static function selectorTokenToText ($tokens = array(), $embedExtInfo = true) {
        if (count ( $tokens ) === 0) {
            return '';
        }
        if ($embedExtInfo) {
            $line = $tokens[0]['line'];
            $col = $tokens[0]['col'];
            $pos = $tokens[0]['pos'];
        }
        $result = array();
        foreach ($tokens as $token) {
            if ($token['spaceBefore']) {
                $result[] = FL_SPACE;
            }
            $result[] = $token['value'];
        }
        $result = trim ( join ( '', $result ) );
        if ($embedExtInfo) {
            return array(
                'text' => $result, 
                'line' => $line, 
                'pos' => $pos, 
                'col' => $col
            );
        }
        return $result;
    }

    /**
     * 
     * check token type is @ type
     * @param string $type
     */
    public static function isAtType ($type = '') {
        return isset ( self::$atTypeList[$type] );
    }

    /**
     * 
     * get clean property, some has IE hack
     * @param string $property
     */
    public static function getPropertyDetail ($property = '') {
        preg_match ( self::$css3PropertyPrefixPattern, $property, $matches );
        if ($matches) {
            $prefix = $matches[0];
            $value = substr ( $property, strlen ( $prefix ) );
        } else {
            $value = preg_replace ( self::$propertyCharHackPattern, "", $property );
            if ($property === $value) {
                $prefix = '';
            } else {
                $prefix = substr ( $property, 0, strpos ( $property, $value ) );
            }
        }
        return array(
            "prefix" => $prefix, 
            "property" => $value
        );
    }

    /**
     * 
     * get css value detail info
     * contain: ie value hack, !important in value
     * @param string $value
     */
    public static function getValueDetail ($value = '') {
        $important = false;
        $suffix = '';
        $cleanValue = preg_replace ( self::$importantPattern, "", $value );
        if ($cleanValue !== $value) {
            $important = true;
        }
        $cleanValue1 = preg_replace ( self::$ieValueHackPattern, "", $cleanValue );
        if ($cleanValue1 !== $cleanValue) {
            $suffix = substr ( $cleanValue, strlen ( $cleanValue1 ) );
        }
        return array(
            'value' => $cleanValue1, 
            'important' => $important, 
            'suffix' => $suffix
        );
    }

    /**
     * 
     * compress css value
     * @param string $value
     */
    public static function compressCommon ($value = '') {
        //remove comment in value
        $value = self::removeComment ( $value );
        //remove newline in value
        $value = str_replace ( FL_NEWLINE, "", $value );
        //replace multi space to one
        $value = preg_replace ( FL_SPACE_PATTERN, " ", $value );
        //can't replace `, ` to `,`, see http://www.imququ.com/post/the_bug_of_ie-matrix-filter.html
        //$value = str_replace ( ", ", ",", $value );
        //$value = str_replace ( "0 0 0 0", "0", $value );
        //$value = str_replace ( "0 0 0", "0", $value );
        //$value = str_replace ( "0 0", "0", $value );
        //Replace 0(px,em,%) with 0.
        $value = preg_replace ( "/([\s:])(0)(px|em|%|in|cm|mm|pc|pt|ex)/is", "$1$2", $value );
        //Replace 0.6 to .6
        $value = trim ( preg_replace ( "/\s0\.(\d+)/is", " .$1", ' ' . $value ) );
        // Shorten colors from #AABBCC to #ABC. Note that we want to make sure
        // the color is not preceded by either ", " or =. Indeed, the property
        //     filter: chroma(color="#FFFFFF");
        // would become
        //     filter: chroma(color="#FFF");
        // which makes the filter break in IE.
        $value = preg_replace ( "/([^\"'=\s])(\s*)#([0-9a-fA-F])\\3([0-9a-fA-F])\\4([0-9a-fA-F])\\5/is", 
                "$1$2#$3$4$5", $value );
        return $value;
    }

    /**
     * 
     * sort properties
     * @param array $attrs
     * @param array $b
     */
    public static function sortProperties ($attrs = array(), $b = null) {
        if ($b) {
            $ap = strtolower ( $attrs['property'] );
            $bp = strtolower ( $b['property'] );
            if ($ap === $bp || $ap === 'filter' || $bp === 'filter') {
                return $attrs['pos'] > $b['pos'] ? 1 : - 1;
            } else {
                foreach (self::$unSortNames as $name) {
                    if (( strpos ( $ap, $name . '-' ) === 0 || $name === $ap ) && ( strpos ( $bp, $name . '-' ) === 0 || $name === $bp )) {
                        return $attrs['pos'] > $b['pos'] ? 1 : - 1;
                    }
                }
                return strcmp ( $ap, $bp ) > 0 ? 1 : - 1;
            }
        } else {
            uasort ( $attrs, "Fl_Css_Static::sortProperties" );
            return $attrs;
        }
    }

    /**
     * 
     * sort selectors
     * @param array $selectors
     * @param array or null $b
     */
    public static function sortSelectors ($selectors, $b = null) {
        if ($b) {
            if ($selectors['score'] === $b['score']) {
                return $selectors['pos'] > $b['pos'] ? 1 : - 1;
            } else 
                if ($selectors['score'] > $b['score']) {
                    return 1;
                }
            return - 1;
        } else {
            uasort ( $selectors, "Fl_Css_Static::sortSelectors" );
            return $selectors;
        }
    }

    /**
     * 
     * combine padding value, such as: padding & margin
     * @param array $attrs
     */
    public static function combineProperty ($attrs = array(), $primary = '', $list = array()) {
        $properties = array(
            $primary => 0
        );
        foreach ($list as $item) {
            $properties[$item] = 0;
        }
        foreach ($attrs as $name => $item) {
            if (isset ( $properties[$item['property']] )) {
                if ($item['important'] || $item['prefix'] || $item['suffix']) {
                    return $attrs;
                } else {
                    $properties[$name] = 1;
                }
            }
            //if css hack in attrs, can't combine it
            if ($item['type'] === FL_TOKEN_CSS_HACK) {
                return $attrs;
            }
        }
        if ($properties[$primary]) {
            $value = $attrs[$primary]['value'];
            $append = array();
            foreach ($list as $k => $item) {
                if ($properties[$item]) {
                    $append[$k] = $attrs[$item]['value'];
                    unset ( $attrs[$item] );
                }
            }
            $attrs[$primary]['value'] = self::short4NumValue ( $value, $append );
            return $attrs;
        } else {
            $value = array();
            $copyAttrs = $attrs;
            foreach ($list as $k => $item) {
                if (! $properties[$item]) {
                    return $attrs;
                } else {
                    $value[$k] = $copyAttrs[$item]['value'];
                    unset ( $copyAttrs[$item] );
                }
            }
            $attrs = $copyAttrs;
            $attrs[$primary] = array(
                'property' => $primary, 
                'value' => self::short4NumValue ( $value )
            );
        }
        return $attrs;
    }

    /**
     * 
     * short for padding,margin,border-color value
     * @param string $value
     */
    public static function short4NumValue ($value = '', $append = array(), $returnArray = false) {
        if (! is_array ( $value )) {
            $value = preg_split ( FL_SPACE_PATTERN, $value );
        }
        $count = count ( $value );
        $v = array(
            "1" => array(
                0, 
                0, 
                0, 
                0
            ), 
            "2" => array(
                0, 
                1, 
                0, 
                1
            ), 
            "3" => array(
                0, 
                1, 
                2, 
                1
            ), 
            "4" => array(
                0, 
                1, 
                2, 
                3
            )
        );
        $sv = $v[strval ( $count )];
        $value = array(
            $value[$sv[0]], 
            $value[$sv[1]], 
            $value[$sv[2]], 
            $value[$sv[3]]
        );
        foreach ($append as $k => $v) {
            $value[$k] = $v;
        }
        if ($value[1] === $value[3]) {
            unset ( $value[3] );
        }
        if (count ( $value ) === 3 && $value[0] === $value[2]) {
            unset ( $value[2] );
        }
        if (count ( $value ) === 2 && $value[0] === $value[1]) {
            unset ( $value[1] );
        }
        if ($returnArray) {
            return $value;
        }
        return trim ( join ( FL_SPACE, $value ) );
    }

    /**
     * 
     * get short value
     * @param string $value
     * @param string $property
     */
    public static function getShortValue ($value, $property) {
        //http://www.w3schools.com/cssref/pr_border-width.asp
        if ($property === 'border-color' || $property === 'border-style' || $property === 'border-width') {
            return self::short4NumValue ( $value );
        }
        $list = array(
            "color" => self::$shortColor, 
            "border-top-color" => self::$shortColor, 
            "border-left-color" => self::$shortColor, 
            "border-right-color" => self::$shortColor, 
            "border-bottom-color" => self::$shortColor, 
            "background-color" => self::$shortColor, 
            "font-weight" => self::$shortFontWeight
        );
        // rgb(0,0,0) -> #000000 (or #000 in this case later)
        $value = self::rgb2Hex ( $value );
        if (isset ( $list[$property] )) {
            $mix = $list[$property];
            return isset ( $mix[$value] ) ? $mix[$value] : $value;
        }
        return $value;
    }

    /**
     * 
     * rgb to hex
     * @param string $value
     */
    public static function rgb2Hex ($value, $r = 0, $g = 0, $b = 0) {
        if ($value === true) {
            $v = array(
                intval ( $r ), 
                intval ( $g ), 
                intval ( $b )
            );
            $result = '#';
            foreach ($v as $item) {
                if ($item < 16) {
                    $result .= '0' . dechex ( $item );
                } else {
                    $result .= dechex ( $item );
                }
            }
            return $result;
        }
        if (strpos ( $value, "rgb" ) === false) {
            return $value;
        }
        $replace = "self::rgb2hex(true, '\\1', '\\2', '\\3')";
        $value = preg_replace ( self::$rgbPattern, $replace, $value );
        return $value;
    }

    /**
     * 
     * merge property
     * @param array $attrs1
     * @param array $attrs2
     */
    public static function mergeProperties ($attrs1 = array(), $attrs2 = array()) {
        foreach ($attrs2 as $name => $item) {
            if (isset ( $attrs1[$name] )) {
                if (! $attrs1[$name]['important'] || $item['important']) {
                    //can't not replace it
                    unset ( $attrs1[$name] );
                    $attrs1[$name] = $item;
                }
            } else {
                $attrs1[$name] = $item;
            }
        }
        return $attrs1;
    }

    /**
     * 
     * check properties equal
     * @param array $attrs1
     * @param array $attrs2
     */
    public static function checkPropertiesEqual ($attrs1, $attrs2) {
        if ($attrs1['prefix'] || $attrs1['suffix'] || $attrs2['prefix'] || $attrs2['suffix']) {
            return - 1;
        }
        if ($attrs1['type'] === FL_TOKEN_CSS_HACK || $attrs2['type'] === FL_TOKEN_CSS_HACK) {
            return - 1;
        }
        unset ( $attrs1['pos'], $attrs2['pos'], $attrs1['equal'], $attrs2['equal'] );
        #return strcmp ( json_encode ( $attrs1 ), json_encode ( $attrs2 ) );
        //use serialize to compare it
        return strcmp ( serialize ( $attrs1 ), serialize ( $attrs2 ) );
    }

    /**
     * 
     * get properties intersect
     * @param array $se1
     * @param array $se2
     */
    public static function getPropertiesIntersect ($se1 = array(), $se2 = array()) {
        $attrs1 = $se1['attrs'];
        $attrs2 = $se2['attrs'];
        $assoc = array_uintersect_assoc ( $attrs1, $attrs2, "Fl_Css_Static::checkPropertiesEqual" );
        //if intersect attrs has hack attr, remove it
        foreach ($assoc as $name => $item) {
            if (preg_match ( self::$multiSamePropertyPattern, $name )) {
                unset ( $assoc[$name] );
                continue;
            }
            $flag = false;
            foreach (self::$unSortNames as $itemName) {
                if ($itemName == $name || strpos ( $itemName . '-', $name ) !== false) {
                    $flag = true;
                    break;
                }
            }
            foreach ($attrs1 as $n1 => $i1) {
                if ($i1['property'] == $item['property'] && ( $i1['prefix'] || $i1['suffix'] )) {
                    unset ( $assoc[$name] );
                    break;
                }
                if ($i1['type'] === FL_TOKEN_CSS_HACK) {
                    return false;
                }
                if ($flag) {
                    if (strpos ( $name, '-' ) !== false) {
                        if (strpos ( $name, $i1['property'] . '-' ) !== false) {
                            unset ( $assoc[$name] );
                            break;
                        }
                    } else {
                        if (strpos ( $i1['property'], $name . '-' ) !== false) {
                            unset ( $assoc[$name] );
                            break;
                        }
                    }
                }
            }
            foreach ($attrs2 as $n1 => $i1) {
                if ($i1['property'] == $item['property'] && ( $i1['prefix'] || $i1['suffix'] )) {
                    unset ( $assoc[$name] );
                    break;
                }
                if ($i1['type'] === FL_TOKEN_CSS_HACK) {
                    return false;
                }
                if ($flag) {
                    if (strpos ( $name, '-' ) !== false) {
                        if (strpos ( $name, $i1['property'] . '-' ) !== false) {
                            unset ( $assoc[$name] );
                            break;
                        }
                    } else {
                        if (strpos ( $i1['property'], $name . '-' ) !== false) {
                            unset ( $assoc[$name] );
                            break;
                        }
                    }
                }
            }
        }
        if (empty ( $assoc )) {
            return false;
        }
        $assCount = count ( $assoc );
        if (count ( $attrs1 ) != $assCount && count ( $attrs2 ) !== $assCount) {
            // 3 chars is `, { }`
            $seLen = strlen ( $se1['selector'] ) + strlen ( $se2['selector'] ) + 3;
            $se1Equal = strlen ( join ( ',', $se1['equal'] ) );
            $se2Equal = strlen ( join ( ',', $se2['equal'] ) );
            if ($se1Equal) {
                $seLen += $se1Equal + 1;
            }
            if ($se2Equal) {
                $seLen += $se2Equal + 1;
            }
            $assLen = 0;
            foreach ($assoc as $item) {
                //2 chars is : and ;
                $assLen += strlen ( $item['prefix'] . $item['property'] . $item['value'] . $item['suffix'] ) + 2;
                //if have important in value, add `!important` length(10)
                if ($item['important']) {
                    $assLen += 10;
                }
            }
            $assLen --;
            //if combine selector length more than combine attrs, can't not combine them
            if ($seLen >= $assLen) {
                return false;
            }
        }
        return $assoc;
    }

    /**
     * 
     * combine same selector
     * @param array $selectors
     */
    public static function combineSameSelector ($selectors = array()) {
        $result = array();
        $preLongSelector = '';
        foreach ($selectors as $item) {
            $longSelector = trim ( $item['selector'] . ',' . join ( ',', $item['equal'] ), ',' );
            if ($longSelector === $preLongSelector) {
                $last = array_pop ( $result );
                $last['attrs'] = self::mergeProperties ( $last['attrs'], $item['attrs'] );
                $result[] = $last;
            } else {
                $result[] = $item;
            }
            $preLongSelector = $longSelector;
        }
        return $result;
    }

    /**
     * 
     * get prefix and suffix in style
     * @param string $value
     */
    public static function getStyleDetail ($value = '') {
        $value = trim ( $value );
        $prefix = $suffix = $text = '';
        foreach (self::$stylePrefixAndSuffix as $item) {
            if (strpos ( $value, $item[0] ) === 0) {
                $pos = strrpos ( $value, $item[1] );
                if ($pos == ( strlen ( $value ) - strlen ( $item[1] ) )) {
                    $prefix = $item[0];
                    $suffix = $item[1];
                    $value = trim ( substr ( $value, strlen ( $item[0] ), ( strlen ( $value ) - strlen ( $item[0] ) - strlen ( $item[1] ) ) ) );
                    break;
                }
            }
        }
        return array(
            "prefix" => $prefix, 
            "suffix" => $suffix, 
            "value" => $value
        );
    }

    /**
     * 
     * 将彩色变为灰色
     * 计算公式为：Gray = R*0.299 + G*0.587 + B*0.114
     * http://wsyjwps1983.blog.163.com/blog/static/6800900120091124324820/
     * @param string $color
     */
    public static function colorToGray ($color = '') {
        $isArray = is_array ( $color );
        if (is_string ( $color )) {
            $color = trim ( $color );
            if (isset ( Fl_Css_Static::$colorKeywords[$color] )) {
                $color = Fl_Css_Static::$colorKeywords[$color];
            }
            if (substr ( $color, 0, 1 ) == '#') {
                $color = substr ( $color, 1 );
                if (strlen ( $color ) == 3) {
                    $color = $color{0} . $color{0} . $color{1} . $color{1} . $color{2} . $color{2};
                }
                $color = hexdec ( $color );
                $R = 0xFF & ( $color >> 0x10 );
                $G = 0xFF & ( $color >> 0x8 );
                $B = 0xFF & $color;
                $color = array(
                    'R' => $R, 
                    'G' => $G, 
                    'B' => $B
                );
            } else {
                return $color;
            }
        }
        $gray = intval ( $color['R'] * 0.299 + $color['G'] * 0.587 + $color['B'] * 0.114 );
        $color['R'] = $color['G'] = $color['B'] = $gray;
        if ($isArray) {
            return $color;
        }
        $gray = dechex ( $gray );
        if (strlen ( $gray ) == 1) {
            $gray = '0' . $gray;
        }
        return '#' . $gray . $gray . $gray;
    }

    /**
     * 
     * token值里可能含有color
     * @param array $token
     */
    public static function mayHasColor ($token) {
        if ($token['type'] != FL_TOKEN_CSS_PROPERTY) {
            return false;
        }
        $value = $token['value'];
        $detail = Fl_Css_Static::getPropertyDetail ( $value );
        $name = strtolower ( $detail['property'] );
        if (isset ( Fl_Css_Static::$mayHasColorProperties[$name] )) {
            return true;
        }
        return false;
    }

    /**
     * 
     * 判断是否是个颜色值
     * @param string $color
     */
    public static function isColor ($color) {
        $color = trim ( strtolower ( $color ) );
        //颜色关键字
        if (isset ( self::$colorKeywords[$color] )) {
            return true;
        }
        //#xxxxxx
        if (preg_match ( "/^\#([0-9a-f]{3}|[0-9a-f]{6})$/", $color )) {
            return true;
        }
        //rgb(0,0,0)
        $rgbPattern = "/^rgb\s*\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*\)$/";
        if (preg_match ( $rgbPattern, $color )) {
            return true;
        }
        //rgb(0,0,0,0)
        $rgbaPattern = "/^rgba\s*\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*,\s*[\d\.]+\s*\)$/";
        if (preg_match ( $rgbaPattern, $color )) {
            return true;
        }
        if ($color === 'transparent') {
            return true;
        }
        return false;
    }

    /**
     * 
     * 是否是个background-repeat值
     * @param string $str
     */
    public static function isBackgroundRepeat ($value = "") {
        $value = strtolower ( $value );
        $repeats = array(
            'repeat', 
            'repeat-x', 
            'repeat-y', 
            'no-repeat'
        );
        return in_array ( $value, $repeats );
    }

    /**
     * 
     * 是否是个百分比的值
     * @param string $value
     */
    public static function isPercentValue ($value = "") {
        $pattern = "/^(\d+\.)?(\d+)\%$/";
        return preg_match ( $pattern, $value );
    }

    /**
     * 
     * 是否是个长度值
     * @param string $value
     */
    public static function isLengthValue ($value = "") {
        $value = strtolower ( $value );
        $pattern = "/^((\-?(\d+\.)?(\d+)(px|em))|0)$/";
        return preg_match ( $pattern, $value );
    }

    /**
     * 
     * 是否是个URL
     * 如果是，则解析里面对应的URL
     * @param string $value
     */
    public static function isUrlValue ($value = "") {
        $value = trim ( $value );
        $pattern = '/^url\s*\(\s*([\'\"]?)([^\'\"\)]+)\\1\s*\)$/i';
        if (preg_match ( $pattern, $value, $match )) {
            return $match[2];
        }
        return false;
    }

    /**
     * 
     * 是否是background-position的值
     * background-position值为：
     * top left
     * top center
     * top right
     * center left
     * center center
     * center right
     * bottom left
     * bottom center
     * bottom right
     * x% y%
     * xpos ypos
     * 
     * @param string $value
     */
    public static function isBackgroundPosition ($value = "") {
        $values = array(
            'top', 
            'left', 
            'bottom', 
            'right', 
            'center'
        );
        if (in_array ( $value, $values )) {
            return true;
        }
        if (self::isPercentValue ( $value )) {
            return true;
        }
        if (self::isLengthValue ( $value )) {
            return true;
        }
        return false;
    }

    /**
     * 
     * 设置背景图的固定方式
     * @param string $value
     */
    public static function isBackgroundAttachment ($value = "") {
        $values = array(
            "scroll", 
            "fixed"
        );
        $value = strtolower ( $value );
        return in_array ( $value, $values );
    }
}
