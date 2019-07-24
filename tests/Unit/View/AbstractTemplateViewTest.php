<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\View;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Component\Error\ChildNotFoundException;
use TYPO3Fluid\Fluid\Core\Compiler\AbstractCompiledTemplate;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\AbstractTemplateView;
use TYPO3Fluid\Fluid\View\Exception\InvalidSectionException;

/**
 * Testcase for the TemplateView
 */
class AbstractTemplateViewTest extends UnitTestCase
{

    /**
     * @var AbstractTemplateView
     */
    protected $view;

    /**
     * @var RenderingContext
     */
    protected $renderingContext;

    /**
     * @var ViewHelperVariableContainer
     */
    protected $viewHelperVariableContainer;

    /**
     * @var VariableProviderInterface
     */
    protected $templateVariableContainer;

    /**
     * Sets up this test case
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->templateVariableContainer = $this->getMock(StandardVariableProvider::class);
        $this->viewHelperVariableContainer = $this->getMock(ViewHelperVariableContainer::class, ['setView']);
        $this->renderingContext = new RenderingContextFixture();
        $this->renderingContext->viewHelperVariableContainer = $this->viewHelperVariableContainer;
        $this->renderingContext->variableProvider = $this->templateVariableContainer;
        $this->view = $this->getMockForAbstractClass(AbstractTemplateView::class);
        $this->view->setRenderingContext($this->renderingContext);
    }

    /**
     * @test
     */
    public function testGetRenderingContextReturnsExpectedRenderingContext(): void
    {
        $result = $this->view->getRenderingContext();
        $this->assertSame($this->renderingContext, $result);
    }

    /**
     * @test
     */
    public function testGetViewHelperResolverReturnsExpectedViewHelperResolver(): void
    {
        $viewHelperResolver = $this->getMock(ViewHelperResolver::class);
        $this->renderingContext->setViewHelperResolver($viewHelperResolver);
        $result = $this->view->getViewHelperResolver();
        $this->assertSame($viewHelperResolver, $result);
    }

    /**
     * @test
     */
    public function assignAddsValueToTemplateVariableContainer(): void
    {
        $this->templateVariableContainer->expects($this->at(0))->method('add')->with('foo', 'FooValue');
        $this->templateVariableContainer->expects($this->at(1))->method('add')->with('bar', 'BarValue');

        $this->view
            ->assign('foo', 'FooValue')
            ->assign('bar', 'BarValue');
    }

    /**
     * @test
     */
    public function assignCanOverridePreviouslyAssignedValues(): void
    {
        $this->templateVariableContainer->expects($this->at(0))->method('add')->with('foo', 'FooValue');
        $this->templateVariableContainer->expects($this->at(1))->method('add')->with('foo', 'FooValueOverridden');

        $this->view->assign('foo', 'FooValue');
        $this->view->assign('foo', 'FooValueOverridden');
    }

    /**
     * @test
     */
    public function assignMultipleAddsValuesToTemplateVariableContainer(): void
    {
        $this->templateVariableContainer->expects($this->at(0))->method('add')->with('foo', 'FooValue');
        $this->templateVariableContainer->expects($this->at(1))->method('add')->with('bar', 'BarValue');
        $this->templateVariableContainer->expects($this->at(2))->method('add')->with('baz', 'BazValue');

        $this->view
            ->assignMultiple(['foo' => 'FooValue', 'bar' => 'BarValue'])
            ->assignMultiple(['baz' => 'BazValue']);
    }

    /**
     * @test
     */
    public function assignMultipleCanOverridePreviouslyAssignedValues(): void
    {
        $this->templateVariableContainer->expects($this->at(0))->method('add')->with('foo', 'FooValue');
        $this->templateVariableContainer->expects($this->at(1))->method('add')->with('foo', 'FooValueOverridden');
        $this->templateVariableContainer->expects($this->at(2))->method('add')->with('bar', 'BarValue');

        $this->view->assign('foo', 'FooValue');
        $this->view->assignMultiple(['foo' => 'FooValueOverridden', 'bar' => 'BarValue']);
    }

    /**
     * @test
     */
    public function testRenderSectionThrowsExceptionIfSectionMissingAndNotIgnoringUnknown(): void
    {
        $parsedTemplate = $this->getMockBuilder(ComponentInterface::class)->setMethods(['getNamedChild'])->getMockForAbstractClass();
        $parsedTemplate->expects($this->any())->method('getNamedChild')->willThrowException(new ChildNotFoundException('...'));
        $view = $this->getMockForAbstractClass(
            AbstractTemplateView::class,
            [],
            '',
            false,
            false,
            true,
            ['getCurrentParsedTemplate', 'getCurrentRenderingType', 'getCurrentRenderingContext']
        );
        $view->expects($this->once())->method('getCurrentRenderingContext')->willReturn($this->renderingContext);
        $view->expects($this->once())->method('getCurrentRenderingType')->willReturn(AbstractTemplateView::RENDERING_LAYOUT);
        $view->expects($this->once())->method('getCurrentParsedTemplate')->willReturn($parsedTemplate);
        $this->setExpectedException(ChildNotFoundException::class);
        $view->renderSection('Missing');
    }
}
