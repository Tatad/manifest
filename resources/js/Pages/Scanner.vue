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
  const MAX_WIDTH = 620;
  const MAX_HEIGHT = 880;
  const imageName = ref('No File Chosen')
  let onChange = async (event) => {
    // const imageFile = event.target.files[0];
    image.value = event.target.files[0];
    
    console.log('originalFile instanceof Blob', image.value instanceof Blob); // true
    console.log(`originalFile size ${image.value.size / 1024 / 1024} MB`);
    const file = event.target.files[0]; // get the file
    const blobURL = URL.createObjectURL(file);
    imageName.value = image.value.name
    console.log(blobURL)
    const img = new Image();
    img.src = blobURL;
    console.log(img.src)
    imageThumb.value = blobURL;
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
          form.image = blob
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
  const imageThumb = ref(null);
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

  let onImageChangeCapture = (event) => {
    imageThumb.value = []
    let file = event.target.files ? event.target.files[0] : null;
    imageThumb.value.push(URL.createObjectURL(file))
    const blobURL = URL.createObjectURL(file);
    const img = new Image();
    img.src = blobURL;
    img.onload = function () {
        const [newWidth, newHeight] = calculateSize(img, MAX_WIDTH, MAX_HEIGHT);
        const canvas = document.createElement("canvas");
        canvas.width = newWidth;
        canvas.height = newHeight;
        const ctx = canvas.getContext("2d");
        ctx.drawImage(img, 0, 0, newWidth, newHeight);
        canvas.toBlob(
          (blob) => {
            form.image.push(blob)
          },
        );
      };
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
            <label for="myfileid" class="wrapper bg-white dark:bg-indigo-100 rounded-lg py-8 icon dark:text-white flex justify-center ">
              <div>
                <div class="w-full bg-stone-100 text-center py-4 rounded-lg mb-8 w-full text-gray-900">
                  <h1 class="text-base px-4 uppercase">{{$filters.truncate(imageName)}}</h1>
                </div>

                <div class="flex justify-center pt-4 pb-4" v-if="imageThumb">
                  <img :src="imageThumb" width="100">
                </div>

                <div class="grid grid cols-3 grid-cols-subgrid gap-4">
                  <span>
                    <h1 class=" bg-red-400 w-full text-xl py-4 px-8 rounded-lg text-center uppercase">Choose File</h1>
                  </span>

                  <label for="addImage" class="icon pt-4 col-start-3 bg-indigo-500 px-4 rounded-lg text-white">
                    <span>
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                      </svg>
                    </span>
                  </label>
                  <input id="addImage" style="display:none;" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" type="file"  accept="image/*" capture="camera" @change="onImageChangeCapture">
                </div>
              </div>
            </label>
            <div>
              <input id="myfileid" style="display:none;" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-white focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" type="file" ref="fileUpload" @change="onChange($event)" accept="image/*">
            </div>

            <PrimaryButton class="bg-gray-500 dark:bg-gray-500 mt-10 ml-24 text-xl w-1/2" v-if="!imageThumb" disabled>Submit</PrimaryButton>
            <PrimaryButton v-if="buttonDisabled == false && imageThumb" class="bg-green-500 dark:bg-green-500 mt-10 ml-24 text-xl w-1/2" @click.prevent="submit"><h1>Submit</h1></PrimaryButton>
            <button v-if="buttonDisabled == true" type="button" class="mt-10 bg-indigo-400 mt-10 ml-24 text-xl w-1/2 text-white rounded-lg" disabled>
                <div class="flex items-center justify-center m-[10px]"> 
                    <div class="h-5 w-5 border-t-transparent border-solid animate-spin rounded-full border-white border-4"></div>
                    <div class="ml-2"> Processing... </div>
                </div>
            </button>
          </form>
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
