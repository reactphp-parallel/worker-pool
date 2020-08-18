# Worker Pool

[![Build Status](https://travis-ci.com/reactphp-parallel/worker-pool.png)](https://travis-ci.com/reactphp-parallel/worker-pool)
[![Latest Stable Version](https://poser.pugx.org/react-parallel/worker-pool/v/stable.png)](https://packagist.org/packages/react-parallel/worker-pool)
[![Total Downloads](https://poser.pugx.org/react-parallel/worker-pool/downloads.png)](https://packagist.org/packages/react-parallel/worker-pool)
[![License](https://poser.pugx.org/react-parallel/worker-pool/license.png)](https://packagist.org/packages/react-parallel/worker-pool)

Create pool with only one specific designated task

## Install ##

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `~`.

```
composer require react-parallel/worker-pool
```

## Usage ##

```php
<?php

use Money\Money;
use React\EventLoop\Factory;
use ReactParallel\Factory as ParallelFactory;
use ReactParallel\Pool\Worker\Workers\ReturnWorkerFactory;
use ReactParallel\Pool\Worker\Workers\WorkObject;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

$loop = Factory::create();
$parallelFactory = new ParallelFactory($loop);
$workerFactory = new ReturnWorkerFactory();

$pool = new \ReactParallel\Pool\Worker\Worker($parallelFactory, $workerFactory, 133);
$pool->perform(new WorkObject(Money::EUR(512)))->always(function () use ($pool) {
    $pool->close();
})->done(function (Money $money) {
    echo $money->getAmount();
});

$loop->run();
```

## Creating your own worker ##

A worker consists of two classes, a factory and a worker created by the factory. Due to the nature `ext-parallel` works
it is expensive to transfer classes and scalars to threads. Workers, are designed to reuse that same thread for as long
as there is work. To get an idea of how it works lets have a look at the return worker. First we create the factory,
which is a data transfer object to kickstart from the main thread to the worker thread. Anything you put into it will
be sent to the worker thread. The less you put in there the better. Once the factory is send to the thread the
`construct` method on it will be called to create the actual worker.

```php
<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Workers;

use ReactParallel\Pool\Worker\Work\Worker as WorkerInterface;
use ReactParallel\Pool\Worker\Work\WorkerFactory;

final class ReturnWorkerFactory implements WorkerFactory
{
    public function construct(): WorkerInterface
    {
        return new ReturnWorker();
    }
}
```

After it created the worker it will listen for incoming work and call the `perform` method for each work DTO coming in.
The `ReturnWorker` will simply sent the work it received back to the main thread. (This is a very simple and effective
way to tests that threads are working as intended, and it is included for anyone wanting to use this package as an easy
set of tools to get started.)

```php
<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Worker\Workers;

use ReactParallel\Pool\Worker\Work as WorkContract;
use ReactParallel\Pool\Worker\Work\Worker as WorkerInterface;

final class ReturnWorker implements WorkerInterface
{
    public function perform(WorkContract $work): Result
    {
        return new Result($work->work());
    }
}
```

Now how the factory creates the worker and what it puts into it is all up to you. You already have the Composer
autoloader active so you can create any object like you normally would.

## License ##

Copyright 2020 [Cees-Jan Kiewiet](http://wyrihaximus.net/)

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
