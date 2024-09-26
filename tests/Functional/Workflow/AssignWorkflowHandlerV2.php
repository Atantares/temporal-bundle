<?php

declare(strict_types=1);

namespace Atantares\TemporalBundle\Test\Functional\Workflow;

use Temporal\Workflow\WorkflowInterface as Workflow;
use Atantares\TemporalBundle\Attribute\AssignWorker;

#[AssignWorker('bar')]
#[Workflow]
final class AssignWorkflowHandlerV2 implements AssignWorkflowV2
{
}
