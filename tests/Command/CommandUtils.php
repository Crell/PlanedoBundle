<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

trait CommandUtils
{
    /**
     * Convenience wrapper to execute this test's command.
     *
     * @param Application $application
     * @param array       $args
     * @param array       $inputs
     * @param bool        $expectPass
     *
     * @return CommandTester
     */
    protected function executeCommand(Application $application, array $args = [], array $inputs = [], bool $expectPass = true): CommandTester
    {
        $command = $application->find(self::Command);
        $commandTester = new CommandTester($command);
        if ($inputs) {
            $commandTester->setInputs($inputs);
        }
        $commandTester->execute($args);

        if ($expectPass) {
            $commandTester->assertCommandIsSuccessful();
        }

        return $commandTester;
    }
}
