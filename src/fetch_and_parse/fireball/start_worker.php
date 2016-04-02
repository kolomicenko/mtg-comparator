<?php

require_once '../core.php';
require_once 'worker.php';

(new Fireball_Worker())->process();