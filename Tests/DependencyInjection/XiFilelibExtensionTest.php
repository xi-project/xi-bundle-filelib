<?php

/**
 * This file is part of the Xi FilelibBundle package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Bundle\FilelibBundle\DependencyInjection;

use Xi\Bundle\FilelibBundle\DependencyInjection\XiFilelibExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Config\FileLocator;

/**
 * @author Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
 */
class XiFilelibExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    protected function setUp()
    {
        $this->container = $this->getContainer();
        $this->container->registerExtension(new XiFilelibExtension());
    }

    /**
     * @test
     */
    public function defaultProfileArguments()
    {
        $this->loadFromFile('basic_config');
        $this->compileContainer();

        $definition = $this->container->getDefinition('filelib.profiles.default');
        $arguments = $definition->getArguments();

        $this->assertEquals('default', $arguments[0]['identifier']);
        $this->assertEquals('Default description', $arguments[0]['description']);
        $this->assertEquals(false, $arguments[0]['accessToOriginal']);
        $this->assertEquals(false, $arguments[0]['publishOriginal']);
    }

    /**
     * @test
     */
    public function doctrine2Backend()
    {
        $this->loadFromFile('doctrine2_backend');
        $this->compileContainer();

        $definition = $this->container->getDefinition('filelib.backend');

        $this->assertEquals('Xi\Filelib\Backend\Doctrine2Backend', $definition->getClass());
        $this->assertMethodCall($definition, 'setFolderEntityName', 'Foo\Folder');
        $this->assertMethodCall($definition, 'setFileEntityName', 'Foo\File');
    }

    /**
     * @param Definition $definition
     * @param string     $method
     * @param mixed      $value
     */
    private function assertMethodCall(Definition $definition, $method, $value)
    {
        foreach ($definition->getMethodCalls() as $methodCall) {
            if ($methodCall[0] === $method) {
                $this->assertContains($value, $methodCall[1]);

                return;
            }
        }

        $this->fail(sprintf('"%s" was not called with "%s"', $method, $value));
    }

    /**
     * @test
     */
    public function mongoBackend()
    {
        $this->loadFromFile('mongo_backend');
        $this->compileContainer();

        $definition = $this->container->getDefinition('filelib.backend');
        $arguments = $definition->getArguments();

        $mongoDb = $arguments[0];
        $mongo = $mongoDb->getArgument(0);

        $this->assertEquals('Xi\Filelib\Backend\MongoBackend', $definition->getClass());
        $this->assertEquals('mongodb://localhost:27017', $mongo->getArgument(0));
        $this->assertEquals('xi_filelib', $mongoDb->getArgument(1));
    }

    /**
     * @test
     */
    public function throwsExceptionIfBackendIsNotConfigured()
    {
        $this->loadFromFile('no_backend');

        $this->setExpectedException(
            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            'Backend must be configured.'
        );

        $this->compileContainer();
    }

    /**
     * @return ContainerBuilder
     */
    private function getContainer()
    {
        return new ContainerBuilder(new ParameterBag(array(
            'kernel.debug'       => false,
            'kernel.bundles'     => array(),
            'kernel.cache_dir'   => sys_get_temp_dir(),
            'kernel.environment' => 'test',
            'kernel.root_dir'    => __DIR__,
        )));
    }

    /**
     * @param string $file
     */
    private function loadFromFile($file)
    {
        $loader = new YamlFileLoader($this->container, new FileLocator(__DIR__.'/Fixtures/config/yml'));
        $loader->load($file.'.yml');
    }

    private function compileContainer()
    {
        $this->container->getCompilerPassConfig()->setOptimizationPasses(array(new ResolveDefinitionTemplatesPass()));
        $this->container->getCompilerPassConfig()->setRemovingPasses(array());
        $this->container->compile();
    }
}
