<script setup>
import { ref, computed } from 'vue'
import { ChevronDownIcon, CheckIcon } from '@heroicons/vue/24/outline'
import {
  Listbox,
  ListboxLabel,
  ListboxButton,
  ListboxOptions,
  ListboxOption,
} from '@headlessui/vue'

const props = defineProps({
  options: Array,
  labelText: String,
  modelValue: Array,
  displayKey: {
    type: String,
    default: "name",
  },
  labelClasses: {
    type: String,
    default: "",
  },
  inputClasses: {
    type: String,
    default: "",
  },
  width: {
    type: String,
    default: "w-44",
  },
})

const emit = defineEmits([
  'update:modelValue',
])

</script>

<template>
  <div>
    <Listbox
      :modelValue="modelValue"
      @update:modelValue="value => emit('update:modelValue', value)"
      multiple
    >
      <ListboxLabel class="ml-1 text-sm text-gray-500" :class="labelClasses">
        {{labelText}}
      </ListboxLabel>
      <ListboxButton :class="width + ' ' + inputClasses" class="flex text-left bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 px-4 py-2" >
        <span class="mr-2 truncate whitespace-nowrap">
          {{ modelValue.length === 0 ? "All" : options.find(option => option.id == modelValue[0].id)[displayKey] }}{{ modelValue.length > 1 ? ", ..." : ""}}
        </span>
        <ChevronDownIcon class="ml-auto w-5 h-5 text-gray-500 shrink-0" />
      </ListboxButton>
      <ListboxOptions :class="width" class="absolute mt-0.5 text-sm bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        <ListboxOption
          v-for="option in options"
          :key="option.id"
          :value="option"
          class="text-left cursor-pointer hover:bg-gray-100"
          v-slot="{ active, selected }"
        >
          <li :class="{
            'flex': true,
            'items-center': true,
            'px-4': true,
            'py-1.5': true,
            'bg-blue-500 text-white': active,
            'text-gray-500': !selected,
            'font-bold': selected,
          }"
          >
            {{ option[displayKey] }}
            <CheckIcon class="ml-auto w-5 h-5" v-show="selected" />
          </li>
        </ListboxOption>
      </ListboxOptions>
    </Listbox>
  </div>
</template>
