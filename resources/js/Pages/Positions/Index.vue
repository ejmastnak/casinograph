<script setup>
import { ref, watch, computed, onBeforeUnmount, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import fuzzysort from 'fuzzysort'
import throttle from "lodash/throttle";
import { Head } from '@inertiajs/vue3'
import DeleteDialog from "@/Components/DeleteDialog.vue";
import MyLink from '@/Components/MyLink.vue'
import SecondaryLink from '@/Components/SecondaryLink.vue'
import PlainButton from '@/Components/PlainButton.vue'
import TextInput from '@/Components/TextInput.vue'
import MultiSelect from '@/Components/MultiSelect.vue'
import { PencilSquareIcon, TrashIcon, PlusCircleIcon, MagnifyingGlassIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  positions: Array,
  position_families: Array,
  show_edit_delete_icons: Boolean,
})

// For filtering Positions by PositionFamily
const selectedPositionFamilies = ref([])
const selectedPositionFamilyIDs = computed(() => {
  return selectedPositionFamilies.value.map(positionFamily => positionFamily.id)
})
function shouldDisplay(position) {
  return (selectedPositionFamilies.value.length === 0) || selectedPositionFamilyIDs.value.includes(position.position_family_id)
}

function removeAccents(str) {
  return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
}

const positionSearchInput = ref(null)
const positionSearchQuery = ref("")
const fuzzysortOptions = {
  keys: ['normalized_name'],
  all: true,       // return all items for empty search query
  limit: 100,      // maximum number of search results
  threshold: -1000  // omit results with scores below threshold
}
const normalized_positions = computed(() => {
  return props.positions.map(position => ({
    id: position.id,
    name: position.name,
    normalized_name: removeAccents(position.name),
    position_family_id: position.position_family_id,
    position_family: position.position_family,
  }))
})
// Convert to fuzzysort format with nested obj key
const filteredPositions = ref(normalized_positions.value.map((position) => ({
  obj: position
})))

function search(query) {
  filteredPositions.value = fuzzysort.go(removeAccents(query.trim()), normalized_positions.value, fuzzysortOptions)
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

let idToDelete = ref(null)
const deleteDialog = ref(null)
function deletePosition() {
  if (idToDelete.value) {
    router.delete(route('positions.destroy', idToDelete.value), {
      onSuccess: () => {
        search(positionSearchQuery.value)  // remove deleted item from display
      }
    });
  }
  idToDelete.value = null
}


// Preserve scroll position and search queries when leaving page.
onBeforeUnmount(() => {
  sessionStorage.setItem('positionsIndexScrollX', window.scrollX)
  sessionStorage.setItem('positionsIndexScrollY', window.scrollY)
  sessionStorage.setItem('positionsIndexSearchQuery', positionSearchQuery.value)
})

// Preserve scroll position and search queries on manual page reload.
window.onbeforeunload = function() {
  sessionStorage.setItem('positionsIndexScrollX', window.scrollX)
  sessionStorage.setItem('positionsIndexScrollY', window.scrollY)
  sessionStorage.setItem('positionsIndexSearchQuery', positionSearchQuery.value)
}

// Restore scroll position and search queries when loading page
onMounted(() => {
  const scrollX = sessionStorage.getItem('positionsIndexScrollX')
  const scrollY = sessionStorage.getItem('positionsIndexScrollY')
  if (scrollX && scrollY) {
    setTimeout(() => {
      window.scrollTo(scrollX, scrollY)
    })
  }

  const storedSearchQuery = sessionStorage.getItem('positionsIndexSearchQuery');
  if (storedSearchQuery) {
    positionSearchQuery.value = storedSearchQuery
    search(positionSearchQuery.value)
  }
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
        <p class="mt-1 text-sm text-gray-500 max-w-xs mr-2">This is a list of all positions. You can use this page to view, edit, delete, or add new positions.</p>
      </div>

      <SecondaryLink class="ml-auto h-fit" :href="route('positions.create')" >
        <PlusCircleIcon class="-ml-1 text-gray-600 h-6 w-6" />
        <p class="ml-1 whitespace-nowrap">New <span class="hidden sm:inline">position</span></p>
      </SecondaryLink>
    </div>

    <!-- Main panel for table and search -->
    <div class="mt-6 pt-3 border border-gray-100 shadow-md rounded-lg overflow-auto">

      <!-- Search and filter components -->
      <div class="px-3 flex flex-wrap items-end space-y-2 gap-x-4">

        <!-- Fuzzy search by name -->
        <div class="w-80 sm:w-fit">
          <label for="position-search-query" class="ml-1 text-sm text-gray-500">
            Search by name
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <MagnifyingGlassIcon class="w-5 h-5 text-gray-500" />
            </div>
            <TextInput
              class="w-80 max-w-full py-1.5 pl-10 text-gray-700 bg-gray-50"
              type="text"
              id="position-search-query"
              v-model="positionSearchQuery"
              ref="positionSearchInput"
            />
          </div>
        </div>

        <!-- PositionFamily Filter -->
        <MultiSelect
          :options="position_families"
          width="w-40"
          labelText="Filter by family"
          :modelValue="selectedPositionFamilies"
          @update:modelValue="newValue => selectedPositionFamilies = newValue"
        />

        <!-- Clear filters buttom -->
        <div class="flex items-center">
          <label for="clear-filters" class="sr-only">
            Clear filters
          </label>
          <PlainButton
            id="clear-filters"
            class="!bg-gray-50"
            @click="clearFilters"
          >
            <XMarkIcon class="-ml-1 w-5 h-5 text-gray-600 shrink-0" />
            <span class="ml-1 text-gray-600 whitespace-nowrap">Clear filters</span>
          </PlainButton>
        </div>

      </div>

      <table class="mt-6 md:table-fixed w-full text-sm sm:text-base text-left">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
          <tr>
            <th
              scope="col"
              class="px-6 py-2 bg-blue-100"
              :class="show_edit_delete_icons ? 'w-8/12' : 'w-9/12'"
            >
              Name
            </th>
            <th
              scope="col"
              class="px-6 py-2 bg-blue-200 w-3/12"
            >
              Family
            </th>
            <!-- For trash and edit icons -->
            <th v-if="show_edit_delete_icons" scope="col" class="bg-blue-100 w-1/12" />
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
            <td class="px-6 py-2 text-gray-600">
              {{position.obj.position_family?.name}}
            </td>
            <!-- Delete/Edit -->
            <td v-if="show_edit_delete_icons">
              <div class="flex items-center">
                <MyLink :href="route('positions.edit', position.obj.id)">
                  <PencilSquareIcon class="text-gray-500 h-5 w-5"/>
                </MyLink>
                <button
                  type="button"
                  class="ml-0.5"
                  @click="idToDelete = position.obj.id; deleteDialog.open()"
                >
                  <TrashIcon class="text-gray-500 h-5 w-5"/>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <p v-show="numDisplayedPositions === 0" class="px-6 py-4" >
        No results found. Try a less restrictive filter or search?
      </p>

    </div>

    <DeleteDialog
      ref="deleteDialog"
      description="position"
      @delete="deletePosition"
      @cancel="idToDelete = null"
    />

  </div>
</template>
