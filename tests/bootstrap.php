<?php

$composerLoader = dirname(__DIR__,3).'/vendor/autoload.php';
if(file_exists($composerLoader)) {
    include_once dirname(__DIR__,3).'/vendor/autoload.php';
}
include_once __DIR__.'/BaseTestCase.php';
include_once __DIR__.'/AbstractEloquentTestCase.php';
include_once __DIR__.'/Classes/TestEloquentManager.php';

include_once __DIR__.'/Classes/Models/App1/AppModel.php';
include_once __DIR__.'/Classes/Models/App1/Application.php';
include_once __DIR__.'/Classes/Models/App2/AppModel.php';
include_once __DIR__.'/Classes/Models/LaravelPackage/LaravelModel.php';
include_once __DIR__.'/Classes/Models/MyModel.php';
include_once __DIR__.'/Classes/Models/ExtendSymbioticModel.php';
