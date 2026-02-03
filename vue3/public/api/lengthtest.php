<?php

$i = 200;
    $seconds = 0;

    while ($i > 0) {
        $i--;

        sleep(1);
        $seconds++;
        echo $seconds;
        echo "<br>";
        ob_flush();
        flush();
    }
