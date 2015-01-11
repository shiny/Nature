<?php
if(isset($app) && is_callable([$app, 'run'])) {
    $app->run();
} else {
    foreach (get_defined_vars() as $var) {
        if(is_a($var, 'nature') && is_callable([$var, 'run'])) {
            $var->run();
        }
    }
}