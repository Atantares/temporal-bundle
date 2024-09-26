<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\Test\Functional\Workflow;

use Temporal\Workflow\WorkflowInterface as Workflow;

#[Workflow]
final class NullWorkflowHandler implements NullWorkflow
{
}
