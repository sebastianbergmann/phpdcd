<?php

class Interpolator
{

    public function methodFoo()
    {
        $data = array('green', 'red');
        return "first color: {$data[0]}, second color: {$data[1]}";
    }

    public function methodBar()
    {
        $color = 'blue';
        return "play the ${color}s";
    }

    public function methodBazBaz()
    {
        return 'yellow';
    }

}
