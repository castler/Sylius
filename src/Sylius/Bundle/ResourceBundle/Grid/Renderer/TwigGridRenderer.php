<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\ResourceBundle\Grid\Renderer;

use Sylius\Bundle\ResourceBundle\Grid\Parser\OptionsParserInterface;
use Sylius\Component\Grid\Definition\Action;
use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\Definition\Filter;
use Sylius\Component\Grid\Renderer\GridRendererInterface;
use Sylius\Component\Grid\View\GridViewInterface;

final class TwigGridRenderer implements GridRendererInterface
{
    /**
     * @var GridRendererInterface
     */
    private $gridRenderer;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var OptionsParserInterface
     */
    private $optionsParser;

    /**
     * @var array
     */
    private $actionTemplates;

    /**
     * @var array
     */
    private $bulkActionTemplates;

    /**
     * @param GridRendererInterface $gridRenderer
     * @param \Twig_Environment $twig
     * @param OptionsParserInterface $optionsParser
     * @param array $actionTemplates
     * @param array $bulkActionTemplates
     */
    public function __construct(
        GridRendererInterface $gridRenderer,
        \Twig_Environment $twig,
        OptionsParserInterface $optionsParser,
        array $actionTemplates = [],
        array $bulkActionTemplates = []
    ) {
        $this->gridRenderer = $gridRenderer;
        $this->twig = $twig;
        $this->optionsParser = $optionsParser;
        $this->actionTemplates = $actionTemplates;
        $this->bulkActionTemplates = $bulkActionTemplates;
    }

    /**
     * {@inheritdoc}
     */
    public function render(GridViewInterface $gridView, ?string $template = null): string
    {
        return (string) $this->gridRenderer->render($gridView, $template);
    }

    /**
     * {@inheritdoc}
     */
    public function renderField(GridViewInterface $gridView, Field $field, $data): string
    {
        return (string) $this->gridRenderer->renderField($gridView, $field, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function renderAction(GridViewInterface $gridView, Action $action, $data = null): string
    {
        $type = $action->getType();
        if (!isset($this->actionTemplates[$type])) {
            throw new \InvalidArgumentException(sprintf('Missing template for action type "%s".', $type));
        }

        $options = $this->optionsParser->parseOptions(
            $action->getOptions(),
            $gridView->getRequestConfiguration()->getRequest(),
            $data
        );

        return (string) $this->twig->render($this->actionTemplates[$type], [
            'grid' => $gridView,
            'action' => $action,
            'data' => $data,
            'options' => $options,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function renderBulkAction(GridViewInterface $gridView, Action $bulkAction, $data = null): string
    {
        $type = $bulkAction->getType();
        if (!isset($this->bulkActionTemplates[$type])) {
            throw new \InvalidArgumentException(sprintf('Missing template for bulk action type "%s".', $type));
        }

        $options = $this->optionsParser->parseOptions(
            $bulkAction->getOptions(),
            $gridView->getRequestConfiguration()->getRequest(),
            $data
        );

        return (string) $this->twig->render($this->bulkActionTemplates[$type], [
            'grid' => $gridView,
            'action' => $bulkAction,
            'data' => $data,
            'options' => $options,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function renderFilter(GridViewInterface $gridView, Filter $filter): string
    {
        return (string) $this->gridRenderer->renderFilter($gridView, $filter);
    }
}
