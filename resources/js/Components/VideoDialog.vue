<script setup>
import {ref} from 'vue'
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import {
  TransitionRoot,
  Dialog,
  DialogPanel,
  DialogTitle,
  DialogDescription,
} from '@headlessui/vue'

const props = defineProps({
  videos: Array,
})
defineExpose({ open })

const isOpen = ref(false)
function open() { isOpen.value = true }
function close() { isOpen.value = false }

const link = "https://youtu.be/KIJGqON_oi0?t=20"

// Extracts a YouTube video ID and seek position in seconds from a YouTube URL
// and constructs a corresponding embed URL. Credit: ChatGPT.
// Example inputs include
// - https://www.youtube.com/watch?v=KIJGqON_oi0&t=20s
// - https://www.youtube.com/watch?v=KIJGqON_oi0
// - https://youtu.be/KIJGqON_oi0?t=20
// - https://youtu.be/KIJGqON_oi0
// Corresponding outputs would be either:
// - https://www.youtube.com/embed/KIJGqON_oi0?start=20&amp;rel=0
// - https://www.youtube.com/embed/KIJGqON_oi0?start=0&amp;rel=0
function constructYouTubeEmbedUrl(url) {
  const videoUrl = new URL(url);
  let videoId = null;
  let seekSeconds = 0;

  // Handling different hostnames and pathnames for ID extraction
  if (videoUrl.hostname === 'youtu.be' || videoUrl.pathname.includes('/embed/')) {
    videoId = videoUrl.pathname.split('/').pop();
  } else if (videoUrl.hostname.includes('youtube.com')) {
    videoId = videoUrl.searchParams.get('v');
  }

  // Handling different parameters for time, with more complex parsing for formats like 1h30m20s
  let timeParam = videoUrl.searchParams.get('t') || videoUrl.searchParams.get('start') || videoUrl.searchParams.get('time_continue');
  if (timeParam) {
    let hours = timeParam.match(/(\d+)h/);
    let minutes = timeParam.match(/(\d+)m/);
    let seconds = timeParam.match(/(\d+)s/);

    hours = hours ? parseInt(hours[1], 10) * 3600 : 0;
    minutes = minutes ? parseInt(minutes[1], 10) * 60 : 0;
    seconds = seconds ? parseInt(seconds[1], 10) : 0;

    seekSeconds = hours + minutes + seconds;
  }

  return "https://www.youtube.com/embed/" + videoId + "?start=" + seekSeconds + "&amp;rel=0";
}

</script>

<template>
  <Dialog :open="isOpen" @close="close" class="relative z-50">
    <div class="fixed inset-0 flex items-center justify-center p-4 bg-blue-50/80">
      <DialogPanel class="p-6 rounded-lg overflow-hidden bg-white shadow">

        <div class="space-y-5">
          <div v-for="video in videos" :key="video.id">
            <iframe
              width="560"
              height="315"
              :src="constructYouTubeEmbedUrl(video.url)"
              frameborder="0"
              allow="encrypted-media; web-share"
              referrerpolicy="strict-origin-when-cross-origin"
              allowfullscreen
            >
            </iframe>
            <p>{{video.description}}</p>
          </div>
        </div>

        <SecondaryButton @click="close" class="mt-8" >
          Close
        </SecondaryButton>

      </DialogPanel>
    </div>
  </Dialog>
</template>
