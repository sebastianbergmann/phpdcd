<?php


class Animal
{
    function hasHead()
    {
        return TRUE;
    }
}

class FurryAnimal extends Animal
{
    function hasFur()
    {
        return TRUE;
    }
}

class Rabbit extends FurryAnimal
{
    function isCute()
    {
        return TRUE;
    }
}


$r = new Rabbit();
$r->hasHead();
$r->hasFur();
$r->isCute();