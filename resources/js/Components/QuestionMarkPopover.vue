<script setup>
import { QuestionMarkCircleIcon } from '@heroicons/vue/24/outline'
import { Popover, PopoverButton, PopoverPanel, PopoverOverlay } from '@headlessui/vue'
</script>

<template>
  <div class="relative">
    <Popover>
      <PopoverButton class="text-gray-500 rounded-sm hover:text-blue-700">
        <QuestionMarkCircleIcon class="-translate-y-px inline w-6 h-6 shrink-0" />
      </PopoverButton>

      <PopoverOverlay class="fixed inset-0 bg-black opacity-20 z-40" />
      <PopoverPanel class="absolute left-0 mt-0.5 p-4 rounded-xl overflow-hidden border border-gray-400 bg-white shadow text-gray-800 z-50">
        <slot />
      </PopoverPanel>
    </Popover>
  </div>
</template>
