<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import PlainButton from '@/Components/PlainButton.vue'
import SecondaryLink from '@/Components/SecondaryLink.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import CustomValueCombobox from '@/Components/CustomValueCombobox.vue'
import FuzzyCombobox from '@/Components/FuzzyCombobox.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import { PlusCircleIcon, TrashIcon } from '@heroicons/vue/24/outline'
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  figure: Object,
  figure_families: Array,
  positions: Array,
  action: String,
});

const form = useForm({
  id: props.figure.id,
  name: props.figure.name ?? "",
  description: props.figure.description ?? "",
  from_position_id: props.figure.from_position_id,
  from_position: props.figure.from_position,
  to_position_id: props.figure.to_position_id,
  to_position: props.figure.to_position,
  figure_family_id: props.figure.figure_family_id,
  figure_family: props.figure.figure_family,
  figure_videos: [],
});

const figureVideos = ref(props.figure.figure_videos.map((fv, idx) => ({
  id: idx,
  figure_video: {
    url: fv.url,
    description: fv.description,
  }
})));
var nextFigureVideoId = figureVideos.value.length;

// Used to focus inputs after adding new figure video
const figureVideoEntryDivRefs = ref([])

function addFigureVideo() {
  figureVideos.value.push({
    id: nextFigureVideoId,
    figure_video: {
      url: "",
      description: "",
    }
  });

  // Focus the first text input in the just-added (i.e. last) entryDiv
  // Using setTimeout lets div be inserted into DOM
  setTimeout(() => {
    const input = figureVideoEntryDivRefs.value[figureVideoEntryDivRefs.value.length - 1].querySelectorAll('input')[0];
    if (input) input.focus();
  }, 0)

  nextFigureVideoId += 1;
}

// Remove figure video with given index from list
function removeFigureVideo(idx) {
  if (idx >= 0 && idx < figureVideos.value.length) figureVideos.value.splice(idx, 1);
}

const submit = () => {
  form.figure_videos = figureVideos.value.map(fv => fv.figure_video);
  if (props.action === "create") {
    form.post(route('figures.store'));
  } else if (props.action === "edit") {
    form.put(route('figures.update', props.figure.id));
  }
};

</script>

<template>
  <form @submit.prevent="submit" class="pb-8">

    <!-- Name input -->
    <div class="mt-4 w-full max-w-xl">
      <InputLabel for="name" value="Name" />
      <TextInput
      id="name"
      type="text"
      class="block w-80"
      v-model="form.name"
      :autofocus="action === 'create'"
      required
    />
      <InputError class="mt-2" :message="form.errors.name" />
    </div>

    <!-- From/to position -->
    <div class="mt-3">
      <FuzzyCombobox
      class="w-80"
      labelText="From position"
      searchKey="name"
      :options="positions"
      :modelValue="form.from_position"
      @update:modelValue="(newValue) => {
      form.from_position = newValue;
      form.from_position_id = newValue.id;
      }"
    />
      <InputError class="mt-2" :message="form.errors.from_position_id" />
    </div>

    <div class="mt-3 flex items-start border-b pb-6 border-gray-200">
      <div>
        <FuzzyCombobox
        class="w-80"
        labelText="To position"
        searchKey="name"
        :options="positions"
        :modelValue="form.to_position"
        @update:modelValue="(newValue) => {
        form.to_position = newValue;
        form.to_position_id = newValue.id;
        }"
      />
        <InputError class="mt-2" :message="form.errors.to_position_id" />
      </div>
    </div>

    <!-- Figure family and weight -->
    <div class="mt-5 flex items-start">

      <!-- Figure family input -->
      <div>
        <CustomValueCombobox
        class="w-72"
        :options="figure_families"
        :nullable="true"
        labelText="Figure family (optional)"
        :modelValue="form.figure_family"
        @update:modelValue="(newFigureFamily) => {
        form.figure_family = newFigureFamily
        form.figure_family_id = newFigureFamily ? newFigureFamily.id : null
        }"
      />
        <InputError class="mt-2" :message="form.errors.figure_family_id" />
        <InputError class="mt-2" :message="form.errors.figure_family" />
        <InputError class="mt-2" :message="form.errors['figure_family.name']" />
      </div>

    </div>

    <!-- Description input -->
    <div class="mt-5 w-full">
      <InputLabel for="description" value="Description (optional)" />
      <TextArea
      id="description"
      class="block w-full h-64 text-sm max-w-xl"
      v-model="form.description"
    />
      <InputError class="mt-2" :message="form.errors.description" />
    </div>

    <!-- FigureVideos -->
    <div class="mt-5 pb-8 border-b border-gray-200">
      <h2 class="text-md text-gray-700">Videos (optional)</h2>

      <ol
        ref="list"
        class="mt-2 space-y-3"
      >
        <li v-for="(fv, idx) in figureVideos" :key="fv.id" >
          <!-- Wrapper div to allow focusing URL input programatically -->
          <div ref="figureVideoEntryDivRefs" class="bg-gray-50 p-3 rounded-lg border border-gray-100">

            <!-- Div to align URL input and delete/rearrange icons -->
            <div class="flex items-center">
              <!-- URL input -->
              <div class="w-full">
                <InputLabel :for="'figure-video-url-' + fv.id" value="URL" />
                <TextInput
                :id="'figure-video-url-' + fv.id"
                type="text"
                class="w-full max-w-xl"
                v-model="fv.figure_video.url"
                required
              />
                <InputError class="mt-2" :message="form.errors['figure_videos.' + idx.toString() + '.url']" />
              </div>

              <!-- Hardcoded top margin aligns with text input -->
              <PlainButton
                type="button"
                @click="removeFigureVideo(idx)"
                class="ml-auto p-1 mt-[1.25rem] text-gray-700 inline-flex"
              >
                <TrashIcon class="-ml-1 w-6 h-6 text-gray-600 shrink-0" />
                <span class="ml-1">Delete</span>
              </PlainButton>

            </div>

            <!-- Description input -->
            <div class="mt-3 w-full">
              <InputLabel :for="'figure-video-description-' + fv.id" value="Description (optional)" />
              <TextArea
              id="'figure-video-description-' + fv.id"
              class="block w-full h-20 text-sm max-w-xl"
              v-model="fv.figure_video.description"
            />
              <InputError class="mt-2" :message="form.errors['figure_videos.' + idx.toString() + '.description']" />
            </div>

          </div>

        </li>
      </ol>
      <p v-if="figureVideos.length === 0" class="text-gray-500 text-sm" >
        No videos added for this figure.
      </p>

      <InputError class="mt-2 max-w-lg" :message="form.errors.figure_videos" />

      <!-- Add FigureVideo -->
      <button
        type="button"
        @click="addFigureVideo"
        class="mt-4 inline-flex compound_figure_figures-center w-fit pl-2 pr-4 py-1 bg-white border border-gray-300 rounded-lg text-gray-500 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
      >
        <PlusCircleIcon class="w-6 h-6"/>
        <span class="ml-2 text-sm">Add video</span>
      </button>

    </div>

    <!-- Submit and Cancel buttons -->
    <div class="mt-8">
      <PrimaryButton
        class=""
        :class="{ 'opacity-25': form.processing }"
        :disabled="form.processing"
      >
        <span v-if="action === 'edit'">Save</span>
        <span v-else>Create</span>
      </PrimaryButton>

      <SecondaryLink :href="route('figures.index')" class="ml-2" >
        Cancel
      </SecondaryLink>
    </div>

  </form>
</template>
