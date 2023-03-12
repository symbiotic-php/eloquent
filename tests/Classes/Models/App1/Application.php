<?php

namespace Symbiotic\Tests\Database\Eloquent\Models\App1;

use Illuminate\Database\Schema\Builder;
use Symbiotic\Database\Eloquent\EloquentManager;

class Application
{

    public function __construct(private EloquentManager $manager) {}

    public function getSchema(): Builder
    {
        return $this->manager->getSchemaBuilder();
    }
}