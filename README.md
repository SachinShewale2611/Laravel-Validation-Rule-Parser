# Laravel Validation Rule Parser

[![Latest Version](https://img.shields.io/github/release/SachinShewale2611/Laravel-Validation-Rule-Parser.svg?style=flat-square)](https://github.com/SachinShewale2611/Laravel-Validation-Rule-Parser/releases)
[![License](https://img.shields.io/github/license/SachinShewale2611/Laravel-Validation-Rule-Parser.svg?style=flat-square)](LICENSE)
[![GitHub issues](https://img.shields.io/github/issues/SachinShewale2611/Laravel-Validation-Rule-Parser.svg?style=flat-square)](https://github.com/SachinShewale2611/Laravel-Validation-Rule-Parser/issues)

A powerful Laravel package that automatically parses your backend validation rules and seamlessly shares them with your Vue.js frontend for real-time Zod validation. Keep your validation logic centralized in Laravel while providing instant feedback to users.

## ✨ Features

- 🔄 **Automatic Rule Parsing** - Converts Laravel validation rules to Zod schemas
- 🚀 **Real-time Validation** - Client-side validation with instant feedback
- 📝 **TypeScript Support** - Auto-generated TypeScript interfaces
- 🎯 **Multiple Detection Methods** - FormRequests, controller methods, properties
- 🔌 **Inertia.js Integration** - Seamless data sharing between backend and frontend
- 🎨 **Vue.js Composable** - Easy-to-use validation hooks
- 🔧 **Extensible** - Support for custom validation rules
- 📱 **Route-aware** - Automatically maps create/edit routes to store/update validation

## 📋 Requirements

- PHP 8.1+
- Laravel 9.0+
- Inertia.js
- Vue.js 3.0+
- Zod (for frontend validation)

## 🚀 Quick Start

### Installation

1. **Clone or download the repository**
```bash
git clone https://github.com/SachinShewale2611/Laravel-Validation-Rule-Parser.git
cd Laravel-Validation-Rule-Parser
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install and setup Inertia.js**
```bash
composer require inertiajs/inertia-laravel
php artisan inertia:middleware
```

4. **Install Node.js dependencies**
```bash
npm install @inertiajs/vue3 vue@next @vitejs/plugin-vue zod
npm install -D typescript @vue/tsconfig
```

5. **Configure your application**
```bash
cp .env.example .env
php artisan key:generate
```

### Basic Setup

1. **Register the middleware** in `app/Http/Kernel.php`:
```php
protected $middlewareGroups = [
    'web' => [
        // ... existing middleware
        \App\Http\Middleware\ShareValidationRules::class,
    ],
];
```

2. **Add the service provider** in `config/app.php`:
```php
'providers' => [
    // ... existing providers
    App\Providers\ValidationServiceProvider::class,
],
```

3. **Setup Vue.js** in `resources/js/app.ts`:
```typescript
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

createInertiaApp({
    resolve: (name) => import(`./Pages/${name}.vue`),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
```

## 📖 Usage

### Backend - Laravel Controller

You can define validation rules in multiple ways:

#### Method 1: Controller Rules Method
```php
class UserController extends Controller
{
    public function create()
    {
        return Inertia::render('Users/Create');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate($this->storeRules($request));
        // Handle store logic
    }
    
    public function storeRules(Request $request): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:8', 'confirmed'],
            'age' => ['required', 'integer', 'min:18', 'max:100'],
        ];
    }
}
```

#### Method 2: FormRequest Classes
```php
class UserStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:8', 'confirmed'],
        ];
    }
}

class UserController extends Controller
{
    public function store(UserStoreRequest $request)
    {
        $validated = $request->validated();
        // Handle store logic
    }
}
```

#### Method 3: Controller Properties
```php
class UserController extends Controller
{
    protected $storeValidationRules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
    ];
}
```

### Frontend - Vue.js Component

```vue
<template>
  <form @submit.prevent="submit">
    <div>
      <input
        v-model="form.name"
        type="text"
        placeholder="Name"
        :class="{ 'error': errors.name }"
        @blur="validateField('name', form.name)"
      />
      <span v-if="errors.name" class="error-message">{{ errors.name }}</span>
    </div>
    
    <div>
      <input
        v-model="form.email"
        type="email"
        placeholder="Email"
        :class="{ 'error': errors.email }"
        @blur="validateField('email', form.email)"
      />
      <span v-if="errors.email" class="error-message">{{ errors.email }}</span>
    </div>
    
    <button type="submit" :disabled="processing">Create User</button>
  </form>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { useValidation } from '@/composables/useValidation';

const form = useForm({
  name: '',
  email: '',
  password: '',
});

const { validate, validateField, errors, clearErrors } = useValidation();

const submit = async () => {
  clearErrors();
  
  if (await validate(form.data())) {
    form.post(route('users.store'));
  }
};
</script>
```

## 🔧 Advanced Usage

### Custom Validation Rules

Extend the `ValidationRuleParser` to support custom rules:

```php
// In ValidationRuleParser.php
private function parseSingleRule($rule, array &$zodRule, string $field): void
{
    // ... existing rules
    
    case 'custom_rule':
        $zodRule['rules'][] = [
            'type' => 'custom',
            'validator' => 'customValidator',
            'message' => 'Custom validation failed'
        ];
        break;
}
```

### API-based Rule Loading

For dynamic rule loading, use the validation API:

```typescript
// Load rules dynamically
const { loadRules } = useValidation();
const rules = await loadRules('user', 'store');
```

### Form-specific Validation Hook

```typescript
import { useFormValidation } from '@/composables/useValidation';

const { validate, errors } = useFormValidation('user', 'store');
```

## 🎯 Supported Validation Rules

| Laravel Rule | Zod Equivalent | Status |
|--------------|----------------|---------|
| `required` | `.min(1)` | ✅ |
| `string` | `z.string()` | ✅ |
| `email` | `.email()` | ✅ |
| `min:n` | `.min(n)` | ✅ |
| `max:n` | `.max(n)` | ✅ |
| `numeric` | `z.number()` | ✅ |
| `boolean` | `z.boolean()` | ✅ |
| `confirmed` | Custom refine | ✅ |
| `in:a,b,c` | `.enum([a,b,c])` | ✅ |
| `regex:pattern` | `.regex()` | ✅ |
| `file` | `z.instanceof(File)` | ✅ |
| `image` | File + type check | ✅ |
| `unique` | Backend only | ⚠️ |
| `exists` | Backend only | ⚠️ |

## 🗂️ Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── UserController.php
│   │   │   └── ValidationRulesController.php
│   │   ├── Middleware/
│   │   │   └── ShareValidationRules.php
│   │   └── Requests/
│   │       └── UserStoreRequest.php
│   ├── Services/
│   │   └── ValidationRuleParser.php
│   └── Providers/
│       └── ValidationServiceProvider.php
├── resources/
│   └── js/
│       ├── composables/
│       │   └── useValidation.ts
│       └── Pages/
│           └── Users/
│               ├── Create.vue
│               └── Edit.vue
└── routes/
    ├── web.php
    └── api.php
```

## 🔄 Route Mapping

The middleware automatically maps routes to validation methods:

| Route | Method | Validation Method |
|-------|---------|------------------|
| `/users/create` | `create` | `store` rules |
| `/users/{id}/edit` | `edit` | `update` rules |
| `/users` | `store` | `store` rules |
| `/users/{id}` | `update` | `update` rules |

## 🧪 FormRequest Naming Conventions

The package automatically detects FormRequest classes using these patterns:

```php
// For UserController store method:
App\Http\Requests\UserStoreRequest          // ✅ Recommended
App\Http\Requests\StoreUserRequest          // ✅ Alternative
App\Http\Requests\UserRequest               // ✅ Generic
App\Http\Requests\UserStoreFormRequest      // ✅ With Form suffix
App\Http\Requests\User\UserStoreRequest     // ✅ Namespaced
App\Http\Requests\UsersStoreRequest         // ✅ Plural form
```

## 🐛 Debugging

Enable debug mode in development:

```vue
<template>
  <!-- Your form -->
  
  <!-- Debug panel (development only) -->
  <div v-if="$page.props.app.debug" class="debug-panel">
    <h3>Validation Rules Debug</h3>
    <pre>{{ JSON.stringify($page.props.validationRules, null, 2) }}</pre>
  </div>
</template>
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request. For major changes, please open an issue first to discuss what you would like to change.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Laravel](https://laravel.com/) - The PHP framework
- [Inertia.js](https://inertiajs.com/) - Modern monolith approach
- [Vue.js](https://vuejs.org/) - Progressive JavaScript framework
- [Zod](https://zod.dev/) - TypeScript-first schema validation

## 📞 Support

If you encounter any issues or have questions:

1. Check the [Issues](https://github.com/SachinShewale2611/Laravel-Validation-Rule-Parser/issues) page
2. Create a new issue with detailed information
3. Join our discussions in the repository

## 🚧 Roadmap

- [ ] Support for more Laravel validation rules
- [ ] Custom error message mapping
- [ ] Multiple language support
- [ ] React.js support
- [ ] Standalone npm package
- [ ] CLI tool for setup automation

---

⭐ **Star this repository if it helped you!**
