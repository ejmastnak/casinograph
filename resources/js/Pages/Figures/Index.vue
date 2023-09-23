<script setup>
import { ref, watch, computed } from 'vue'
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
  show_edit_delete_icons: Boolean,
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
        search(figureSearchQuery.value)
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
      <div class="">
        <h1 class="text-xl">Figures</h1>
        <p class="mt-1 text-sm text-gray-500 max-w-xs">This is a list of all figures. You can use this page to view, edit, delete, or add new figures.</p>
      </div>

      <SecondaryButton class="ml-auto h-fit" @click="newFigureDialog.open()" >
        <PlusCircleIcon class="-ml-1 text-gray-600 h-6 w-6 shrink-0" />
        <p class="ml-1 whitespace-nowrap">New figure</p>
      </SecondaryButton>
    </div>

    <!-- Main panel for table and search -->
    <div class="mt-6 border border-gray-100 shadow-md rounded-lg">
      <div class="m-3 flex items-end">
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
          class="ml-3"
          :options="figure_families"
          width="w-48"
          labelText="Filter by family"
          inputClasses="!py-2.5"
          :modelValue="selectedFigureFamilies"
          @update:modelValue="newValue => selectedFigureFamilies = newValue"
        />

        <!-- Simple and compound figure checkboxes -->
        <div class="ml-4 text-gray-700 text-sm">

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
        <div class="ml-auto flex items-center h-fit">
          <label for="clear-filters" class="sr-only">
            Clear filters
          </label>
          <PlainButton
            id="clear-filters"
            class="!bg-gray-50"
            @click="clearFilters"
          >
            <XMarkIcon class="-ml-2 w-6 h-6 text-gray-500 shrink-0" />
            <div class="ml-2 text-gray-600">
              <p>Clear</p>
              <p>filters</p>
            </div>
          </PlainButton>
        </div>


      </div>

      <table class="mt-6 sm:table-fixed w-full text-sm sm:text-base text-left">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
          <tr>
            <th
              scope="col"
              class="px-5 py-2 bg-blue-100"
              :class="show_edit_delete_icons ? 'w-7/12' : 'w-8/12'"
            >
              Name
            </th>
            <th scope="col" class="px-5 py-2 bg-blue-200" >
              Simple?
            </th>
            <th scope="col" class="px-5 py-2 bg-blue-100" >
              Family
            </th>
            <!-- For trash and edit icons -->
            <th v-if="show_edit_delete_icons" scope="col" class="bg-blue-200 w-1/12" />
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="figure in filteredFigures"
            :key="figure.obj.id.toString() + figure.obj.compound.toString()"
            v-show="shouldDisplay(figure.obj)"
            class="bg-white border-b"
          >
            <!-- Name -->
            <td scope="row" class="px-5 py-2">
              <MyLink :href="route(figure.obj.compound ? 'compound_figures.show' : 'figures.show', figure.obj.id)">
                {{figure.obj.name}}
              </MyLink>
            </td>
            <!-- Compound figure? -->
            <td class="px-4 py-2 text-gray-600">
              {{figure.obj.compound ? "Compound" : "Simple"}}
            </td>
            <!-- FigureFamily -->
            <td class="px-5 py-2 text-gray-600 whitespace-nowrap">
              {{figure.obj.figure_family?.name}}
            </td>
            <!-- Delete/Edit -->
            <td v-if="show_edit_delete_icons" class="px-2">
              <div class="flex items-center">
                <MyLink :href="route(figure.obj.compound ? 'compound_figures.edit' : 'figures.edit', figure.obj.id)">
                  <PencilSquareIcon class="text-gray-500 h-5 w-5"/>
                </MyLink>
                <button class="ml-0.5" type="button" @click="idToDelete = figure.obj.id; deleteCompound = figure.obj.compound; deleteDialog.open()">
                  <TrashIcon class="text-gray-500 h-5 w-5"/>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

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
