<script setup>
import fuzzysort from 'fuzzysort'
import throttle from "lodash/throttle";
import { ref, computed, watch, onMounted } from 'vue'
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
  fuzzyLimit: {  // max number of search results
    type: Number,
    default: 15
  },
  fuzzyThreshold: {  // omit results with scores below threshold
    type: Number,
    default: -1000
  },
  throttlems: {  // ms amount used to throttle fuzzy search
    type: Number,
    default: 300
  },
  inputClasses: {
    type: String,
    default: "",
  },
  bespokeDisplayForFigures: {  // shows from/to position name in addition to figure name
    type: Boolean,
    default: false,
  }
})

const emit = defineEmits([
  'update:modelValue',
])

const selectedValue = computed({
  get() {
    return props.modelValue;
  },
  set(option) {
    emit('update:modelValue', option.obj.original)
  }
})

function removeAccents(str) {
  return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
}

// For fuzzy search over option names
const query = ref("")
const filteredOptions = ref([])
const fuzzyOptions = {
  all: true,
  // key: props.searchKey,
  key: 'normalized',
  limit: props.fuzzyLimit,
  threshold: props.fuzzyThreshold
}

const normalizedOptions = computed(() => {
  return props.options.map(option => ({
    original: option,
    normalized: removeAccents(option[props.searchKey]),
  }))
})

function search(query) {
  filteredOptions.value = fuzzysort.go(query.trim(), normalizedOptions.value, fuzzyOptions)
}

onMounted(() => { search(query.value) })

watch(query, throttle(function (value) {
  search(value)
}, props.throttlems))

// Refreshes filteredOptions when props.options change
watch(normalizedOptions, () => {
  search(query.value)
})

</script>

<template>
  <Combobox v-model="selectedValue">
    <div class="relative">

      <div>
        <ComboboxLabel class="text-sm font-medium text-gray-700">
          {{labelText}}
        </ComboboxLabel>

        <div class="relative">
          <ComboboxInput
            class="w-full border border-gray-300 rounded-md shadow-sm focus:border focus:border-blue-500"
            :class="inputClasses"
            @change="query = $event.target.value"
            :displayValue="(option) => option ? option[props.searchKey] : ''"
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
              {{option.obj.original[props.searchKey]}}
            </p>
            <div v-if="bespokeDisplayForFigures" class="text-sm">
              {{option.obj.original.from_position.name}}
              to
              {{option.obj.original.to_position.name}}
            </div>
          </li>
        </ComboboxOption>
      </ComboboxOptions>

    </div>

  </Combobox>



</template>
