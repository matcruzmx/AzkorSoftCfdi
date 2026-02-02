<?php


namespace AzkorSoft\Cfdi\Cancelacion;

class CancelacionResponse
{
    public function __construct(
        public bool $success,
        public ?string $acuse = null,
        public ?string $mensaje = null
    ) {}
}
