<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Fixtures;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class TestViewHelper
 */
class TestViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('param1', 'integer', 'P1 Stuff', true);
        $this->registerArgument('param2', 'array', 'P2 Stuff', true);
        $this->registerArgument('param3', 'string', 'P3 Stuff', false, 'default');
    }

    /**
     * My comments.
     *
     * @return string
     */
    public function render()
    {
        return $this->arguments['param1'];
    }

    /**
     * Handle any additional comments by ignoring them
     *
     * @param string $argumentName
     * @return bool
     */
    public function allowUndeclaredArgument(string $argumentName): bool
    {
        return true;
    }


}
