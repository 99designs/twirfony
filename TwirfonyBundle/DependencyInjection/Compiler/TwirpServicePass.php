<?php

namespace Twirfony\TwirfonyBundle\DependencyInjection\Compiler;

use RuntimeException;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Twirfony\TwirfonyBundle\DependencyInjection\Twirp\ServiceDefinition;
use Twirfony\TwirfonyBundle\DependencyInjection\Twirp\ServiceMethod;
use Twirfony\TwirpService;

class TwirpServicePass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     * @throws \ReflectionException
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('twirp.service_registry');

        $taggedServices = $container->findTaggedServiceIds(TwirpService::TAG_NAME);
        foreach ($taggedServices as $id => $tags) {

            $ref = new ReflectionClass($container->findDefinition($id)->getClass());

            $interface = $this->getTwirpInterface($ref);

            $serviceDef = new Definition(ServiceDefinition::class, [$id]);

            foreach($interface->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {

                $methodDef = new Definition(ServiceMethod::class, [
                    $this->getTwirpPath($method),
                    $method->getName(),
                    $this->getFirstArgumentType($method)
                ]);
                $serviceDef->addMethodCall('addMethod', [$methodDef]);
            }

            $definition->addMethodCall('addService', [$serviceDef]);
        }
    }

    private function getTwirpInterface(ReflectionClass $class)
    {
        foreach ($class->getInterfaces() as $interface) {
            if ($interface->getConstant('SERVICE_NAME')) {
                return $interface;
            }
        }
        throw new RuntimeException("Twirp interface not found on {$class->getName()}");
    }

    private function getTwirpPath(ReflectionMethod $method)
    {
        preg_match('/@rpc (.*)/', $method->getDocComment(), $matches);
        if (!isset($matches[1]) || !$matches[1]) {

            $fqn = $method->getDeclaringClass()->getName().'::'.$method->getName();
            throw new RuntimeException("Twirp service method {$fqn} missing @rpc annotation");
        }
        return $matches[1];
    }

    private function getFirstArgumentType(ReflectionMethod $method)
    {
        return $method->getParameters()[0]->getType()->getName();
    }
}
