<script setup>
import { ref, watch, computed, onBeforeUnmount, onMounted} from 'vue'
import { router } from '@inertiajs/vue3'
import fuzzysort from 'fuzzysort'
import throttle from "lodash/throttle";
import { Head } from '@inertiajs/vue3'
import DeleteDialog from "@/Components/DeleteDialog.vue";
import MyLink from '@/Components/MyLink.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import PlainButton from '@/Components/PlainButton.vue'
import TextInput from '@/Components/TextInput.vue'
import MultiSelect from '@/Components/MultiSelect.vue'
import { PencilSquareIcon, TrashIcon, PlusCircleIcon, MagnifyingGlassIcon, XMarkIcon, AdjustmentsHorizontalIcon, FunnelIcon } from '@heroicons/vue/24/outline'
import NewFigureDialog from './Partials/NewFigureDialog.vue'

const props = defineProps({
  figures: Array,
  figure_families: Array,
})

// For filtering Figures by FigureFamily
const selectedFigureFamilies = ref([])
const selectedFigureFamilyIDs = computed(() => {
  return selectedFigureFamilies.value.map(figureFamily => figureFamily.id)
})

// For filtering Figures by simple/compound flag
const showCompoundFigures = ref(true)
const showSimpleFigures = ref(true)

function shouldDisplay(figure) {
  return ((selectedFigureFamilies.value.length === 0) || selectedFigureFamilyIDs.value.includes(figure.figure_family_id))
    && ((showCompoundFigures.value && figure.compound) || (showSimpleFigures.value && !figure.compound))
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
    compound: figure.compound,
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
  figureSearchQuery.value = ""
  selectedFigureFamilies.value = []
  showSimpleFigures.value = true
  showCompoundFigures.value = true
  search(figureSearchQuery.value)
  figureSearchInput.value.focus()
}

// Used to conditionally display a "No results found" message.
const numDisplayedFigures = computed(() => {
  return filteredFigures.value.filter(figure => shouldDisplay(figure.obj)).length
})

let idToDelete = ref(null)
let deleteCompound = ref(true)
const deleteDialog = ref(null)
function deleteFigure() {
  if (idToDelete.value !== null && deleteCompound.value !== null) {
    router.delete(route((deleteCompound.value ? 'compound_figures.destroy' : 'figures.destroy'), idToDelete.value), {
      onSuccess: () => {
        // Set selectedFigureFamilies to only those previously selected
        // figure families still present in props.figure_families after
        // deleting the last figure. This handles the edge case where a user
        // filters by figure family and deletes last figure in a family
        // (causing figure family itself to be deleted in backend). Stored
        // selectedFigureFamilies then includes a figure family that no
        // longer exists in props.figure_families.
        const figureFamilyIds = props.figure_families.map(p => p.id);
        selectedFigureFamilies.value = selectedFigureFamilies.value.filter(f => figureFamilyIds.includes(f.id));
        search(figureSearchQuery.value)  // remove deleted item from display
      }
    });
  }
  idToDelete.value = null
  deleteCompound.value = null
}

const newFigureDialog = ref(null)
function newSimpleFigure() {
  router.get(route('figures.create'));
}
function newCompoundFigure() {
  router.get(route('compound_figures.create'));
}

// Preserve scroll position and search queries when leaving page.
onBeforeUnmount(() => {
  sessionStorage.setItem('figuresIndexScrollX', window.scrollX)
  sessionStorage.setItem('figuresIndexScrollY', window.scrollY)
  sessionStorage.setItem('figuresIndexSearchQuery', figureSearchQuery.value)
  sessionStorage.setItem('figuresIndexShowSimpleFigures', showSimpleFigures.value)
  sessionStorage.setItem('figuresIndexShowCompoundFigures', showCompoundFigures.value)
  sessionStorage.setItem('figuresIndexSelectedFamilies', JSON.stringify(selectedFigureFamilies.value))
})

// Preserve scroll position and search queries on manual page reload.
window.onbeforeunload = function() {
  sessionStorage.setItem('figuresIndexScrollX', window.scrollX)
  sessionStorage.setItem('figuresIndexScrollY', window.scrollY)
  sessionStorage.setItem('figuresIndexSearchQuery', figureSearchQuery.value)
  sessionStorage.setItem('figuresIndexShowSimpleFigures', showSimpleFigures.value)
  sessionStorage.setItem('figuresIndexShowCompoundFigures', showCompoundFigures.value)
  sessionStorage.setItem('figuresIndexSelectedFamilies', JSON.stringify(selectedFigureFamilies.value))
}

