<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue'

const showingNavigationDropdown = ref(false);

</script>

<template>
  <div class="bg-[#fefefe]">
    <div class="min-h-screen max-w-4xl mx-auto px-6">
      <nav class="mx-auto border-b border-gray-100">
        <div class="relative px-2 sm:px-4 sm:pr-6 lg:pr-8">

          <FlashMessage :flash="$page.props.flash" class="h-full" />

          <!-- Desktop navigation menu -->
          <div class="flex justify-between h-16">

            <div class="flex">
              <!-- Navigation Links -->
              <div class="space-x-8 flex">
                <NavLink :href="route('home')" :active="route().current('home')" >
                  Home
                </NavLink>
                <NavLink class="hidden sm:inline-flex" :href="route('figures.index')" :active="route().current('figures.index')" >
                  Figures
                </NavLink>
                <NavLink class="hidden sm:inline-flex" :href="route('positions.index')" :active="route().current('positions.index')" >
                  Positions
                </NavLink>
                <NavLink class="hidden sm:inline-flex" :href="route('figure-sequence.home')" :active="route().current('figure-sequence.home')" >
                  Figure sequence
                </NavLink>
              </div>
            </div>

            <!-- Dropdown with Profile/Log Out links -->
            <!-- Only show for authenticated users -->
            <div
              v-if="$page.props.auth.user"
              class="hidden sm:flex sm:items-center sm:ml-6"
            >
              <!-- Settings Dropdown -->
              <div class="ml-3 relative">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                      >
                        {{ $page.props.auth.user.name ?? $page.props.auth.user.username }}

                        <svg
                          class="ml-2 -mr-0.5 h-4 w-4"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                    </template>

                  <template #content>
                    <DropdownLink :href="route('profile.edit')"> Profile </DropdownLink>
                    <DropdownLink :href="route('logout')" method="post" as="button">
                      Log Out
                    </DropdownLink>
                    </template>
                </Dropdown>
              </div>
            </div>
            <!-- Log In for unauthenticated users -->
            <div
              v-else
              class="hidden sm:inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
            >
              <Link :href="route('login')">
                Log In
              </Link>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
              <button
                @click="showingNavigationDropdown = !showingNavigationDropdown"
                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
              >
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                  <path
                    :class="{
                      hidden: showingNavigationDropdown,
                      'inline-flex': !showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                  />
                  <path
                    :class="{
                      hidden: !showingNavigationDropdown,
                      'inline-flex': showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Mobile navigation menu -->
        <div
          :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
          class="sm:hidden"
        >

          <!-- Links in mobile navigation menu -->
          <div class="pt-2 pb-3 space-y-1">
            <ResponsiveNavLink :href="route('figures.index')" :active="route().current('figures.index')" >
              Figures
            </ResponsiveNavLink>
            <ResponsiveNavLink :href="route('positions.index')" :active="route().current('positions.index')" >
              Positions
            </ResponsiveNavLink>
            <ResponsiveNavLink :href="route('figure-sequence.home')" :active="route().current('figure-sequence.home')" >
              Figure sequence
            </ResponsiveNavLink>
            <!-- Display log in link for unauthenticated users -->
            <ResponsiveNavLink v-if="!$page.props.auth.user" :href="route('login')">
              Log In
            </ResponsiveNavLink>

          </div>

          <!-- Responsive Settings Options -->
          <div
            v-if="$page.props.auth.user"
            class="pt-4 pb-1 border-t border-gray-200"
          >
            <div class="px-4">
              <div class="font-medium text-base text-gray-800">
                {{ $page.props.auth.user.name ?? $page.props.auth.user.username }}
              </div>
              <div class="font-medium text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
            </div>

            <div class="mt-3 space-y-1">
              <ResponsiveNavLink :href="route('profile.edit')"> Profile </ResponsiveNavLink>
              <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                Log Out
              </ResponsiveNavLink>
            </div>
          </div>
        </div>
      </nav>

      <!-- Page Heading -->
      <header class="bg-white shadow mx-auto" v-if="$slots.header">
        <div class="mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <!-- Page Content -->
      <main class="py-6 px-0 sm:px-6">
        <slot />
      </main>
    </div>
  </div>
  </template>
