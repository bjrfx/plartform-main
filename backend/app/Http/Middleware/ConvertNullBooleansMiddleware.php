<?php

namespace App\Http\Middleware;

use Closure;

class ConvertNullBooleansMiddleware
{
    public function handle($request, Closure $next)
    {
        // Only process on POST, PUT, PATCH requests
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            return $next($request);
        }

        $rules = $this->extractBooleanRules($request);

        foreach ($rules as $field) {
            if (is_null($request->input($field))) {
                $request->merge([$field => false]);
            }
        }

        return $next($request);
    }

    /**
     * Extract all fields with boolean validation rules
     */
    private function extractBooleanRules($request): array
    {
        // Check if controller is using FormRequest and extract rules
        $formRequest = $request->route()->controller->getFormRequestInstance();

        if ($formRequest && method_exists($formRequest, 'rules')) {
            $rules = $formRequest->rules();

            // Return keys where 'boolean' is part of the rule
            return array_keys(array_filter($rules, function ($rule) {
                return str_contains(is_array($rule) ? implode('|', $rule) : $rule, 'boolean');
            }));
        }

        return [];
    }
}
