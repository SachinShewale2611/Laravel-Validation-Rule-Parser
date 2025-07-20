<template>
  <div class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Create User</h1>

    <form @submit.prevent="submit" class="space-y-6">
      <!-- Name Field -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
          Name <span class="text-red-500">*</span>
        </label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          :class="{ 'border-red-500': errors.name }"
          @blur="validateField('name', form.name)"
          @input="clearErrors('name')"
        />
        <p v-if="errors.name" class="mt-1 text-sm text-red-600">
          {{ errors.name }}
        </p>
      </div>

      <!-- Email Field -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
          Email <span class="text-red-500">*</span>
        </label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          :class="{ 'border-red-500': errors.email }"
          @blur="validateField('email', form.email)"
          @input="clearErrors('email')"
        />
        <p v-if="errors.email" class="mt-1 text-sm text-red-600">
          {{ errors.email }}
        </p>
      </div>

      <!-- Password Field -->
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
          Password <span class="text-red-500">*</span>
        </label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          :class="{ 'border-red-500': errors.password }"
          @blur="validateField('password', form.password)"
          @input="clearErrors('password')"
        />
        <p v-if="errors.password" class="mt-1 text-sm text-red-600">
          {{ errors.password }}
        </p>
      </div>

      <!-- Password Confirmation -->
      <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
          Confirm Password <span class="text-red-500">*</span>
        </label>
        <input
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          :class="{ 'border-red-500': errors.password_confirmation }"
          @blur="validateField('password_confirmation', form.password_confirmation)"
          @input="clearErrors('password_confirmation')"
        />
        <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">
          {{ errors.password_confirmation }}
        </p>
      </div>

      <!-- Age Field -->
      <div>
        <label for="age" class="block text-sm font-medium text-gray-700 mb-2">
          Age <span class="text-red-500">*</span>
        </label>
        <input
          id="age"
          v-model.number="form.age"
          type="number"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          :class="{ 'border-red-500': errors.age }"
          @blur="validateField('age', form.age)"
          @input="clearErrors('age')"
        />
        <p v-if="errors.age" class="mt-1 text-sm text-red-600">
          {{ errors.age }}
        </p>
      </div>

      <!-- Phone Field -->
      <div>
        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
          Phone Number
        </label>
        <input
          id="phone"
          v-model="form.phone"
          type="tel"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          :class="{ 'border-red-500': errors.phone }"
          @blur="validateField('phone', form.phone)"
          @input="clearErrors('phone')"
        />
        <p v-if="errors.phone" class="mt-1 text-sm text-red-600">
          {{ errors.phone }}
        </p>
      </div>

      <!-- Role Field -->
      <div>
        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
          Role <span class="text-red-500">*</span>
        </label>
        <select
          id="role"
          v-model="form.role"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          :class="{ 'border-red-500': errors.role }"
          @change="validateField('role', form.role)"
        >
          <option value="">Select a role</option>
          <option value="admin">Admin</option>
          <option value="user">User</option>
          <option value="moderator">Moderator</option>
        </select>
        <p v-if="errors.role" class="mt-1 text-sm text-red-600">
          {{ errors.role }}
        </p>
      </div>

      <!-- Birth Date Field -->
      <div>
        <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-2">
          Birth Date <span class="text-red-500">*</span>
        </label>
        <input
          id="birth_date"
          v-model="form.birth_date"
          type="date"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          :class="{ 'border-red-500': errors.birth_date }"
          @blur="validateField('birth_date', form.birth_date)"
          @change="clearErrors('birth_date')"
        />
        <p v-if="errors.birth_date" class="mt-1 text-sm text-red-600">
          {{ errors.birth_date }}
        </p>
      </div>

      <!-- Avatar Field -->
      <div>
        <label for="avatar" class="block text-sm font-medium text-gray-700 mb-2">
          Avatar
        </label>
        <input
          id="avatar"
          type="file"
          accept="image/*"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          :class="{ 'border-red-500': errors.avatar }"
          @change="handleFileChange"
        />
        <p v-if="errors.avatar" class="mt-1 text-sm text-red-600">
          {{ errors.avatar }}
        </p>
      </div>

      <!-- Terms Checkbox -->
      <div>
        <div class="flex items-center">
          <input
            id="terms"
            v-model="form.terms"
            type="checkbox"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            :class="{ 'border-red-500': errors.terms }"
            @change="validateField('terms', form.terms)"
          />
          <label for="terms" class="ml-2 block text-sm text-gray-900">
            I agree to the <a href="#" class="text-blue-600 hover:text-blue-500">Terms and Conditions</a>
            <span class="text-red-500">*</span>
          </label>
        </div>
        <p v-if="errors.terms" class="mt-1 text-sm text-red-600">
          {{ errors.terms }}
        </p>
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end">
        <button
          type="submit"
          :disabled="processing"
          class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="processing">Creating...</span>
          <span v-else>Create User</span>
        </button>
      </div>
    </form>

    <!-- Debug Panel (Development only) -->
    <div v-if="showDebug" class="mt-8 p-4 bg-gray-100 rounded-lg">
      <h3 class="text-lg font-semibold mb-2">Debug Information</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <h4 class="font-medium mb-2">Form Data:</h4>
          <pre class="text-xs bg-white p-2 rounded border overflow-auto">{{ JSON.stringify(form, null, 2) }}</pre>
        </div>
        <div>
          <h4 class="font-medium mb-2">Validation Rules:</h4>
          <pre class="text-xs bg-white p-2 rounded border overflow-auto">{{ JSON.stringify(validationRules, null, 2) }}</pre>
        </div>
        <div>
          <h4 class="font-medium mb-2">Current Errors:</h4>
          <pre class="text-xs bg-white p-2 rounded border overflow-auto">{{ JSON.stringify(errors, null, 2) }}</pre>
        </div>
        <div>
          <h4 class="font-medium mb-2">TypeScript Interface:</h4>
          <pre class="text-xs bg-white p-2 rounded border overflow-auto">{{ $page.props.typeScriptInterface }}</pre>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useValidation } from '@/composables/useValidation';

// Form data
const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  age: null as number | null,
  phone: '',
  role: '',
  birth_date: '',
  avatar: null as File | null,
  terms: false,
});

// Validation composable
const {
  validationRules,
  errors,
  validate,
  validateField,
  clearErrors
} = useValidation();

// Component state
const processing = ref(false);
const showDebug = ref (false);

// Handle file input change
const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0] || null;
  form.avatar = file;

  if (file) {
    validateField('avatar', file);
  } else {
    clearErrors('avatar');
  }
};

// Handle form submission
const submit = () => {
  // Clear previous errors
  clearErrors();

  // Validate form data
  const isValid = validate(form.data());

  if (!isValid) {
    console.log('Form validation failed:', errors);
    return;
  }

  processing.value = true;

  // Submit form using Inertia
  form.post(route('users.store'), {
    onFinish: () => {
      processing.value = false;
    },
    onError: (errors) => {
      // Handle server-side validation errors
      console.log('Server validation errors:', errors);
    },
    onSuccess: () => {
      console.log('User created successfully');
    }
  });
};

// Additional validation helpers
const validateConfirmPassword = () => {
  if (form.password !== form.password_confirmation) {
    errors.value.password_confirmation = 'Passwords do not match';
    return false;
  }
  delete errors.value.password_confirmation;
  return true;
};

// Watch for password changes to validate confirmation
import { watch } from 'vue';
watch([() => form.password, () => form.password_confirmation], () => {
  if (form.password_confirmation) {
    validateConfirmPassword();
  }
});
</script>
