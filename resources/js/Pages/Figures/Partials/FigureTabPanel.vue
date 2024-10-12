<script setup>
import { ref, watch, computed, onBeforeUnmount, onMounted} from 'vue'
import { router } from '@inertiajs/vue3'
import fuzzysort from 'fuzzysort'
import throttle from "lodash/throttle";
import { MagnifyingGlassIcon, XMarkIcon, TrashIcon } from '@heroicons/vue/24/outline'
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import DeleteDialog from "@/Components/DeleteDialog.vue";
import TextInput from '@/Components/TextInput.vue'
import MultiCombobox from '@/Components/MultiCombobox.vue'
import PlainButton from '@/Components/PlainButton.vue'
import MyLink from '@/Components/MyLink.vue'

const props = defineProps({
  figures: Array,
  figure_families: Array,
  positions: Array,
  compound: Boolean,  // is this panel for base or compound figures?
})

// For filtering Figures by FigureFamily
const figureFamilyFilterRef = ref(null)
const selectedFigureFamilies = ref([])
const selectedFigureFamilyIds = computed(() => {
  return selectedFigureFamilies.value.map(figureFamily => figureFamily.id)
})

// For filtering Figures by from position
const fromPositionFilterRef = ref(null)
const selectedFromPositions = ref([])
const selectedFromPositionIds = computed(() => {
  return selectedFromPositions.value.map(fromPosition => fromPosition.id)
})

// For filtering Figures by to position
const toPositionFilterRef = ref(null)
const selectedToPositions = ref([])
const selectedToPositionIds = computed(() => {
  return selectedToPositions.value.map(toPosition => toPosition.id)
})

// Updated in onMounted based on stored filter parameters
const showMoreFiltersDisclosureIsOpen = ref(false)
const filtersActive = computed(() => {
  return (selectedFigureFamilies.value.length > 0) || (selectedFromPositions.value.length > 0) || (selectedToPositions.value.length > 0)
})

function shouldDisplay(figure) {
  return ((selectedFigureFamilies.value.length === 0) || selectedFigureFamilyIds.value.includes(figure.figure_family_id))
    && ((selectedFromPositions.value.length === 0) || selectedFromPositionIds.value.includes(figure.from_position_id))
    && ((selectedToPositions.value.length === 0) || selectedToPositionIds.value.includes(figure.to_position_id))
}

function removeAccents(str) {
  return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
}

const figureSearchInput = ref(null)
const figureSearchQuery = ref("")
const fuzzysortOptions = {
  keys: ['normalized_name'],
  all: true,       // return all items for empty search query
  limit: 100,      // maximum number of search results
  threshold: -1000  // omit results with scores below threshold
}
const normalized_figures = computed(() => {
  return props.figures.map(figure => ({
    id: figure.id,
    name: figure.name,
    weight: figure.weight,
    normalized_name: removeAccents(figure.name),
    figure_family_id: figure.figure_family_id,
    figure_family: figure.figure_family,
    from_position_id: figure.from_position_id,
    from_position: figure.from_position,
    to_position_id: figure.to_position_id,
    to_position: figure.to_position,
  }))
})
// Convert to fuzzysort format with nested obj key
const filteredFigures = ref(normalized_figures.value.map((figure) => ({
  obj: figure
})))

function search(query) {
  filteredFigures.value = fuzzysort.go(removeAccents(query.trim()), normalized_figures.value, fuzzysortOptions)
}
watch(figureSearchQuery, throttle(function (value) {
  search(value)
}, 400))

function clearFilters() {
  if (figureFamilyFilterRef.value) figureFamilyFilterRef.value.clear();
  if (fromPositionFilterRef.value) fromPositionFilterRef.value.clear();
  if (toPositionFilterRef.value) toPositionFilterRef.value.clear();
  figureSearchQuery.value = ""
  selectedFigureFamilies.value = []
  selectedFromPositions.value = []
  selectedToPositions.value = []
  search(figureSearchQuery.value)
  figureSearchInput.value.focus()
}

// Used to conditionally display a "No results found" message.
const numDisplayedFigures = computed(() => {
  return filteredFigures.value.filter(figure => shouldDisplay(figure.obj)).length
})

let idToDelete = ref(null)
const deleteDialog = ref(null)
function deleteFigure() {
  if (idToDelete.value) {
    router.delete(route((props.compound ? 'compound-figures.destroy' : 'figures.destroy'), idToDelete.value), {
      onSuccess: () => {
        // Set selectedFigureFamilies to only those previously selected
        // figure families still present in props.figure_families after
        // deleting the last figure. This handles the edge case where a user
        // filters by figure family and deletes last figure in a family
        // (causing figure family itself to be deleted in backend). Stored
        // selectedFigureFamilies then includes a figure family that no
        // longer exists in props.figure_families.
        const figureFamilyIds = props.figure_families.map(f => f.id);
        selectedFigureFamilies.value = selectedFigureFamilies.value.filter(f => figureFamilyIds.includes(f.id));
        search(figureSearchQuery.value)  // remove deleted item from display
      }
    });
  }
  idToDelete.value = null
}


