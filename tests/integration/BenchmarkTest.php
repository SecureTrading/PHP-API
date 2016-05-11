<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Integration;

class BenchmarkTest extends \Securetrading\Unittest\IntegrationtestAbstract {
  public function testBenchmark() {
    $outputLines = array(); # After exec() this could be parsed to use in assertions on the time/memory usage.
    $returnStatus = null;
    $cmd = "vendor/bin/phpbench run benchmarks/ApiBench.php --subject benchFullApi --report='{\"name\":\"console_table\",\"memory\":true}' --parameters='{\"max_length_of_test_in_seconds\":60,\"transactions_to_process\":500}'";

    exec($cmd, $outputLines, $returnStatus);

    $this->assertEquals(0, $returnStatus); # Will be 1 if an exception was thrown from PHPBench.
  }
}