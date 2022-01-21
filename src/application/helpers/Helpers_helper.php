<?php
if (! function_exists('dd')) {
    function dd()
    {
        foreach (func_get_args() as $arg) {
            $style = 'line-height: 123%; font-size: 9pt';
            if (is_array($arg) || is_object($arg)) {
                $style = 'line-height: 123%; font-size: 9pt; color: #006699; z-index: 999999';
            }
            echo "<pre style='$style'>";
            var_dump($arg);
            echo '</pre>';
        }
        die;
    }
}
