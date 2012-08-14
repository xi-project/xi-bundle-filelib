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

        $this->loadFromFile('basic_config');
        $this->compileContainer();
    }

    /**
     * @test
     */
    public function defaultProfileArguments()
    {
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
    public function changeFormatPlugin()
    {
        $definition = $this->container->getDefinition('filelib.plugins.change_format');
        $arguments = $definition->getArguments();

        $this->assertEquals('filelib.fileoperator', $arguments[0]);
        $this->assertArrayHasKey('targetExtension', $arguments[1]);
    }

    /**
     * @test
     */
    public function versionPlugin()
    {
        $definition = $this->container->getDefinition('filelib.plugins.version');
        $arguments = $definition->getArguments();

        $tempDir = $this->container->getParameterBag()->get('kernel.root_dir') . '/data/temp';

        $this->assertEquals('filelib.storage', $arguments[0]);
        $this->assertEquals('filelib.publisher', $arguments[1]);
        $this->assertEquals('filelib.fileoperator', $arguments[2]);
        $this->assertEquals($tempDir, $arguments[3]);
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
