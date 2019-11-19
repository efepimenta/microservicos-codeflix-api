<?php


namespace Tests\Traits;


trait TestProd
{

    protected function skipTestIfNotInProd($message = ''){
        if (!$this->isInProd()) {
            $this->markTestSkipped($message);
        }
    }

    protected function isInProd() {
        return env('TESTING_PROD') !== false;
    }

}