// Restore scroll position and search queries when loading page
onMounted(() => {
  const scrollX = sessionStorage.getItem('figuresIndexScrollX')
  const scrollY = sessionStorage.getItem('figuresIndexScrollY')
  if (scrollX && scrollY) {
    setTimeout(() => {
      window.scrollTo(scrollX, scrollY)
    })
  }

  const storedSearchQuery = sessionStorage.getItem('figuresIndexSearchQuery');
  if (storedSearchQuery) {
    figureSearchQuery.value = storedSearchQuery
    search(figureSearchQuery.value)
  }

  const storedShowSimpleFigures = sessionStorage.getItem('figuresIndexShowSimpleFigures');
  if (storedShowSimpleFigures) {
    showSimpleFigures.value = (storedShowSimpleFigures === 'true')
  }

  const storedShowCompoundFigures = sessionStorage.getItem('figuresIndexShowCompoundFigures');
  if (storedShowCompoundFigures) {
    showCompoundFigures.value = (storedShowCompoundFigures === 'true')
  }

  const storedSelectedFamilies = sessionStorage.getItem('figuresIndexSelectedFamilies');
  if (storedSelectedFamilies) {
    selectedFigureFamilies.value = JSON.parse(storedSelectedFamilies)
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
    <Head title="Figures" />

    <div class="flex">
      <div class="mr-2">
        <h1 class="text-xl">Figures</h1>
        <p class="mt-1 text-sm text-gray-500 max-w-xs">This is a list of all figures. You can use this page to view, edit, delete, or add new figures.</p>
      </div>

      <SecondaryButton class="ml-auto h-fit" @click="newFigureDialog.open()" >
        <PlusCircleIcon class="-ml-1 text-gray-600 h-6 w-6 shrink-0" />
        <p class="ml-1 whitespace-nowrap">New <span class="hidden sm:inline">figure</span></p>
      </SecondaryButton>
    </div>

    <!-- Main panel for table and search -->
    <div class="mt-6">

      <!-- Search and filter components -->
      <div class="flex flex-wrap items-end space-y-2 gap-x-4">

        <!-- Fuzzy search by name -->
        <div>
          <label for="figure-search-query" class="ml-1 text-sm text-gray-500">
            Search by name
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <MagnifyingGlassIcon class="w-5 h-5 text-gray-500" />
            </div>
            <TextInput
              class="py-2 pl-10 text-gray-700 w-80 bg-gray-50"
              type="text"
              id="figure-search-query"
              v-model="figureSearchQuery"
              ref="figureSearchInput"
            />
          </div>
        </div>

        <!-- FigureFamily Filter -->
        <MultiSelect
          :options="figure_families"
          width="w-44"
          labelText="Filter by family"
          inputClasses="!py-2.5"
          :modelValue="selectedFigureFamilies"
          @update:modelValue="newValue => selectedFigureFamilies = newValue"
        />

        <!-- Simple and compound figure checkboxes -->
        <div class="text-gray-700 text-sm">

          <p class="text-gray-500">Figures to show</p>

          <div class="mt-1">
            <!-- Show/hide simple figures -->
            <div class="flex items-center">
              <input
                type="checkbox"
                id="simple"
                name="simple"
                v-model="showSimpleFigures"
                class="rounded"
              />
              <label class="ml-1.5" for="simple">Simple</label>
            </div>

            <!-- Show/hide compound figures -->
            <div class="flex items-center">
              <input
                type="checkbox"
                id="compound"
                name="compound"
                v-model="showCompoundFigures"
                class="rounded"
              />
              <label class="ml-1.5" for="compound">Compound</label>
            </div>
          </div>

        </div>

        <!-- Clear filters buttom -->
        <div class="flex items-center h-fit">
          <label for="clear-filters" class="sr-only">
            Clear filters
          </label>
          <PlainButton
            id="clear-filters"
            class="!bg-gray-50"
            @click="clearFilters"
          >
            <XMarkIcon class="-ml-2 w-6 h-6 text-gray-500 shrink-0" />
            <div class="ml-0.5 text-gray-600 whitespace-nowrap">
              Clear filters
            </div>
          </PlainButton>
        </div>

      </div>

      <!-- Listing of Figures -->
      <!-- Layouts: -->
      <!-- pre-sm: (name, simple, family) (7, 2, 3) -->
      <!-- sm, !$user: (name, simple, family) (7, 2, 3) -->
      <!-- sm, $user: (name, simple, family, edit/delete) (7, 2, 2, 1) -->
      <div class="mt-6  min-w-[400px]">
        <!-- Table header -->
        <div class="grid grid-cols-12 text-xs uppercase text-gray-700 font-semibold">
          <p class="col-span-7 px-3 sm:px-5 py-3 bg-blue-50">Name</p>
          <p class="col-span-2 px-3 sm:px-5 py-3 bg-blue-100">Simple?</p>
          <p 
            class="px-3 sm:px-5 py-3 bg-blue-50"
            :class="$page.props.auth.user ? 'col-span-3 sm:col-span-2' : 'col-span-3 sm:col-span-3'"
          >
            Family
          </p>
          <p v-if="$page.props.auth.user" class="hidden sm:block col-span-1 py-3 bg-blue-100" />
        </div>

        <div
          v-for="(figure, idx) in filteredFigures"
          :key="figure.obj.id"
          v-show="shouldDisplay(figure.obj)"
          class="grid grid-cols-12 text-gray-800 text-sm sm:text-base border-b py-1.5"
        >
          <!-- Name -->
          <div class="col-span-7 px-3 sm:px-4">
            <MyLink class="inline-block" :href="route(figure.obj.compound ? 'compound_figures.show' : 'figures.show', figure.obj.id)">
              {{figure.obj.name}}
            </MyLink>
            <p class="text-sm text-gray-500">
              <MyLink class="font-medium" :href="route('positions.show', figure.obj.from_position_id)" >{{figure.obj.from_position.name}}</MyLink>
              to
              <MyLink class="font-medium" :href="route('positions.show', figure.obj.to_position_id)" >{{figure.obj.to_position.name}}</MyLink>
            </p>
          </div>
          <!-- Simple/Compound -->
          <div class="col-span-2 px-3 sm:px-4 text-gray-600 flex items-center">
            <p class="sm:hidden">{{figure.obj.compound ? "Comp." : "Simple"}}</p>
            <p class="hidden sm:block">{{figure.obj.compound ? "Compound" : "Simple"}}</p>
          </div>
          <!-- Figure family -->
          <div
            class="px-4 text-gray-600  flex items-center"
            :class="$page.props.auth.user ? 'col-span-3 sm:col-span-2' : 'col-span-3 sm:col-span-3'"
          >
            <MyLink
              v-if="figure.obj.figure_family"
              :href="route('figure_families.show', figure.obj.figure_family.id)"
            >
              {{figure.obj.figure_family.name}}
            </MyLink>
          </div>
          <!-- Edit/Delete -->
          <div v-if="$page.props.auth.user" class="hidden sm:flex sm:col-span-1 px-1 items-center">
            <MyLink :href="route(figure.obj.compound ? 'compound_figures.edit' : 'figures.edit', figure.obj.id)" class="text-gray-500 hover:text-blue-600">
              <PencilSquareIcon class="h-5 w-5"/>
            </MyLink>
            <button
              type="button"
              class="ml-0.5 text-gray-500 hover:text-red-600"
              @click="idToDelete = figure.obj.id; deleteCompound = figure.obj.compound; deleteDialog.open()"
            >
              <TrashIcon class="h-5 w-5"/>
            </button>
          </div>
        </div>
      </div>

      <p v-show="numDisplayedFigures === 0" class="px-5 py-4" >
        No results found. Try a less restrictive filter or search?
      </p>

    </div>

    <DeleteDialog
      ref="deleteDialog"
      description="figure"
      @delete="deleteFigure"
      @cancel="idToDelete = null; deleteCompound = null"
    />

    <NewFigureDialog
      ref="newFigureDialog"
      @simple="newSimpleFigure"
      @compound="newCompoundFigure"
    />

  </div>
</template>
