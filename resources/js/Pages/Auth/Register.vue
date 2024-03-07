<script setup>
import { ref } from 'vue'
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryLink from '@/Components/SecondaryLink.vue'
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};

function cancel() {
  history.back()
}
</script>

<template>
  <GuestLayout>
    <Head title="Register" />

    <form @submit.prevent="submit">

      <!-- Username -->
      <div>
        <InputLabel for="username" value="Username" />
        <TextInput
          id="username"
          type="text"
          class="mt-1 block w-full"
          v-model="form.username"
          required
          autofocus
          autocomplete="username"
        />
        <InputError class="mt-2" :message="form.errors.username" />
      </div>

      <!-- Password -->
      <div class="mt-4">
        <InputLabel for="password" value="Password" />
        <TextInput
          id="password"
          type="password"
          class="mt-1 block w-full"
          v-model="form.password"
          required
          autocomplete="new-password"
        />
        <InputError class="mt-2" :message="form.errors.password" />
      </div>

      <!-- Password confirmation -->
      <div class="mt-4">
        <InputLabel for="password_confirmation" value="Confirm Password" />
        <TextInput
          id="password_confirmation"
          type="password"
          class="mt-1 block w-full"
          v-model="form.password_confirmation"
          required
          autocomplete="new-password"
        />
        <InputError class="mt-2" :message="form.errors.password_confirmation" />
      </div>

      <!-- Name (optional) -->
      <div class="mt-8 border-t border-gray-200 pt-6">
        <InputLabel for="name" value="Name (optional)" />
        <p class="text-sm text-gray-500">This is how we'll address you. Put whatever you want.</p>
        <TextInput
          id="name"
          type="text"
          class="mt-1 block w-full"
          v-model="form.name"
          autocomplete="name"
        />
        <InputError class="mt-2" :message="form.errors.name" />
      </div>

      <!-- Email (optional) -->
      <div class="mt-4">
        <InputLabel for="email" value="Email (optional)" />
        <p class="mr-1 text-sm text-gray-500">If you think you'll need to recover your password in the future.</p>
        <TextInput
          id="email"
          type="email"
          class="mt-1 block w-full"
          v-model="form.email"
          autocomplete="email"
        />
        <InputError class="mt-2" :message="form.errors.email" />
      </div>

      <!-- <div class="mt-6 text-gray-700"> -->
      <!--   <input type="checkbox" class="rounded" id="rules" name="rules" required /> -->
      <!--   <label class="ml-1" for="rules"> -->
      <!--     I acknowledge <a href="#the-rules">the rules</a> and will be nice. -->
      <!--   </label> -->
      <!-- </div> -->

      <div class="flex items-center mt-6">
        <Link
          :href="route('login')"
          class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Already registered?
        </Link>

        <SecondaryLink class="ml-auto" :href="route('home')">
          Cancel
        </SecondaryLink>

        <PrimaryButton class="ml-2" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          Register
        </PrimaryButton>
      </div>
    </form>

    <!-- The rules -->
    <!-- <div class="border-t border-gray-200 mt-8 pt-4 pb-2"> -->
    <!--   <h2 class="text-center text-xl text-gray-700" id="the-rules">The rules</h2> -->
    <!--   <ul class="mt-2 ml-4 list-disc text-gray-800 text-sm space-y-2"> -->
    <!--     <li> -->
    <!--       You can create new positions and figures, and edit and delete the positions and figures you created, but you cannot modify or delete positions and figures created by other users. -->
    <!--     </li> -->
    <!--     <li> -->
    <!--       You'll be civil and use the app in good faith. -->
    <!--     </li> -->
    <!--     <li> -->
    <!--       I reserve the right to override your edits and contributions. -->
    <!--     </li> -->
    <!--   </ul> -->
    <!-- </div> -->

  </GuestLayout>
</template>
