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
    email: '',
    password: '',
    password_confirmation: '',
});

const theRules = ref(null)
function focusRules() {
  theRules.value.focus()
}

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Name" />
                <p class="text-sm text-gray-500">This can be whatever you want.</p>
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />
                <p class="mr-1 text-sm text-gray-500">Only used as your username when logging in—put a fake email if you like, as long as you can remember it.</p>
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

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

            <div class="mt-4 text-gray-700">
                <input type="checkbox" class="rounded" id="rules" name="rules" required />
                <label class="ml-1" for="rules">
                    I acknowledge <a href="#the-rules">the rules</a> and will be nice.
                </label>
            </div>

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
        <div class="border-t border-gray-200 mt-8 pt-4 pb-2">
            <h2 class="text-center text-xl text-gray-700" id="the-rules">The rules</h2>
            <ul class="mt-2 ml-4 list-disc text-gray-800 text-sm space-y-2">
                <li>
                    You can create new positions and figures, and edit and delete the positions and figures you created, but you cannot modify or delete positions and figures created by other users.
                </li>
                <li>
                    You'll be civil and use the app in good faith.
                </li>
                <li>
                    I reserve the right to override your edits and contributions.
                </li>
            </ul>
        </div>

    </GuestLayout>
</template>
