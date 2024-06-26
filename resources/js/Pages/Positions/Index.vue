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
import MultiCombobox from '@/Components/MultiCombobox.vue'
import { TrashIcon, PlusCircleIcon, MagnifyingGlassIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  positions: Array,
  position_families: Array,
  can_delete: Boolean,
})

// For filtering Positions by PositionFamily
const selectedPositionFamilies = ref([])
const selectedPositionFamilyIds = computed(() => {
  return selectedPositionFamilies.value.map(positionFamily => positionFamily.id)
})
function shouldDisplay(position) {
  return (selectedPositionFamilies.value.length === 0) || selectedPositionFamilyIds.value.includes(position.position_family_id)
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
        // Set selectedPositionFamilies to only those previously selected
        // position families still present in props.position_families after
        // deleting the last position. This handles the edge case where a user
        // filters by position family and deletes last position in a family
        // (causing position family itself to be deleted in backend). Stored
        // selectedPositionFamilies then includes a position family that no
        // longer exists in props.position_families.
        const positionFamilyIds = props.position_families.map(p => p.id);
        selectedPositionFamilies.value = selectedPositionFamilies.value.filter(f => positionFamilyIds.includes(f.id));
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
  sessionStorage.setItem('positionsIndexSelectedFamilyIds', JSON.stringify(selectedPositionFamilyIds.value))
})

// Preserve scroll position and search queries on manual page reload.
window.onbeforeunload = function() {
  sessionStorage.setItem('positionsIndexScrollX', window.scrollX)
  sessionStorage.setItem('positionsIndexScrollY', window.scrollY)
  sessionStorage.setItem('positionsIndexSearchQuery', positionSearchQuery.value)
  sessionStorage.setItem('positionsIndexSelectedFamilyIds', JSON.stringify(selectedPositionFamilyIds.value))
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

  if (sessionStorage.getItem('positionsIndexSelectedFamilyIds')) {
    const storedSelectedPositionFamilyIds = JSON.parse(sessionStorage.getItem('positionsIndexSelectedFamilyIds'))
    props.position_families.forEach((positionFamily) => {
      if (storedSelectedPositionFamilyIds.includes(positionFamily.id)) {
        selectedPositionFamilies.value.push(positionFamily)
      }
    })
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
    <div class="mt-6">

      <!-- Search and filter components -->
      <div class="flex flex-wrap items-end space-y-2 gap-x-4">

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
              class="w-80 max-w-full py-2 pl-10 text-gray-700 bg-gray-50"
              type="text"
              id="position-search-query"
              v-model="positionSearchQuery"
              ref="positionSearchInput"
            />
          </div>
        </div>

        <!-- PositionFamily Filter -->
        <MultiCombobox
          :options="position_families"
          class="w-48"
          labelText="Filter by family"
          labelClasses="ml-1 !text-sm !font-normal !text-gray-500"
          inputClasses="py-2 text-gray-700 bg-gray-50"
          :modelValue="selectedPositionFamilies"
          @update:modelValue="newValues => selectedPositionFamilies = newValues"
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
          <XMarkIcon class="-ml-2 w-6 h-6 text-gray-500 shrink-0" />
          <p class="ml-0.5 text-gray-600 whitespace-nowrap">
            Clear filters
          </p>
          </PlainButton>
        </div>

      </div>

      <!-- Listing of Positions -->
      <!-- Layouts: -->
      <!-- pre-sm: (name, family) (7, 5) -->
      <!-- sm, !$user: (name, family) (8, 4) -->
      <!-- sm, $user: (name, family, edit/delete) (8, 3, 1) -->
      <div class="mt-6 min-w-[380px]">

        <!-- Header -->
        <div class="grid grid-cols-12 text-xs uppercase text-gray-700 font-semibold">
          <p class="col-span-7 sm:col-span-8 px-3 sm:px-5 py-3 bg-blue-50">Name</p>
          <p 
            class="px-3 sm:px-5 py-3 bg-blue-100"
            :class="can_delete ? 'col-span-5 sm:col-span-3' : 'col-span-5 sm:col-span-4'"
          >
            Family
          </p>
          <p v-if="can_delete" class="hidden sm:block sm:col-span-1 py-3 bg-blue-50" />
        </div>

        <div
          v-for="(position, idx) in filteredPositions"
          :key="position.obj.id"
          v-show="shouldDisplay(position.obj)"
          class="grid grid-cols-12 text-gray-800 text-sm sm:text-base border-b py-1.5"
        >
          <!-- Name -->
          <div class="col-span-7 sm:col-span-8 px-3 sm:px-4">
            <MyLink class="inline-block" :href="route('positions.show', position.obj.id)">
              {{position.obj.name}}
            </MyLink>
          </div>
          <!-- Position family -->
          <div
            class="px-3 sm:px-4 text-gray-600"
            :class="can_delete ? 'col-span-5 sm:col-span-3' : 'col-span-5 sm:col-span-4'"
          >
            <MyLink
              v-if="position.obj.position_family"
              :href="route('position-families.show', position.obj.position_family.id)"
            >
              {{position.obj.position_family.name}}
            </MyLink>
          </div>
          <!-- Delete button -->
          <div v-if="can_delete" class="hidden sm:block sm:col-span-1 px-1">
            <PlainButton
              class="text-gray-500 hover:text-red-600"
              @click="idToDelete = position.obj.id; deleteDialog.open()"
            >
              <TrashIcon class="h-5 w-5"/>
            </PlainButton>
          </div>
        </div>
      </div>

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
