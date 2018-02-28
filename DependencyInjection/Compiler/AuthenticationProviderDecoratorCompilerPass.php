<?php

namespace Scheb\TwoFactorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Get all registered authentication providers and decorate them with AuthenticationProviderDecorator
 */
class AuthenticationProviderDecoratorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $authenticationManager = $container->getDefinition('security.authentication.manager');
        $authenticationProviderIds = $authenticationManager->getArgument(0)->getValues();
        foreach ($authenticationProviderIds as $authenticationProviderId) {
            // Ensure not to decorate the two-factor authentication provider, otherwise we'll get an endless loop
            if (strpos($authenticationProviderId, 'security.authentication.provider.two_factor') === false) {
                $this->decorateAuthenticationProvider($container, $authenticationProviderId);
            }
        }
    }

    private function decorateAuthenticationProvider(ContainerBuilder $container, string $providerId): void
    {
        $decoratedServiceId = $providerId . '.two_factor_decorator';
        $container
            ->setDefinition($decoratedServiceId, new ChildDefinition('scheb_two_factor.security.authentication.provider.decorator'))
            ->setDecoratedService($providerId)
            ->replaceArgument(0, new Reference($decoratedServiceId . '.inner'));
    }
}
