<script setup>
import throttle from "lodash/throttle";
import { ref, watch } from 'vue'
import { MagnifyingGlassIcon, ChevronDownIcon } from '@heroicons/vue/24/outline'
import {
  Combobox,
  ComboboxLabel,
  ComboboxInput,
  ComboboxButton,
  ComboboxOptions,
  ComboboxOption,
} from '@headlessui/vue'

const props = defineProps({
  options: Array,
  labelText: String,
  modelValue: Object,
  searchKey: {
    type: String,
    default: "name",
  },
  throttlems: {
    type: Number,
    default: 100
  },
  inputClasses: {
    type: String,
    default: "",
  },
  labelClasses: {
    type: String,
    default: "",
  },
})

const emit = defineEmits([
  'update:modelValue',
])

function removeAccents(str) {
  return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
}

const query = ref("")
const filteredOptions = ref(props.options)

defineExpose({ clear })
function clear() { query.value = '' }

function search(query) {
  filteredOptions.value = (query === '')
    ? props.options
    : props.options.filter((option) => {
      return removeAccents(option[props.searchKey].toLowerCase()).includes(removeAccents(query.trim().toLowerCase()))
    })
}

watch(query, throttle(function (value) {
  search(value)
}, props.throttlems))

</script>

<template>
  <Combobox
    :modelValue="modelValue"
    @update:modelValue="value => emit('update:modelValue', value)"
    multiple
  >
    <div class="relative">

      <div>
        <ComboboxLabel class="text-sm font-medium text-gray-700" :class="labelClasses" >
          {{labelText}}
        </ComboboxLabel>

        <div class="relative">
          <ComboboxInput
            class="w-full text-left border-gray-300 rounded-md shadow-sm focus:border focus:border-blue-500 truncate pr-10"
            :class="inputClasses + (modelValue.length == 0 ? ' !text-gray-500' : '')"
            @change="query = $event.target.value"
            :displayValue="(options) => (options && options.length) 
            ? (options.map(option => option[props.searchKey]).join(', '))
            : 'All'"
          />
          <ComboboxButton tabindex="0" class="absolute right-0 px-4 rounded-md h-full focus:outline-none focus:border-2 focus:border-blue-500 active:border-0" >
            <ChevronDownIcon class="w-5 h-5 text-gray-600 shrink-0"/>
          </ComboboxButton>
        </div>

      </div>

      <ComboboxOptions class="absolute z-50 overflow-hidden mt-0.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <!-- Options passed as props -->
        <ComboboxOption
          v-for="option in filteredOptions"
          :key="option.id"
          :value="option"
          class="text-left cursor-pointer"
          v-slot="{ active, selected }"
        >
          <li :class="{
            'px-4': true,
            'py-1.5': true,
            'bg-blue-500 text-white': active,
            'text-gray-500': !selected,
            'font-bold': selected,
            }"
          >
            <p :class="{
              'bg-blue-500 text-white': active,
              'text-gray-700': !selected,
              }">
              {{option[props.searchKey]}}
            </p>
          </li>
        </ComboboxOption>
      </ComboboxOptions>

    </div>

  </Combobox>
</template>
