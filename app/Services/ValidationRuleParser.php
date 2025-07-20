<?php

namespace App\Services;

use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ValidationRuleParser
{
    /**
     * Parse Laravel validation rules to Zod-compatible format
     */
    public function parseRules(array $rules): array
    {
        $zodRules = [];

        foreach ($rules as $field => $rule) {
            $zodRules[$field] = $this->convertRuleToZod($rule, $field);
        }

        return $zodRules;
    }

    /**
     * Convert individual Laravel rule to Zod format
     */
    private function convertRuleToZod($rule, string $field): array
    {
        if (is_string($rule)) {
            $rule = explode('|', $rule);
        }

        if (!is_array($rule)) {
            $rule = [$rule];
        }

        $zodRule = [
            'type' => 'string', // default
            'required' => false,
            'rules' => []
        ];

        foreach ($rule as $singleRule) {
            $this->parseSingleRule($singleRule, $zodRule, $field);
        }

        return $zodRule;
    }

    /**
     * Parse a single validation rule
     */
    private function parseSingleRule($rule, array &$zodRule, string $field): void
    {
        if (is_object($rule)) {
            $this->parseObjectRule($rule, $zodRule);
            return;
        }

        $ruleName = $rule;
        $parameters = [];

        if (str_contains($rule, ':')) {
            [$ruleName, $paramString] = explode(':', $rule, 2);
            $parameters = explode(',', $paramString);
        }

        switch ($ruleName) {
            case 'required':
                $zodRule['required'] = true;
                break;

            case 'string':
                $zodRule['type'] = 'string';
                break;

            case 'integer':
            case 'numeric':
                $zodRule['type'] = 'number';
                break;

            case 'boolean':
                $zodRule['type'] = 'boolean';
                break;

            case 'email':
                $zodRule['type'] = 'string';
                $zodRule['rules'][] = ['type' => 'email'];
                break;

            case 'url':
                $zodRule['type'] = 'string';
                $zodRule['rules'][] = ['type' => 'url'];
                break;

            case 'min':
                $zodRule['rules'][] = [
                    'type' => 'min',
                    'value' => (int) $parameters[0]
                ];
                break;

            case 'max':
                $zodRule['rules'][] = [
                    'type' => 'max',
                    'value' => (int) $parameters[0]
                ];
                break;

            case 'between':
                $zodRule['rules'][] = [
                    'type' => 'min',
                    'value' => (int) $parameters[0]
                ];
                $zodRule['rules'][] = [
                    'type' => 'max',
                    'value' => (int) $parameters[1]
                ];
                break;

            case 'in':
                $zodRule['rules'][] = [
                    'type' => 'enum',
                    'values' => $parameters
                ];
                break;

            case 'regex':
                $zodRule['rules'][] = [
                    'type' => 'regex',
                    'pattern' => $parameters[0]
                ];
                break;

            case 'confirmed':
                $zodRule['rules'][] = [
                    'type' => 'confirmed',
                    'field' => $field . '_confirmation'
                ];
                break;

            case 'unique':
                $zodRule['rules'][] = [
                    'type' => 'unique',
                    'table' => $parameters[0] ?? null,
                    'column' => $parameters[1] ?? null
                ];
                break;

            case 'exists':
                $zodRule['rules'][] = [
                    'type' => 'exists',
                    'table' => $parameters[0] ?? null,
                    'column' => $parameters[1] ?? null
                ];
                break;

            case 'date':
                $zodRule['type'] = 'date';
                break;

            case 'after':
                $zodRule['rules'][] = [
                    'type' => 'after',
                    'date' => $parameters[0]
                ];
                break;

            case 'before':
                $zodRule['rules'][] = [
                    'type' => 'before',
                    'date' => $parameters[0]
                ];
                break;

            case 'array':
                $zodRule['type'] = 'array';
                break;

            case 'file':
                $zodRule['type'] = 'file';
                break;

            case 'image':
                $zodRule['type'] = 'file';
                $zodRule['rules'][] = ['type' => 'image'];
                break;

            case 'mimes':
                $zodRule['rules'][] = [
                    'type' => 'mimes',
                    'types' => $parameters
                ];
                break;

            case 'size':
                $zodRule['rules'][] = [
                    'type' => 'size',
                    'value' => (int) $parameters[0]
                ];
                break;
        }
    }

    /**
     * Parse object-based rules (like Rule::unique())
     */
    private function parseObjectRule($rule, array &$zodRule): void
    {
        $className = get_class($rule);

        // Handle common Rule objects
        if (method_exists($rule, '__toString')) {
            $ruleString = (string) $rule;
            $this->parseSingleRule($ruleString, $zodRule, '');
        }
    }

    /**
     * Generate TypeScript interface for the validation rules
     */
    public function generateTypeScriptInterface(array $zodRules, string $interfaceName = 'FormData'): string
    {
        $properties = [];

        foreach ($zodRules as $field => $rule) {
            $type = $this->getTypeScriptType($rule);
            $optional = $rule['required'] ? '' : '?';
            $properties[] = "  {$field}{$optional}: {$type};";
        }

        $propertiesString = implode("\n", $properties);

        return "export interface {$interfaceName} {\n{$propertiesString}\n}";
    }

    /**
     * Get TypeScript type from Zod rule
     */
    private function getTypeScriptType(array $rule): string
    {
        switch ($rule['type']) {
            case 'string':
                return 'string';
            case 'number':
                return 'number';
            case 'boolean':
                return 'boolean';
            case 'date':
                return 'Date | string';
            case 'array':
                return 'any[]';
            case 'file':
                return 'File';
            default:
                return 'any';
        }
    }
}
