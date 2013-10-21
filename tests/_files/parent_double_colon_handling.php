<?php

abstract class Toy
{
    public function ping()
    {
        return 'pong';
    }
}

class Ball extends Toy
{
    public function roll()
    {
        parent::ping();
        return 'rolling';
    }
}

$b = new Ball();
$b->roll();
