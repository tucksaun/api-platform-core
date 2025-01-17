<?php

/*
 * This file is part of the API Platform project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiPlatform\Symfony\EventListener;

use ApiPlatform\Util\RequestAttributesExtractor;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;

/**
 * Handles requests errors.
 *
 * @author Samuel ROZE <samuel.roze@gmail.com>
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class ExceptionListener
{
    private readonly ErrorListener $errorListener;

    public function __construct(string|object|array|null $controller, LoggerInterface $logger = null, bool $debug = false)
    {
        $this->errorListener = new ErrorListener($controller, $logger, $debug);
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        // Normalize exceptions only for routes managed by API Platform
        if (
            'html' === $request->getRequestFormat('') ||
            !((RequestAttributesExtractor::extractAttributes($request)['respond'] ?? $request->attributes->getBoolean('_api_respond', false)) || $request->attributes->getBoolean('_graphql', false))
        ) {
            return;
        }

        $this->errorListener->onKernelException($event);
    }
}
