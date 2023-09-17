<script setup>
import { ref, watch, computed } from 'vue'
import fuzzysort from 'fuzzysort'
import throttle from "lodash/throttle";
import { Head } from '@inertiajs/vue3'
import MyLink from '@/Components/MyLink.vue'
import SecondaryLink from '@/Components/SecondaryLink.vue'
import PlainButton from '@/Components/PlainButton.vue'
import TextInput from '@/Components/TextInput.vue'
import MultiSelect from '@/Components/MultiSelect.vue'
import { PencilSquareIcon, TrashIcon, PlusCircleIcon, MagnifyingGlassIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  positions: Array,
  position_families: Array,
})

// For filtering Positions by PositionFamily
const selectedPositionFamilies = ref([])
const selectedPositionFamilyIDs = computed(() => {
  return selectedPositionFamilies.value.map(positionFamily => positionFamily.id)
})
function shouldDisplay(position) {
  return (selectedPositionFamilies.value.length === 0) || selectedPositionFamilyIDs.value.includes(position.position_family_id)
}

// Convert to fuzzysort format
const filteredPositions = ref(props.positions.map((position) => ({
  obj: position
})))

const positionSearchInput = ref(null)
const positionSearchQuery = ref("")
const fuzzysortOptions = {
  keys: ['name'],
  all: true,       // return all items for empty search query
  limit: 100,      // maximum number of search results
  threshold: -1000  // omit results with scores below threshold
}
function search(query) {
  filteredPositions.value = fuzzysort.go(query.trim(), props.positions, fuzzysortOptions)
}
watch(positionSearchQuery, throttle(function (value) {
  search(value)
}, 400))

function clearFilters() {
  positionSearchQuery.value = ""
  selectedPositionFamilies.value = []
  search(positionSearchQuery.value)
  positionSearchInput.value.focus()
}

// Used to conditionally display a "No results found" message.
const numDisplayedPositions = computed(() => {
  return filteredPositions.value.filter(position => shouldDisplay(position.obj)).length
})

</script>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
export default {
  layout: AppLayout,
}
</script>

<template>
  <div class="">
    <Head title="Positions" />

    <div class="flex">
      <div class="">
        <h1 class="text-xl">Positions</h1>
        <p class="mt-1 text-sm text-gray-500 max-w-xs">This is a list of all positions. You can use this page to view, edit, delete, or add new positions.</p>
      </div>

      <SecondaryLink class="ml-auto h-fit" :href="route('positions.create')" >
        <PlusCircleIcon class="-ml-1 text-gray-600 h-6 w-6" />
        <p class="ml-1 whitespace-nowrap">New position</p>
      </SecondaryLink>
    </div>

    <!-- Main panel for table and search -->
    <div class="mt-6 border border-gray-100 shadow-md rounded-lg">
      <div class="m-3 flex">
        <!-- Fuzzy search by name -->
        <div>
          <label for="position-search-query" class="ml-1 text-sm text-gray-500">
            Search by name
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <MagnifyingGlassIcon class="w-5 h-5 text-gray-500" />
            </div>
            <TextInput
              class="py-1.5 pl-10 text-gray-700 w-72 bg-gray-50"
              type="text"
              id="position-search-query"
              v-model="positionSearchQuery"
              ref="positionSearchInput"
            />
          </div>
        </div>


        <div class="flex items-end ml-auto">

          <!-- PositionFamily Filter -->
          <MultiSelect
            :options="position_families"
            width="w-36"
            labelText="Filter by family"
            :modelValue="selectedPositionFamilies"
            @update:modelValue="newValue => selectedPositionFamilies = newValue"
            class="ml-4"
          />

          <!-- Clear filters buttom -->
          <div class="ml-4 flex items-center">
            <label for="clear-filters" class="sr-only">
              Clear filters
            </label>
            <PlainButton
              id="clear-filters"
              class="!bg-gray-50"
              @click="clearFilters"
            >
              <XMarkIcon class="-ml-1 w-5 h-5 text-gray-600 shrink-0" />
              <span class="ml-1 text-gray-600">Clear filters</span>
            </PlainButton>
          </div>
        </div>

      </div>

      <table class="mt-6 sm:table-fixed w-full text-sm sm:text-base text-left">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-2 w-8/12 bg-blue-100">
              Name
            </th>
            <th scope="col" class="px-6 py-2 w-3/12 bg-blue-200">
              Family
            </th>
            <!-- For trash and edit icons -->
            <th scope="col" class="bg-blue-100" />
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="position in filteredPositions"
            :key="position.obj.id"
            v-show="shouldDisplay(position.obj)"
            class="bg-white border-b"
          >
            <!-- Name -->
            <td scope="row" class="px-5 py-2">
              <MyLink :href="route('positions.show', position.obj.id)">
                {{position.obj.name}}
              </MyLink>
            </td>
            <!-- PositionFamily -->
            <td class="px-6 py-2 text-gray-700">
              {{position.obj.position_family?.name}}
            </td>
            <!-- Delete/Edit -->
            <td>
              <div class="flex items-center">
                <MyLink :href="route('positions.edit', position.obj.id)">
                  <PencilSquareIcon class="text-gray-500 h-5 w-5"/>
                </MyLink>
                <MyLink as="button" method="delete" :href="route('positions.destroy', position.obj.id)">
                  <TrashIcon class="text-gray-500 h-5 w-5"/>
                </MyLink>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <p v-show="numDisplayedPositions === 0" class="px-6 py-4" >
        No results found. Try a less restrictive filter or search?
      </p>


    </div>

  </div>
</template>
