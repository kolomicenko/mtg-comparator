<?php

require_once '../core.php';
require_once 'worker.php';

(new Rytir_Worker())->process();