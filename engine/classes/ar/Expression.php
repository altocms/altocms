<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

namespace alto\engine\ar;
use \E as E, \F as F, \C as C;

/**
 * Class Expression
 *
 * @package alto\engine\ar
 */
class Expression extends \ArrayObject {

    public function __construct($sText) {

        parent::__construct(array('text' => $sText));
    }
}

// EOF