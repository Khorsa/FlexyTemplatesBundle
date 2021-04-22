<?php
namespace flexycms\FlexyTemplatesBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class FlexyTemplatesBundle extends Bundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}