# Symfony Temporal Bundle

This is a package for the [official PHP SDK](https://github.com/temporalio/sdk-php) with Workflow And Activity Registry and full-configurable worker and workflow client. 
Used repositories: [roadrunner-bundle](https://github.com/Baldinof/roadrunner-bundle), [temporal-bundle](https://github.com/highcoreorg/temporal-bundle), [temporal-bundle-vanta](https://github.com/VantaFinance/temporal-bundle)

## Requirements:

- php >= 8.1
- symfony >= 6.0

## Installation

Use this command to install
`composer require atantares/temporal-bundle`

## Usage

Example configuration:
```yaml
# config/packages/temporal.yaml
temporal:
  defaultClient: default
  pool:
    dataConverter: temporal.data_converter
    roadrunnerRPC: '%env(RR_RPC)%'

  workers:
    default:
      taskQueue: default
      exceptionInterceptor: temporal.exception_interceptor
      finalizers:
        - temporal.doctrine_ping_connection_default.finalizer
        - temporal.doctrine_clear_entity_manager.finalizer
      interceptors:
        - temporal.doctrine_ping_connection_default_activity_inbound.interceptor

  clients:
    default:
      namespace: default
      address: '%env(TEMPORAL_ADDRESS)%'
      dataConverter: temporal.data_converter
    cloud:
      namespace: default
      address: '%env(TEMPORAL_ADDRESS)%'
      dataConverter: temporal.data_converter
      clientKey: '%env(TEMPORAL_CLIENT_KEY_PATH)%'
      clientPem: '%env(TEMPORAL_CLIENT_CERT_PATH)%'
```

Doctrine integrations

If [`DoctrineBundle`](https://github.com/doctrine/DoctrineBundle) is use, the following finalizer is available to you:

- `temporal.doctrine_ping_connection_<entity-mananger-name>.finalizer`
- `temporal.doctrine_clear_entity_manager.finalizer`

And interceptors:
- `temporal.doctrine_ping_connection_<entity-mananger-name>_activity_inbound.interceptor`

Create rr.yaml:
```yaml
version: "3"

server:
  command: "php public/index.php"
  env:
    - APP_RUNTIME: Atantares\TemporalBundle\Runtime\TemporalRuntime

temporal:
  address: "temporal:7233"
  namespace: 'default' # Configure a temporal namespace (you must create a namespace manually or use the default namespace named "default")
  activities:
    num_workers: 4 # Set up your worker count

# Set up your values
logs:
  mode: production
  output: stdout
  err_output: stderr
  encoding: json
  level: error

rpc:
  listen: tcp://0.0.0.0:6001
```

**Workflow example:**

```php
<?php

declare(strict_types=1);

namespace App\Workflow;

use Vanta\Integration\Symfony\Temporal\Attribute\AssignWorker;
use Temporal\Workflow\WorkflowInterface;

#[AssignWorker(name: 'worker1')]
#[WorkflowInterface]
final class MoneyTransferWorkflow
{
    #[WorkflowMethod]
    public function transfer(...): \Generator;

    #[SignalMethod]
    function withdraw(): void;

    #[SignalMethod]
    function deposit(): void;
}
```

**Activity example:**

```php
<?php

declare(strict_types=1);

namespace App\Workflow;

use Vanta\Integration\Symfony\Temporal\Attribute\AssignWorker;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

#[AssignWorker(name: 'worker1')]
#[ActivityInterface(...)]
final class MoneyTransferActivity
{
    #[ActivityMethod]
    public function transfer(...): int;

    #[ActivityMethod]
    public function cancel(...): bool;
}
```

More php examples you can find [here](https://github.com/temporalio/samples-php).

Now you can start workers:
```bash
rr serve rr.yaml
```

## Credits

- [Official Temporal PHP SDK](https://github.com/temporalio/sdk-php)
- [Official Temporal PHP Samples](https://github.com/temporalio/samples-php)
- [Symfony Framework](https://github.com/symfony/symfony)

## License

MIT License

Copyright (c) 2024 Atantares

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

