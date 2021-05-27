<?php

namespace Miniature\Component\Tests\TestEnv\Components;

use Miniature\Component\Component;

class BComponent extends Component {
    protected static ?Component $instance = null;
}
