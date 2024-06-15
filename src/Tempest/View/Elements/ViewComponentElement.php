<?php

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\ViewComponent;
use Tempest\View\ViewRenderer;

final class ViewComponentElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly ViewComponent $viewComponent,
        private readonly ?Element $previous,
        private readonly array $attributes,
        private array $data = [],
    ) {}

    public function render(ViewRenderer $renderer): string
    {
        return $this->viewComponent->render($renderer);
    }
}