<script  lang="ts" setup>
  import { ref, computed } from "vue";
  import GuestLayout from '@/Layouts/GuestLayout.vue';
  import { Head } from '@inertiajs/vue3';
  import PrimaryButton from '@/Components/PrimaryButton.vue';
  import SecondaryButton from '@/Components/SecondaryButton.vue';
  import TextInput from '@/Components/TextInput.vue';
  import InputError from '@/Components/InputError.vue';
  import InputLabel from '@/Components/InputLabel.vue';
  import { useForm } from '@inertiajs/vue3';
  import Menu from '@/Pages/Menu.vue';
  import Modal from '@/Components/Modal.vue';

  const props = defineProps({
    item: {
        type: Object,
    }
  });

  const itemInfo = ref(props.item);
  const upcCode = ref('');
  const previewItemModal = ref(false);
  const buttonDisabled = ref(false);

  const form = useForm({
    id: 0,
    item: 0,
    upc_code: '',
    description: '',
    isEmpty: 0,
    image: [],
    type: '',
    msrp: 0,
    retail_price: 0,
    scanMethod: 'upc'
  });

  const image = ref('');
  const MAX_WIDTH = 620;
  const MAX_HEIGHT = 880;

  let onChange = (event) => {
    //form.image = event.target.files ? event.target.files[0] : null;

    image.value = event.target.files[0];
    const file = event.target.files[0]; // get the file
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
          // Handle the compressed image.
          form.image = blob
          form.post(route('scanUpcCode'), {
            onSuccess: (response) => {
                upcCode.value = response.props.code
                // form.reset()
                form.get(route('UpcLookup'), {
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: () => {
                      form.upc_code = response.props.code
                    }
                });
            }
          });
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

  const submit = () => {
    itemInfo.value = [];
    if(selected.value == 'upc'){
      form.item = 0;
    }

    if(selected.value == 'item'){
      form.upc_code = '';
    }
    form.post(route('lookupUpcCode'), {
        onSuccess: (response) => {
            if(response.props.data.isEmpty == 0){
              itemInfo.value = response.props.data.manifest
              itemInfo.value.upc_code = response.props.data.upc_code

              previewItemModal.value = true
              itemInfo.value.confirmed = true;
            }else{

              itemInfo.value = response.props.data
              itemInfo.value.confirmed = true;
            }
            
            
            form.get(route('UpcLookup'), {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                  
                }
            });
        }
    });
  }

  const closeModal = () => {
    form.get(route('UpcLookup'), {
        onSuccess: () => {
          form.reset();
          form.item = '';
          form.description = '';
          form.msrp = '';
          form.retail_price = '';
          form.image = '';
          form.type = '';
          form.upc_code = '';
          form.images = '';
          itemInfo.value = [];
          previewItemModal.value = false;
        }
    });
  };

  const save = () => {
    form.description = itemInfo.value.description;
    form.msrp = itemInfo.value.msrp;
    form.retail_price = itemInfo.value.retail_price;
    form.item = itemInfo.value.item;
    form.upc_code = itemInfo.value.upc_code
    form.type = itemInfo.value.type
    buttonDisabled.value = true;
    form.post(route('addItem'), {
        onSuccess: () => {
          form.get(route('UpcLookup'), {
            onSuccess: () => {
              form.reset()
              form.upc_code = '';
              itemInfo.value = [];
              previewItemModal.value = false;
              buttonDisabled.value = false;
              toaster.info('Item saved successfully.', {
                position: "top-right",
              });
            },
            onError: () => {
              buttonDisabled.value = false;
            }
          });
        }
    });
  }

  const selected = ref('upc')
  const screenWidth = ref(screen.width)

  const imageThumb = ref([]);
  const fileUpload = ref(null);
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

  let onImageChange = (event) => {
    //form.image = event.target.files ? event.target.files[0] : null;
    imageThumb.value = []
    for (var i = 0; i < fileUpload.value.files.length; i++ ){
      let file = fileUpload.value.files[i];
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
            // Handle the compressed image.
            form.image.push(blob)
          },
        );
      };
    }
  }

</script>

