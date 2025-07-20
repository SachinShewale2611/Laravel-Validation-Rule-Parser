<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ValidationRuleParser;
use Inertia\Inertia;
use ReflectionMethod;

class ShareValidationRules
{
    protected $parser;

    public function __construct(ValidationRuleParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Get current route
        $route = $request->route();

        if (!$route) {
            return $next($request);
        }

        $controller = $route->getController();
        $action = $route->getActionMethod();

        // Map actions to validation methods
        $methodMap = [
            'create' => 'store',
            'edit' => 'update',
            'show' => null,
            'index' => null,
        ];

        // Get the validation method based on current action
        $validationMethod = $methodMap[$action] ?? $action;

        // Skip if no validation method needed
        if ($validationMethod === null) {
            return $next($request);
        }

        // Try to get validation rules from the controller
        $validationRules = $this->getValidationRules($controller, $validationMethod, $request);

        if (!empty($validationRules)) {
            $zodRules = $this->parser->parseRules($validationRules);
            $tsInterface = $this->parser->generateTypeScriptInterface($zodRules);

            // Share with Inertia
            Inertia::share([
                'validationRules' => $zodRules,
                'typeScriptInterface' => $tsInterface,
                'validationMethod' => $validationMethod,
                'currentAction' => $action,
            ]);
        }

        return $next($request);
    }

    /**
     * Extract validation rules from controller
     */
    private function getValidationRules($controller, string $validationMethod, Request $request): array
    {
        if (!$controller) {
            return [];
        }

        // Method 1: Check if controller has a rules method (e.g., storeRules, updateRules)
        $rulesMethod = $validationMethod . 'Rules';
        if (method_exists($controller, $rulesMethod)) {
            return $controller->{$rulesMethod}($request);
        }

        // Method 2: Check if controller has a validation rules property (e.g., storeValidationRules)
        $rulesProperty = $validationMethod . 'ValidationRules';
        if (property_exists($controller, $rulesProperty)) {
            return $controller->{$rulesProperty};
        }

        // Method 3: Look for FormRequest class using controller and method names
        $formRequestClass = $this->findFormRequestClass($controller, $validationMethod);
        if ($formRequestClass) {
            try {
                $formRequest = new $formRequestClass();
                return $formRequest->rules();
            } catch (\Exception $e) {
                // Continue to other methods
            }
        }

        // Method 4: Try to extract from FormRequest if used in method signature
        try {
            $reflection = new \ReflectionMethod(get_class($controller), $validationMethod);
            $parameters = $reflection->getParameters();

            foreach ($parameters as $parameter) {
                $type = $parameter->getType();
                if ($type && !$type->isBuiltin()) {
                    $className = $type->getName();
                    if (is_subclass_of($className, \Illuminate\Foundation\Http\FormRequest::class)) {
                        $formRequest = new $className();
                        return $formRequest->rules();
                    }
                }
            }
        } catch (\Exception $e) {
            // Continue to other methods
        }

        return [];
    }

    /**
     * Find FormRequest class using naming conventions
     */
    private function findFormRequestClass($controller, string $method): ?string
    {
        $controllerClass = get_class($controller);
        $controllerName = class_basename($controllerClass);

        // Remove 'Controller' suffix
        $resourceName = str_replace('Controller', '', $controllerName);

        // Try different naming conventions for FormRequest classes
        $possibleClasses = [
            // ResourceMethodRequest (e.g., UserStoreRequest, UserUpdateRequest)
            "App\\Http\\Requests\\{$resourceName}" . ucfirst($method) . "Request",

            // MethodResourceRequest (e.g., StoreUserRequest, UpdateUserRequest)
            "App\\Http\\Requests\\" . ucfirst($method) . "{$resourceName}Request",

            // ResourceRequest (e.g., UserRequest)
            "App\\Http\\Requests\\{$resourceName}Request",

            // With Form prefix (e.g., UserStoreFormRequest)
            "App\\Http\\Requests\\{$resourceName}" . ucfirst($method) . "FormRequest",

            // Different namespace structures
            "App\\Http\\Requests\\{$resourceName}\\{$resourceName}" . ucfirst($method) . "Request",
            "App\\Http\\Requests\\{$resourceName}\\" . ucfirst($method) . "Request",

            // Plural forms
            "App\\Http\\Requests\\" . $this->str_plural($resourceName) . ucfirst($method) . "Request",
            "App\\Http\\Requests\\" . ucfirst($method) . $this->str_plural($resourceName) . "Request",
        ];

        foreach ($possibleClasses as $className) {
            if (class_exists($className) && is_subclass_of($className, \Illuminate\Foundation\Http\FormRequest::class)) {
                return $className;
            }
        }

        return null;
    }

    /**
     * Get plural form of a word (simple implementation)
     */
private function str_plural(string $word): string
{
    // Simple pluralization
    if (str_ends_with($word, 'y')) {
        return substr($word, 0, -1) . 'ies';
    }

    $suffixes = ['s', 'sh', 'ch', 'x', 'z'];
    foreach ($suffixes as $suffix) {
        if (str_ends_with($word, $suffix)) {
            return $word . 'es';
        }
    }

    return $word . 's';
}

}
