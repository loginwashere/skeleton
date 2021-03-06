<?php
/**
 * Grid of pages
 *
 * @author   Anton Shevchuk
 * @created  27.08.12 10:08
 */
namespace Application;

use Bluz\Proxy\Layout;

return
/**
 * @privilege Management
 * @return void
 */
function () use ($view, $module, $controller) {
    /**
     * @var Bootstrap $this
     * @var \Bluz\View\View $view
     */
    Layout::setTemplate('dashboard.phtml');
    Layout::breadCrumbs(
        [
            $view->ahref('Dashboard', ['dashboard', 'index']),
            __('Pages')
        ]
    );

    $grid = new Pages\Grid();
    $grid->setModule($module);
    $grid->setController($controller);

    $view->grid = $grid;
};