<template>
  <Head title="Lookup" />
  <GuestLayout>
    <template #header>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight dark:text-white">Item Lookup</h2>
    </template>

    <div class="py-3 mb-40">

      <div class="bg-white dark:bg-indigo-100 py-8 px-4 rounded-lg">
        <InputLabel for="item" class="dark:text-gray-800 text-xl" value="Item UPC Code"/>
        <form @submit.prevent="submit" >
          <div class="grid grid cols-3 grid-cols-subgrid gap-4">
            <TextInput
                id="item"
                v-model="form.upc_code"
                type="readonly"
                class="border-solid border-2 border-black-600 p-2 mt-1 block block w-full col-span-2"
                placeholder="Item UPC Code"
            />
            <label for="myfileid" class="icon pt-4 dark:text-gray-800 col-start-3">
              <span v-if="screenWidth < 640">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                </svg>
              </span>
              <span v-else>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
              </span>
            </label>
            <input id="myfileid" style="display:none;" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" type="file"  accept="image/*" capture="camera" @change="onChange">
          </div>

          <PrimaryButton class="mt-10 dark:bg-gray-400 dark:text-white px-4 " v-if="form.upc_code.length == 0" disabled>Lookup UPC Record</PrimaryButton>
          <PrimaryButton class="mt-10 dark:bg-red-400 dark:text-white px-4 " v-if="form.upc_code.length > 0">Lookup UPC Record</PrimaryButton>
        </form>
      </div>

      <div class="mt-6 border-2 px-5 pt-6 bg-indigo-100 dark:bg-indigo-100 rounded-lg" v-if="itemInfo && itemInfo.confirmed == true">
        <div v-if="buttonDisabled == false" class="dark:text-black font-extrabold float-right" @click.prevent="itemInfo = [];itemInfo.confirmed = false;form.upc_code = ''">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
          </svg>

        </div>
        <div class="clear-both pt-5"></div>
        <h2 class="text-2xl pb-3 font-extrabold dark:text-black" v-if="itemInfo.isEmpty == 1">Item not found. Please enter the details below.</h2>
        <div class="mt-2">
          <InputLabel class="dark:text-black text-xl" for="item" value="Item Description"/>
          <TextInput
              id="item"
              v-model="itemInfo.description"
              type="text"
              class="border-solid border-2 border-black-600 p-2 mt-1 block block w-full"
              placeholder="Item Description" required
          />
          <InputError class="mt-2" :message="form.errors.description" />
        </div>

        <div class="mt-2">
          <InputLabel class="dark:text-black text-xl" for="item" value="Item Number"/>
          <TextInput
              id="item"
              v-model="itemInfo.item"
              type="number"
              class="border-solid border-2 border-black-600 p-2 mt-1 block block w-full"
              maxlength="8"
              oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
              placeholder="Item Number" :readonly="selected == 'item'" required
          />
        </div>

        <div class="mt-2">
          <InputLabel class="dark:text-black text-xl" for="item" value="Item UPC Code"/>
          <TextInput
              id="item"
              v-model="itemInfo.upc_code"
              type="number"
              class="border-solid border-2 border-black-600 p-2 mt-1 block block w-full bg-gray-200"
              placeholder="Item UPC Code" :readonly="selected == 'upc'"
          />
        </div>

        <div class="mt-2">
          <InputLabel class="dark:text-black text-xl" for="item" value="Item MSRP"/>
          <TextInput
              id="item"
              v-model="itemInfo.msrp"
              type="number"
              class="border-solid border-2 border-black-600 p-2 mt-1 block block w-full"
              placeholder="Item MSRP" required
          />
        </div>

        <div class="mt-2">
          <InputLabel class="dark:text-black text-xl" for="item" value="Item Retail Price"/>
          <TextInput
              id="item"
              v-model="itemInfo.retail_price"
              type="number"
              class="border-solid border-2 border-black-600 p-2 mt-1 block block w-full"
              placeholder="Item Retail Price" required
          />
        </div>

        <div class="mt-2">
          <InputLabel class="dark:text-black text-xl" for="item" value="Item Images"/>

          <div class="grid grid-cols-3 md:grid-cols-3 gap-4 pb-4">
            <div class="mt-2 bg-stone-300 py-4 px-4 rounded-lg flex justify-center items-center" v-if="itemInfo.images" v-for="image in JSON.parse(itemInfo.images)">
              <img :src="(image)"  class="h-auto max-w-full">
            </div>
          </div>
        </div>

        <div class="mt-2">
          <InputLabel class="dark:text-black text-xl pb-2" for="item" value="Upload Item Image(s)"/>

          <div class="grid grid cols-3 grid-cols-subgrid gap-4">
            <input class="block text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 w-full" id="file_input" type="file" multiple="multiple" accept="image/*" @change="onImageChange" ref="fileUpload">

            <label for="addImage" class="icon pl-4 dark:text-black col-start-3">
                <span>
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                  </svg>
                </span>
              </label>
              <input id="addImage" style="display:none;" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" type="file"  accept="image/*" capture="camera" @change="onImageChangeCapture">
            </div>
            <div class="grid grid-cols-3 gap-1 pt-4 pb-4" v-if="imageThumb">
              <img v-for="image in imageThumb" :src="image" width="100">
            </div>
        </div>

        <div>
          <InputLabel class="dark:text-black text-xl" for="item" value="Item type"/>
          <select
            class="w-full rounded-lg"
            v-model="itemInfo.type"
            name="type"
          >
            <option value="Clothing">Clothing</option>
            <option value="Mixed">Mixed</option>
          </select>
        </div>

        <div class="mt-6 mb-6 flex justify-end">
            <SecondaryButton v-if="buttonDisabled == false" class="mr-4" @click="closeModal"> Close </SecondaryButton>
            <PrimaryButton v-if="buttonDisabled == false" class="mr-4 bg-green-400 text-xs px-4" @click="save"> Submit </PrimaryButton>

            <button v-if="buttonDisabled == true" type="button" class="bg-indigo-400 h-max w-max rounded-lg text-white font-bold hover:bg-indigo-300 hover:cursor-not-allowed duration-[500ms,800ms]" disabled>
              <div class="flex items-center justify-center m-[10px]"> 
                  <div class="h-5 w-5 border-t-transparent border-solid animate-spin rounded-full border-white border-4"></div>
                  <div class="ml-2"> Processing... </div>
              </div>
          </button>
        </div>
      </div>

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
