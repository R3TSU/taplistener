<?php

namespace Erelyr;

use PHPUnit\Framework\TestListener;

class TapListener extends PHPUnit\Util\Printer implements TestListener
{

    /**
     * @var int
     */
    protected $testNumber = 0;

    /**
     * @var int
     */
    protected $testSuiteLevel = 0;

    /**
     * @var bool
     */
    protected $testSuccessful = true;

    /**
     * Constructor.
     *
     * @param mixed $out
     *
     * @throws PHPUnit\Framework\Exception
     */
    public function __construct($out = null)
    {
        parent::__construct($out);
        $this->write("TAP version 13\n");
    }


    /**
     * An error occurred.
     *
     * @param PHPUnit\Framework\Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addError(PHPUnit\Framework\Test $test, Exception $e, $time)
    {
        $this->writeNotOk($test, 'Error');
    }

    /**
     * A warning occurred.
     *
     * @param PHPUnit\Framework\Test    $test
     * @param PHPUnit\Framework\Warning $e
     * @param float                     $time
     */
    public function addWarning(PHPUnit\Framework\Test $test, PHPUnit\Framework\Warning $e, $time)
    {
        $this->writeNotOk($test, 'Warning');
    }

    /**
     * A failure occurred.
     *
     * @param PHPUnit\Framework\Test                 $test
     * @param PHPUnit\Framework\AssertionFailedError $e
     * @param float                                  $time
     */
    public function addFailure(PHPUnit\Framework\Test $test, PHPUnit\Framework\AssertionFailedError $e, $time)
    {
        $this->writeNotOk($test, 'Failure');
        $message    = explode(
            "\n",
            PHPUnit\Framework\TestFailure::exceptionToString($e)
        );
        $diagnostic = [
            'message'  => $message[0],
            'severity' => 'fail',
        ];
        if ($e instanceof PHPUnit\Framework\ExpectationFailedException) {
            $cf = $e->getComparisonFailure();
            if ($cf !== null) {
                $diagnostic['data'] = [
                    'got'      => $cf->getActual(),
                    'expected' => $cf->getExpected(),
                ];
            }
        }
        $yaml = new Symfony\Component\Yaml\Dumper;
        $this->write(
            sprintf(
                "  ---\n%s  ...\n",
                $yaml->dump($diagnostic, 2, 2)
            )
        );
    }

    /**
     * Incomplete test.
     *
     * @param PHPUnit\Framework\Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addIncompleteTest(PHPUnit\Framework\Test $test, Exception $e, $time)
    {
        $this->writeNotOk($test, '', 'TODO Incomplete Test');
    }

    /**
     * Risky test.
     *
     * @param PHPUnit\Framework\Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addRiskyTest(PHPUnit\Framework\Test $test, Exception $e, $time)
    {
        $this->write(
            sprintf(
                "ok %d - # RISKY%s\n",
                $this->testNumber,
                $e->getMessage() != '' ? ' ' . $e->getMessage() : ''
            )
        );
        $this->testSuccessful = false;
    }

    /**
     * Skipped test.
     *
     * @param PHPUnit\Framework\Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addSkippedTest(PHPUnit\Framework\Test $test, Exception $e, $time)
    {
        $this->write(
            sprintf(
                "ok %d - # SKIP%s\n",
                $this->testNumber,
                $e->getMessage() != '' ? ' ' . $e->getMessage() : ''
            )
        );
        $this->testSuccessful = false;
    }

    /**
     * A testsuite started.
     *
     * @param PHPUnit\Framework\TestSuite $suite
     */
    public function startTestSuite(PHPUnit\Framework\TestSuite $suite)
    {
        $this->testSuiteLevel++;
    }
    /**
     * A testsuite ended.
     *
     * @param PHPUnit\Framework\TestSuite $suite
     */
    public function endTestSuite(PHPUnit\Framework\TestSuite $suite)
    {
        $this->testSuiteLevel--;
        if ($this->testSuiteLevel == 0) {
            $this->write(sprintf("1..%d\n", $this->testNumber));
        }
    }
    /**
     * A test started.
     *
     * @param PHPUnit\Framework\Test $test
     */
    public function startTest(PHPUnit\Framework\Test $test)
    {
        $this->testNumber++;
        $this->testSuccessful = true;
    }
    /**
     * A test ended.
     *
     * @param PHPUnit\Framework\Test $test
     * @param float                  $time
     */
    public function endTest(PHPUnit\Framework\Test $test, $time)
    {
        if ($this->testSuccessful === true) {
            $this->write(
                sprintf(
                    "ok %d - %s\n",
                    $this->testNumber,
                    PHPUnit\Util\Test::describe($test)
                )
            );
        }
        $this->writeDiagnostics($test);
    }

    /**
     * @param PHPUnit\Framework\Test $test
     * @param string                 $prefix
     * @param string                 $directive
     */
    protected function writeNotOk(PHPUnit\Framework\Test $test, $prefix = '', $directive = '')
    {
        $this->write(
            sprintf(
                "not ok %d - %s%s%s\n",
                $this->testNumber,
                $prefix != '' ? $prefix . ': ' : '',
                PHPUnit\Util\Test::describe($test),
                $directive != '' ? ' # ' . $directive : ''
            )
        );
        $this->testSuccessful = false;
    }

    /**
     * @param PHPUnit\Framework\Test $test
     */
    private function writeDiagnostics(PHPUnit\Framework\Test $test)
    {
        if (!$test instanceof PHPUnit\Framework\TestCase) {
            return;
        }
        if (!$test->hasOutput()) {
            return;
        }
        foreach (explode("\n", trim($test->getActualOutput())) as $line) {
            $this->write(
                sprintf(
                    "# %s\n",
                    $line
                )
            );
        }
    }
}