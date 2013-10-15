<?php

class Animal
{
    public function hasHead()
    {
        return true;
    }
}

class Rabbit extends Animal
{
    public function hasFur()
    {
        return true;
    }

    public function eatsCarrots()
    {
        return true;
    }
}

$r = new Rabbit();

$h = $r->hasHead();
$f = $r->hasFur();
