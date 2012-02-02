<?php
class Test {
    private function getClass() {
        return new stdClass();
    }

    public function callClass() {
        $this->getClass()->test = 'a';
    }
}

$a = new Test();
$a->callClass();
