<?php

abstract class Painting
{

    abstract public function getColors();

    abstract public function getPrice();

    public function getShape()
    {
        return 'rectangle';
    }

}
