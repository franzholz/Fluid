<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\View\ViewInterface;

/**
 * A key/value store that can be used by ViewHelpers to communicate between each other.
 */
class ViewHelperVariableContainer
{
    /**
     * Two-dimensional object array storing the values. The first dimension is the fully qualified ViewHelper name,
     * and the second dimension is the identifier for the data the ViewHelper wants to store.
     *
     * @var array
     */
    protected $objects = [];

    /**
     * @var ViewInterface
     */
    protected $view;

    /**
     * Add a variable to the Variable Container. Make sure that $viewHelperName is ALWAYS set
     * to your fully qualified ViewHelper Class Name
     *
     * @param string $viewHelperName The ViewHelper Class name (Fully qualified, like "TYPO3Fluid\Fluid\ViewHelpers\ForViewHelper")
     * @param string $key Key of the data
     * @param mixed $value The value to store
     * @return void
     */
    public function add(string $viewHelperName, string $key, $value): void
    {
        $this->addOrUpdate($viewHelperName, $key, $value);
    }

    /**
     * Adds, or overrides recursively, all current variables defined in associative
     * array or Traversable (with string keys!).
     *
     * @param string $viewHelperName The ViewHelper Class name (Fully qualified, like "TYPO3Fluid\Fluid\ViewHelpers\ForViewHelper")
     * @param array|\Traversable $variables An associative array of all variables to add
     * @return void
     */
    public function addAll(string $viewHelperName, iterable $variables): void
    {
        if (!is_array($variables) && !$variables instanceof \Traversable) {
            throw new \InvalidArgumentException(
                'Invalid argument type for $variables in ViewHelperVariableContainer->addAll(). Expects array/Traversable ' .
                'but received ' . (is_object($variables) ? get_class($variables) : gettype($variables)),
                1501425195
            );
        }
        $this->objects[$viewHelperName] = array_replace_recursive(
            isset($this->objects[$viewHelperName]) ? $this->objects[$viewHelperName] : [],
            $variables instanceof \Traversable ? iterator_to_array($variables) : $variables
        );
    }

    /**
     * Add a variable to the Variable Container. Make sure that $viewHelperName is ALWAYS set
     * to your fully qualified ViewHelper Class Name.
     * In case the value is already inside, it is silently overridden.
     *
     * @param string $viewHelperName The ViewHelper Class name (Fully qualified, like "TYPO3Fluid\Fluid\ViewHelpers\ForViewHelper")
     * @param string $key Key of the data
     * @param mixed $value The value to store
     * @return void
     */
    public function addOrUpdate(string $viewHelperName, string $key, $value): void
    {
        if (!isset($this->objects[$viewHelperName])) {
            $this->objects[$viewHelperName] = [];
        }
        $this->objects[$viewHelperName][$key] = $value;
    }

    /**
     * Gets a variable which is stored
     *
     * @param string $viewHelperName The ViewHelper Class name (Fully qualified, like "TYPO3Fluid\Fluid\ViewHelpers\ForViewHelper")
     * @param string $key Key of the data
     * @param mixed $default Default value to use if no value is found.
     * @return mixed The object stored
     */
    public function get(string $viewHelperName, string $key, $default = null)
    {
        return $this->exists($viewHelperName, $key) ? $this->objects[$viewHelperName][$key] : $default;
    }

    /**
     * Gets all variables stored for a particular ViewHelper
     *
     * @param string $viewHelperName The ViewHelper Class name (Fully qualified, like "TYPO3Fluid\Fluid\ViewHelpers\ForViewHelper")
     * @param mixed $default
     * @return array
     */
    public function getAll(string $viewHelperName, $default = null): array
    {
        return isset($this->objects[$viewHelperName]) ? $this->objects[$viewHelperName] : $default;
    }

    /**
     * Determine whether there is a variable stored for the given key
     *
     * @param string $viewHelperName The ViewHelper Class name (Fully qualified, like "TYPO3Fluid\Fluid\ViewHelpers\ForViewHelper")
     * @param string $key Key of the data
     * @return boolean TRUE if a value for the given ViewHelperName / Key is stored, FALSE otherwise.
     */
    public function exists(string $viewHelperName, string $key): bool
    {
        return isset($this->objects[$viewHelperName]) && array_key_exists($key, $this->objects[$viewHelperName]);
    }

    /**
     * Remove a value from the variable container
     *
     * @param string $viewHelperName The ViewHelper Class name (Fully qualified, like "TYPO3Fluid\Fluid\ViewHelpers\ForViewHelper")
     * @param string $key Key of the data to remove
     * @return void
     */
    public function remove(string $viewHelperName, string $key): void
    {
        unset($this->objects[$viewHelperName][$key]);
    }

    /**
     * Set the view to pass it to ViewHelpers.
     *
     * @param ViewInterface $view View to set
     * @return void
     */
    public function setView(ViewInterface $view): void
    {
        $this->view = $view;
    }

    /**
     * Get the view.
     *
     * !!! This is NOT a public API and might still change!!!
     *
     * @return ViewInterface The View
     */
    public function getView(): ViewInterface
    {
        return $this->view;
    }

    /**
     * Clean up for serializing.
     *
     * @return array
     */
    public function __sleep(): array
    {
        return ['objects'];
    }
}