// Preserve search queries when leaving page.
onBeforeUnmount(() => {
  sessionStorage.setItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSearchQuery', figureSearchQuery.value)
  sessionStorage.setItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSelectedFamilyIds', JSON.stringify(selectedFigureFamilyIds.value))
  sessionStorage.setItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSelectedFromPositionIds', JSON.stringify(selectedFromPositionIds.value))
  sessionStorage.setItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSelectedToPositionIds', JSON.stringify(selectedToPositionIds.value))
})

// Preserve search queries on manual page reload.
window.onbeforeunload = function() {
  sessionStorage.setItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSearchQuery', figureSearchQuery.value)
  sessionStorage.setItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSelectedFamilyIds', JSON.stringify(selectedFigureFamilyIds.value))
  sessionStorage.setItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSelectedFromPositionIds', JSON.stringify(selectedFromPositionIds.value))
  sessionStorage.setItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSelectedToPositionIds', JSON.stringify(selectedToPositionIds.value))
}

// Restore search queries when loading page
onMounted(() => {
  const storedSearchQuery = sessionStorage.getItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSearchQuery');
  if (storedSearchQuery) {
    figureSearchQuery.value = storedSearchQuery
    search(figureSearchQuery.value)
  }

  // Restore selected figure families
  if (sessionStorage.getItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSelectedFamilyIds')) {
    const storedSelectedFigureFamilyIds = JSON.parse(sessionStorage.getItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSelectedFamilyIds'))
    props.figure_families.forEach((figureFamily) => {
      if (storedSelectedFigureFamilyIds.includes(figureFamily.id)) {
        selectedFigureFamilies.value.push(figureFamily)
      }
    })
  }

  // Restore selected from positions
  if (sessionStorage.getItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSelectedFromPositionIds')) {
    const storedSelectedFromPositionIds = JSON.parse(sessionStorage.getItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSelectedFromPositionIds'))
    props.positions.forEach((position) => {
      if (storedSelectedFromPositionIds.includes(position.id)) {
        selectedFromPositions.value.push(position)
      }
    })
  }

  // Restore selected to positions
  if (sessionStorage.getItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSelectedToPositionIds')) {
    const storedSelectedToPositionIds = JSON.parse(sessionStorage.getItem((props.compound ? 'compoundFigures' : 'figures') + 'IndexSelectedToPositionIds'))
    props.positions.forEach((position) => {
      if (storedSelectedToPositionIds.includes(position.id)) {
        selectedToPositions.value.push(position)
      }
    })
  }

  // Show filters when rendering page if queries were stored in history
  showMoreFiltersDisclosureIsOpen.value = (selectedFigureFamilies.value.length > 0) || (selectedFromPositions.value.length > 0) || (selectedToPositions.value.length > 0)

})


</script>

<template>
  <!-- Main panel for table and search -->
  <div>

    <!-- Search and filter components -->
    <div class="flex flex-wrap items-end space-y-2 gap-x-4">

      <!-- Fuzzy search by name -->
      <div>
        <label :for="(compound ? 'compound-' : '') + 'figure-search-query'" class="ml-1 text-sm text-gray-500">
          Search by name
        </label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <MagnifyingGlassIcon class="w-5 h-5 text-gray-500" />
          </div>
          <TextInput
            class="py-2 pl-10 text-gray-700 w-80 bg-gray-50"
            type="text"
            :id="(compound ? 'compound-' : '') + 'figure-search-query'"
            v-model="figureSearchQuery"
            ref="figureSearchInput"
          />
        </div>
      </div>

      <Disclosure v-slot="{ open }" >
        <DisclosureButton
          @click="showMoreFiltersDisclosureIsOpen = !showMoreFiltersDisclosureIsOpen"
          class="w-[7rem] inline-flex items-center px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
          :class="{
            '!font-semibold': (!showMoreFiltersDisclosureIsOpen && filtersActive)
          }"
        >
          {{showMoreFiltersDisclosureIsOpen ? 'Hide filters' : 'More filters'}}
        </DisclosureButton>

        <!-- Clear filters button (located so as to appear after -->
        <!-- More Filters button but before extra filter elements) -->
        <div class="flex items-center h-fit">
          <label :for="(compound ? 'compound-' : '') + 'clear-filters'" class="sr-only">
            Clear filters
          </label>
          <PlainButton
            :id="(compound ? 'compound-' : '') + 'clear-filters'"
            class="!bg-gray-50"
            @click="clearFilters"
          >
            <XMarkIcon class="-ml-2 w-6 h-6 text-gray-500 shrink-0" />
            <p class="ml-0.5 text-gray-700 whitespace-nowrap">
              Clear filters
            </p>
          </PlainButton>
        </div>

        <div v-show="showMoreFiltersDisclosureIsOpen">
          <DisclosurePanel static class="flex flex-wrap items-end space-y-2 gap-x-4">

            <!-- FigureFamily Filter -->
            <MultiCombobox
              ref="figureFamilyFilterRef"
              :options="figure_families"
              class="w-48"
              labelText="Figure family"
              labelClasses="ml-1 !text-sm !font-normal !text-gray-500"
              inputClasses="text-gray-700 bg-gray-50"
              :modelValue="selectedFigureFamilies"
              @update:modelValue="newValues => selectedFigureFamilies = newValues"
            />

            <!-- From Position filter -->
            <MultiCombobox
              ref="fromPositionFilterRef"
              :options="positions"
              class="w-48"
              labelText="From position"
              labelClasses="ml-1 !text-sm !font-normal !text-gray-500"
              inputClasses="py-2.0 text-gray-700 bg-gray-50"
              :modelValue="selectedFromPositions"
              @update:modelValue="newValues => selectedFromPositions = newValues"
            />

            <!-- To Position filter -->
            <MultiCombobox
              ref="toPositionFilterRef"
              :options="positions"
              class="w-48"
              labelText="To position"
              labelClasses="ml-1 !text-sm !font-normal !text-gray-500"
              inputClasses="py-2.0 text-gray-700 bg-gray-50"
              :modelValue="selectedToPositions"
              @update:modelValue="newValues => selectedToPositions = newValues"
            />

          </DisclosurePanel>
        </div>

      </Disclosure>


    </div>

    <!-- Listing of Figures -->
    <!-- Layouts: -->
    <!-- pre-sm: (name, family) (7, 5) -->
    <!-- sm, !$user: (name, family) (8, 4) -->
    <!-- sm, $user: (name, family, edit/delete) (8, 3, 1) -->
    <div class="mt-6  min-w-[400px]">
      <!-- Table header -->
      <div class="grid grid-cols-12 text-xs uppercase text-gray-700 font-semibold">
        <p class="col-span-7 sm:col-span-8 px-3 sm:px-5 py-3 bg-blue-50">Name</p>
        <p 
          class="px-3 sm:px-5 py-3 bg-blue-100"
          :class="$page.props.auth.user ? 'col-span-5 sm:col-span-3' : 'col-span-5 sm:col-span-4'"
        >
          Family
        </p>
        <p v-if="$page.props.auth.user" class="hidden sm:block sm:col-span-1 py-3 bg-blue-50" />
      </div>

      <div
        v-for="(figure, idx) in filteredFigures"
        :key="figure.obj.id"
        v-show="shouldDisplay(figure.obj)"
        class="grid grid-cols-12 text-gray-800 text-sm sm:text-base border-b py-1.5"
      >
        <!-- Name -->
        <div class="col-span-7 sm:col-span-8 px-3 sm:px-4">
          <MyLink class="inline-block" :href="route(compound ? 'compound-figures.show' : 'figures.show', figure.obj.id)">
            {{figure.obj.name}}
          </MyLink>
          <p class="text-sm text-gray-500">
            <MyLink class="font-medium" :href="route('positions.show', figure.obj.from_position_id)" >{{figure.obj.from_position.name}}</MyLink>
            to
            <MyLink class="font-medium" :href="route('positions.show', figure.obj.to_position_id)" >{{figure.obj.to_position.name}}</MyLink>
          </p>
        </div>
        <!-- Figure family -->
        <div
          class="px-4 text-gray-600  flex items-center"
          :class="$page.props.auth.user ? 'col-span-5 sm:col-span-3' : 'col-span-5 sm:col-span-4'"
        >
          <MyLink
            v-if="figure.obj.figure_family"
            :href="route('figure-families.show', figure.obj.figure_family.id)"
          >
            {{figure.obj.figure_family.name}}
          </MyLink>
        </div>
        <!-- Delete button -->
        <div v-if="$page.props.auth.user" class="hidden sm:block sm:col-span-1 px-1">
          <PlainButton
            class="text-gray-500 hover:text-red-600"
            @click="idToDelete = figure.obj.id; deleteDialog.open()"
          >
            <TrashIcon class="h-5 w-5"/>
          </PlainButton>
        </div>
      </div>
    </div>

    <p v-show="numDisplayedFigures === 0" class="px-5 py-4" >
        <span v-if="positions.length === 0">You haven't created any figures yet! You can create one <MyLink :colored="true" :href="route('figures.create')" >here</MyLink>.
        </span>
        <span v-else>No results found. Try a less restrictive filter or search?</span>
    </p>

    <DeleteDialog
      ref="deleteDialog"
      description="figure"
      @delete="deleteFigure"
      @cancel="idToDelete = null; deleteCompound = null"
    />

  </div>
</template>
