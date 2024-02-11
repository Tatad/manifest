<script  lang="ts" setup>
  import { ref, computed } from "vue";
  import GuestLayout from '@/Layouts/GuestLayout.vue';
  import Menu from '@/Pages/Menu.vue';
  import { Head } from '@inertiajs/vue3';
  import PrimaryButton from '@/Components/PrimaryButton.vue';
  import { useForm } from '@inertiajs/vue3';
  import { createToaster } from "@meforma/vue-toaster";

  const form = useForm({
    image: {}
  });
  const image = ref('');
  const MAX_WIDTH = 320;
  const MAX_HEIGHT = 180;
  let onChange = async (event) => {
    // const imageFile = event.target.files[0];
    image.value = event.target.files[0];
    
    console.log('originalFile instanceof Blob', image.value instanceof Blob); // true
    console.log(`originalFile size ${image.value.size / 1024 / 1024} MB`);
    const file = event.target.files[0]; // get the file
    const blobURL = URL.createObjectURL(file);
    console.log(blobURL)
    const img = new Image();
    img.src = blobURL;
    console.log(img.src)

    img.onload = function () {
      const [newWidth, newHeight] = calculateSize(img, MAX_WIDTH, MAX_HEIGHT);
      const canvas = document.createElement("canvas");
      canvas.width = newWidth;
      canvas.height = newHeight;
      const ctx = canvas.getContext("2d");
      ctx.drawImage(img, 0, 0, newWidth, newHeight);
      canvas.toBlob(
        (blob) => {
          // Handle the compressed image.
          const displayTag = document.createElement('h1');
          displayTag.innerText = `Original Image - ${readableBytes(file.size)} :::::: Compressed Image - ${readableBytes(blob.size)}`;
          console.log(displayTag)
          form.image = blob
          //console.log(blob)
           //document.getElementById('container').append(displayTag);
        },
      );
    };
  }

  function calculateSize(img, maxWidth, maxHeight) {
  let width = img.width;
  let height = img.height;

  // calculate the width and height, constraining the proportions
  if (width > height) {
    if (width > maxWidth) {
      height = Math.round((height * maxWidth) / width);
      width = maxWidth;
    }
  } else {
    if (height > maxHeight) {
      width = Math.round((width * maxHeight) / height);
      height = maxHeight;
    }
  }
  return [width, height];
}

function readableBytes(bytes) {
  const i = Math.floor(Math.log(bytes) / Math.log(1024)),
    sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

  return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + sizes[i];
}

  const fileUpload = ref(null);

  const buttonDisabled = ref(false);

  const submit = () => {
    if (form.image) {
      buttonDisabled.value = true;
      form.post(route('scan'), {
          preserveScroll: true,
          preserveState: true,
          onSuccess: (response) => {
              form.reset()
              form.get(route('scanner'), {
                  preserveScroll: true,
                  preserveState: true,
                  onSuccess: () => {
                      buttonDisabled.value = false;
                      form.image = ''
                      toaster.info(response.props.message, {
                        position: "top-right",
                      });
                  }
              });
          }
      });
    }
  }

  const toaster = createToaster({ /* options */ });

</script>

<template>
  <Head title="Scanned List" />
  <GuestLayout>
    <template #header>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">Scanned List</h2>
    </template>

    <div class="py-3">
      <span class="tag">
        <!-- <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="file_input"></label> -->
        <div>
          <form enctype="multipart/form-data">
          <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-white focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" type="file" ref="fileUpload" @change="onChange($event)"  accept="image/*">
          <PrimaryButton v-if="buttonDisabled == false" class="mt-10" @click.prevent="submit">Submit</PrimaryButton>
          <button v-if="buttonDisabled == true" type="button" class="mt-10 bg-indigo-400 h-max w-max rounded-lg text-white font-bold hover:bg-indigo-300 hover:cursor-not-allowed duration-[500ms,800ms]" disabled>
              <div class="flex items-center justify-center m-[10px]"> 
                  <div class="h-5 w-5 border-t-transparent border-solid animate-spin rounded-full border-white border-4"></div>
                  <div class="ml-2"> Processing... </div>
              </div>
          </button>
        </form>
  <!-- <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-white focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" id="file_input" type="file"  accept="image/*" capture="camera" @change="onChange"> -->
  
          </div>

        
      </span>
    </div>
  </GuestLayout>

    <Menu></Menu>
</template>

<style scoped>
a {
  color: #42b983;
}
.information {
  margin-top: 100px;
}
</style>
