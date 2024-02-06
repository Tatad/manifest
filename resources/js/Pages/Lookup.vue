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
    image: '',
    type: '',
    msrp: 0,
    retail_price: 0
  });

  let onChange = (event) => {
    form.image = event.target.files ? event.target.files[0] : null;
    form.post(route('scanUpcCode'), {
        onSuccess: (response) => {
            console.log(response.props.code)
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
            console.log(response.props.data)
            if(response.props.data.isEmpty == 0){
              itemInfo.value = response.props.data.manifest
              itemInfo.value.upc_code = response.props.data.upc_code

              previewItemModal.value = true
              itemInfo.value.confirmed = false;
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
          form.upc_code = ''
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
    console.log(form)
    form.post(route('addItem'), {
        onSuccess: () => {
          form.reset()
          openEnlargeImageModal.value = false;
          itemNumberModal.value = false;
          itemInfo.value = []
        }
    });
  }

  const selected = ref('item')
</script>

<template>
  <Head title="Lookup" />
  <GuestLayout>
    <template #header>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Item Lookup</h2>
    </template>

    <div class="py-3 mb-6">
      <!-- <div class="flex mb-6">
        <InputLabel for="item" value="Scan Item by:"/>
        <div class="pl-2 flex items-center me-4">
            <input v-model="selected" id="inline-radio" type="radio" value="upc" name="inline-radio-group" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
            <label for="inline-radio" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">UPC Code</label>
        </div>
        <div class="flex items-center me-4">
            <input v-model="selected" id="inline-2-radio" type="radio" value="item" name="inline-radio-group" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
            <label for="inline-2-radio" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Item Number</label>
        </div>
      </div> -->

      <div v-if="selected == 'upc'">
        <InputLabel for="item" value="Item UPC Code"/>
        <div class="grid grid-cols-2 gap-1">
          <TextInput
              id="item"
              v-model="form.upc_code"
              type="readonly"
              class="border-solid border-2 border-black-600 p-2 mt-1 block block w-full"
              placeholder="Item UPC Code"
          />
          <label for="myfileid" class="icon pt-4">
            <span>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
              </svg>
            </span>
          </label>
          <input id="myfileid" style="display:none;" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" type="file"  accept="image/*" capture="camera" @change="onChange">
        </div>
      </div>
      <div v-if="selected == 'item'">
        <InputLabel for="item"  class="dark:text-white" value="Item Number"/>
        <div class="grid grid-cols-2 gap-1">
          <TextInput
              id="item"
              v-model="form.item"
              type="readonly"
              class="border-solid border-2 border-black-600 p-2 mt-1 block block w-full"
              placeholder="Item Number"
          />
        </div>
      </div>

      <PrimaryButton class="mt-10" @click.prevent="submit">Lookup UPC Record</PrimaryButton>

      <!--preview item modal-->
      <Modal :show="previewItemModal" @close="closeModal">
          <div class="p-6" v-if="itemInfo">
            <h2 class="text-2xl font-extrabold dark:text-white">Is this the item you are looking for?</h2>
            <div class="mt-6">
               <div class="mt-2">
                  <InputLabel for="item" value="Item Description"/>
                  <TextInput
                      id="item"
                      v-model="itemInfo.description"
                      type="text"
                      class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                      placeholder="Item Description" readonly
                  />
                </div>

                <div class="mt-2">
                  <InputLabel for="item" value="Item Number"/>
                  <TextInput
                      id="item"
                      v-model="itemInfo.item"
                      type="number"
                      class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                      placeholder="Item Number" readonly
                  />
                </div>

                <div class="mt-2">
                  <InputLabel for="item" value="Item UPC Code"/>
                  <TextInput
                      id="item"
                      v-model="itemInfo.upc_code"
                      type="number"
                      class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                      placeholder="Item UPC Code" readonly
                  />
                </div>

                <div class="mt-2">
                  <InputLabel for="item" value="Item MSRP"/>
                  <TextInput
                      id="item"
                      v-model="itemInfo.msrp"
                      type="number"
                      class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                      placeholder="Item MSRP" readonly
                  />
                </div>

                <div class="mt-2">
                  <InputLabel for="item" value="Item Retail Price"/>
                  <TextInput
                      id="item"
                      v-model="itemInfo.retail_price"
                      type="number"
                      class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                      placeholder="Item Retail Price" readonly
                  />
                </div>

                <div class="mt-2" v-if="itemInfo.images" v-for="image in JSON.parse(itemInfo.images)">
                  <InputLabel for="item" value="Item image"/>
                  <img :src="image"  class="pt-6 object-cover w-52">
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <SecondaryButton class="mr-4" @click="closeModal"> No </SecondaryButton>
                <PrimaryButton class="mr-4" @click="itemInfo.confirmed = true;previewItemModal = false"> Yes </PrimaryButton>
            </div>
          </div>
      </Modal>

      <div class="mt-6 border-2 pl-5 pt-6" v-if="itemInfo && itemInfo.confirmed == true">

        <h2 class="text-2xl pb-3 font-extrabold dark:text-white" v-if="itemInfo.isEmpty == 1">Item not found. Please enter the details below.</h2>
        <div class="mt-2">
          <InputLabel for="item" value="Item Description"/>
          <TextInput
              id="item"
              v-model="itemInfo.description"
              type="text"
              class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
              placeholder="Item Description" required
          />
        </div>

        <div class="mt-2">
          <InputLabel for="item" value="Item Number"/>
          <TextInput
              id="item"
              v-model="itemInfo.item"
              type="number"
              maxlength="8"
              oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
              class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4 bg-gray-200"
              placeholder="Item Number" :readonly="selected == 'item'"
          />
        </div>

        <div class="mt-2">
          <InputLabel for="item" class="dark:text-white" value="Item UPC Code"/>
          <TextInput
              id="item"
              v-model="itemInfo.upc_code"
              type="number"
              class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
              placeholder="Item UPC Code" :readonly="selected == 'upc'" required
          />
        </div>

        <div class="mt-2">
          <InputLabel for="item" value="Item MSRP"/>
          <TextInput
              id="item"
              v-model="itemInfo.msrp"
              type="number"
              class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
              placeholder="Item MSRP" required
          />
        </div>

        <div class="mt-2">
          <InputLabel for="item" value="Item Retail Price"/>
          <TextInput
              id="item"
              v-model="itemInfo.retail_price"
              type="number"
              class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
              placeholder="Item Retail Price" required
          />
        </div>

        <div class="mt-2" v-if="itemInfo.images" v-for="image in JSON.parse(itemInfo.images)">
          <InputLabel for="item" value="Item image"/>
          <img :src="image"  class="pt-6 object-cover w-52">
        </div>

        <div class="mt-6 mb-6 flex justify-end">
            <PrimaryButton v-if="buttonDisabled == false" class="mr-4" @click="save"> Submit </PrimaryButton>


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