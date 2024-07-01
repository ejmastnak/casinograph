<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import PlainButton from '@/Components/PlainButton.vue'
import SecondaryLink from '@/Components/SecondaryLink.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import CustomValueCombobox from '@/Components/CustomValueCombobox.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import { PlusCircleIcon, TrashIcon } from '@heroicons/vue/24/outline'
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  position: Object,
  position_families: Array,
  action: String,
});

const form = useForm({
  id: props.position.id,
  name: props.position.name ?? "",
  description: props.position.description ?? "",
  position_family_id: props.position.position_family_id,
  position_family: props.position.position_family,
  position_images: [],
});

const positionImages = ref(props.position.position_images.map((pi, idx) => ({
  id: idx,
  position_image: {
    id: pi.id,
    path: pi.path,
    image: null,
    description: pi.description ?? "",
  }
})));
var nextPositionImageId = positionImages.value.length;

// Used to focus inputs after adding new position images
const positionImageEntryDivRefs = ref([])

function addPositionImage() {
  positionImages.value.push({
    id: nextPositionImageId,
    position_image: {
      id: null,
      path: null,
      image: null,
      description: "",
    }
  });

  nextPositionImageId += 1;
}

// Remove position image with given index from list
function removePositionImage(idx) {
  if (idx >= 0 && idx < positionImages.value.length) positionImages.value.splice(idx, 1);
}

const submit = () => {
  form.position_images = positionImages.value.map(pi => pi.position_image);
  if (props.action === "create") {
    form.post(route('positions.store'));
  } else if (props.action === "edit") {
    form.transform((data) => ({
      ...data,
      _method: 'PUT'
    })).post(route('positions.update', props.position.id), {
        forceFormData: true,
      });
  }
};

</script>

<template>
  <form @submit.prevent="submit" class="">

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

    <!-- Position family input -->
    <div class="">
      <CustomValueCombobox
      class="mt-4 w-72"
      :options="position_families"
      :nullable="true"
      labelText="Position family (optional)"
      :modelValue="form.position_family"
      @update:modelValue="(newPositionFamily) => {
      form.position_family = newPositionFamily
      form.position_family_id = newPositionFamily ? newPositionFamily.id : null
      }"
    />
      <InputError class="mt-2" :message="form.errors.position_family_id" />
      <InputError class="mt-2" :message="form.errors.position_family" />
      <InputError class="mt-2" :message="form.errors['position_family.name']" />
    </div>

    <!-- Description input -->
    <div class="mt-4 w-full">
      <InputLabel for="description" value="Description (optional)" />
      <TextArea
      id="description"
      class="block w-full h-64 text-sm max-w-xl"
      v-model="form.description"
    />
      <InputError class="mt-2" :message="form.errors.description" />
    </div>

    <!-- PositionImages -->
    <div class="mt-5 pb-8 border-b border-gray-200">
      <h2 class="text-md text-gray-700">Images (optional)</h2>

      <ol
        ref="list"
        class="mt-2 space-y-3"
      >
        <li v-for="(positionImage, idx) in positionImages" :key="positionImage.id" >
          <!-- Wrapper div to allow focusing URL input programatically -->
          <div ref="positionImageEntryDivRefs" class="bg-gray-50 p-3 rounded-lg border border-gray-100">

            <!-- Div to align URL input and delete/rearrange icons -->
            <div class="flex items-center">
              <!-- Image file input -->
              <div class="w-full">
                <InputLabel :for="'position-image-image-' + positionImage.id" value="Image" />

                <img
                  v-if="positionImage.position_image.path"
                  :src="'/' + positionImage.position_image.path"
                  :alt="'Image of ' + position.name"
                  class="max-w-md mb-2"
                >

                <input
                  type="file"
                  :id="'position-image-image-' + positionImage.id"
                  @input="positionImage.position_image.image = $event.target.files[0]" 
                  :required="positionImage.position_image.id === null"
                />
                <InputError class="mt-2" :message="form.errors['position_images.' + idx.toString() + '.image']" />
                <InputError class="mt-2" :message="form.errors['position_images.' + idx.toString() + '.id']" />
              </div>

              <!-- Hardcoded top margin aligns with text input -->
              <PlainButton
                type="button"
                @click="removePositionImage(idx)"
                class="ml-auto p-1 mt-[1.25rem] text-gray-700 inline-flex"
              >
                <TrashIcon class="-ml-1 w-6 h-6 text-gray-600 shrink-0" />
                <span class="ml-1">Delete</span>
              </PlainButton>

            </div>

            <!-- Image description input -->
            <div class="mt-3 w-full">
              <InputLabel :for="'position-image-description-' + positionImage.id" value="Description (optional)" />
              <TextArea
              id="'position-image-description-' + positionImage.id"
              class="block w-full h-20 text-sm max-w-xl"
              v-model="positionImage.position_image.description"
            />
              <InputError class="mt-2" :message="form.errors['position_images.' + idx.toString() + '.description']" />
            </div>

            <InputError class="mt-2" :message="form.errors['position_images.' + idx.toString()]" />

          </div>

        </li>
      </ol>
      <p v-if="positionImages.length === 0" class="text-gray-500 text-sm" >
        No images added for this position.
      </p>

      <InputError class="mt-2 max-w-lg" :message="form.errors.position_images" />

      <!-- Add PositionImage -->
      <button
        type="button"
        @click="addPositionImage"
        class="mt-4 inline-flex compound_figure_figures-center w-fit pl-2 pr-4 py-1 bg-white border border-gray-300 rounded-lg text-gray-500 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
      >
        <PlusCircleIcon class="w-6 h-6"/>
        <span class="ml-2 text-sm">Add image</span>
      </button>

    </div>

    <!-- Submit and Cancel buttons -->
    <div class="mt-6">
      <PrimaryButton
        class=""
        :class="{ 'opacity-25': form.processing }"
        :disabled="form.processing"
      >
        <span v-if="action === 'edit'">Update</span>
        <span v-else>Create</span>
      </PrimaryButton>

      <SecondaryLink :href="route('positions.index')" class="ml-2" >
        Cancel
      </SecondaryLink>
    </div>

  </form>
</template>
