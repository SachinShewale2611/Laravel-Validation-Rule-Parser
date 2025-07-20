// composables/useValidation.ts
import { usePage } from '@inertiajs/vue3';
import { computed, readonly, ref, toRaw } from 'vue';

import { z, ZodType } from 'zod';

interface ValidationRule {
    type: string;
    required: boolean;
    rules: Array<{
        type: string;
        value?: any;
        values?: string[];
        pattern?: string;
        field?: string;
        table?: string;
        column?: string;
        date?: string;
        types?: string[];
    }>;
}

interface ValidationRules {
    [key: string]: ValidationRule;
}

export function useValidation() {
    const page = usePage();

    const validationRules = computed(() => (page.props.validationRules as ValidationRules) || {});

    const errors = ref<Record<string, string>>({});

    /**
     * Convert Laravel validation rules to Zod schema
     */
    function createZodSchema(rules: ValidationRules): ZodType {
        const shape: Record<string, any> = {};

        Object.entries(rules).forEach(([field, rule]) => {
            const zodField = createZodField(rule);
            shape[field] = zodField;
        });

        return z.object(shape);
    }

    /**
     * Create individual Zod field from Laravel rule
     */
    function createZodField(rule: ValidationRule): any {
        console.log('validationRules', validationRules.value);

        let zodField: any;

        // Base type
        switch (rule.type) {
            case 'string':
                zodField = z.string();
                break;
            case 'number':
                zodField = z.number();
                break;
            case 'boolean':
                zodField = z.boolean();
                break;
            case 'date':
                zodField = z.date().or(z.string().datetime());
                break;
            case 'array':
                zodField = z.array(z.any());
                break;
            case 'file':
                zodField = z.instanceof(File);
                break;
            default:
                zodField = z.string();
        }

        // Apply rules
        rule.rules.forEach((validationRule) => {
            switch (validationRule.type) {
                case 'email':
                    zodField = zodField.email('Please enter a valid email address');
                    break;

                case 'url':
                    zodField = zodField.url('Please enter a valid URL');
                    break;

                case 'min':
                    if (rule.type === 'string') {
                        zodField = zodField.min(validationRule.value, `Must be at least ${validationRule.value} characters`);
                    } else if (rule.type === 'number') {
                        zodField = zodField.min(validationRule.value, `Must be at least ${validationRule.value}`);
                    }
                    break;

                case 'max':
                    if (rule.type === 'string') {
                        zodField = zodField.max(validationRule.value, `Must be no more than ${validationRule.value} characters`);
                    } else if (rule.type === 'number') {
                        zodField = zodField.max(validationRule.value, `Must be no more than ${validationRule.value}`);
                    }
                    break;

                case 'enum':
                    zodField = zodField.refine(
                        (val: any) => validationRule.values?.includes(val),
                        `Must be one of: ${validationRule.values?.join(', ')}`,
                    );
                    break;

                case 'regex':
                    zodField = zodField.regex(new RegExp(validationRule.pattern || ''), 'Invalid format');
                    break;

                case 'image':
                    zodField = zodField.refine((file: File) => file.type.startsWith('image/'), 'Must be an image file');
                    break;

                case 'mimes':
                    zodField = zodField.refine(
                        (file: File) => {
                            const extension = file.name.split('.').pop()?.toLowerCase();
                            return validationRule.types?.includes(extension || '');
                        },
                        `Must be one of: ${validationRule.types?.join(', ')}`,
                    );
                    break;
            }
        });

        // Handle required/optional
        console.log('rule.required', rule);

        if (!rule.required) {
            zodField = zodField.optional();
        }else{
            if (rule.type === 'string') {
                zodField = zodField.min(1, { message: 'This field is required' });
            }
        }

        return zodField;
    }

    /**
     * Validate form data against the schema
     */
    function validate(data: Record<string, any>): boolean {
        errors.value = {};
        console.log('validationRules', toRaw(validationRules.value));

        try {
            const schema = createZodSchema(validationRules.value);
            console.log('schema', schema);

            schema.parse(data);
            return true;
        } catch (error: any) {
            // Defensive check before accessing .errors
            if (error instanceof z.ZodError && Array.isArray(error.issues)) {
                error.issues.forEach((err) => {
                    const field = err.path.join('.');
                    errors.value[field] = err.message;
                });
            } else {
                console.error('Unexpected error during validation:', error);
            }
            console.log('errors', errors.value);

            return false;
        }
    }

    /**
     * Validate single field
     */
    function validateField(field: string, value: any): boolean {
        const rule = validationRules.value[field];
        if (!rule) return true;

        try {
            const zodField = createZodField(rule);
            zodField.parse(value);
            delete errors.value[field];
            return true;
        } catch (error) {
            if (error instanceof z.ZodError) {
                errors.value[field] = error.errors[0]?.message || 'Invalid value';
            }
            return false;
        }
    }

    /**
     * Get validation schema for a specific form
     */
    function getSchema(fields?: string[]): ZodSchema {
        let rules = validationRules.value;

        if (fields) {
            rules = Object.fromEntries(Object.entries(rules).filter(([key]) => fields.includes(key)));
        }

        return createZodSchema(rules);
    }

    /**
     * Clear errors
     */
    function clearErrors(field?: string) {
        if (field) {
            delete errors.value[field];
        } else {
            errors.value = {};
        }
    }

    return {
        validationRules: validationRules,
        errors: readonly(errors),
        validate,
        validateField,
        getSchema,
        clearErrors,
        createZodSchema,
    };
}
