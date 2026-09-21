<?php

use App\Models\MenuItemModel;

if (!function_exists('get_user_menu')) {
    function get_user_menu(): array
    {
        $session = session();
        $loginData = $session->get('LoginData');
        if (empty($loginData)) {
            return ['header' => [], 'items' => []];
        }

        $jenisUser = $loginData['JENIS_USER'] ?? 'User';
        $menuModel = new MenuItemModel();

        return [
            'header' => $menuModel->getHeaderMenu($jenisUser),
            'items'  => $menuModel->getItemMenu($jenisUser)
        ];
    }
}

if (!function_exists('is_selected')) {
    function is_selected(?string $controller = null, ?string $action = null): string
    {
        $router = service('router');
        $currentController = strtolower(class_basename($router->controllerName()));
        $currentMethod = strtolower($router->methodName());

        if ($controller && $action) {
            return (strtolower($controller) === $currentController && strtolower($action) === $currentMethod) ? 'active' : '';
        }

        if ($controller) {
            return (strtolower($controller) === $currentController) ? 'active' : '';
        }

        if ($action) {
            return (strtolower($action) === $currentMethod) ? 'active' : '';
        }

        return '';
    }
}
