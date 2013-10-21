<?php

class Klass{
    function doSomething()
    {
    }
}

function doSomething()
{
}

function main()
{
    global $x;
    $x->doSomething();
}

$x = new Klass();
main();

