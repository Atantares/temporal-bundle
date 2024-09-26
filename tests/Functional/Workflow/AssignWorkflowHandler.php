<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\Test\Functional\Workflow;

use Temporal\Workflow\WorkflowInterface as Workflow;
use Atantares\TemporalBundle\Attribute\AssignWorker;

#[AssignWorker('foo')]
#[Workflow]
final class AssignWorkflowHandler implements AssignWorkflow
{
}
