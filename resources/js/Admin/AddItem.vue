<script  lang="ts" setup>
	import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
	import Modal from '@/Components/Modal.vue';
	import SecondaryButton from '@/Components/SecondaryButton.vue';
	import PrimaryButton from '@/Components/PrimaryButton.vue';
	import TextInput from '@/Components/TextInput.vue';
	import InputLabel from '@/Components/InputLabel.vue';
	import InputError from '@/Components/InputError.vue';	
  	import { createToaster } from "@meforma/vue-toaster";
	import { useForm } from '@inertiajs/vue3';

	const props = defineProps({
	    show: {
	        type: Boolean,
	        default: false,
	    },
	    closeable: {
	        type: Boolean,
	        default: true,
	    },
	    pallets: {
	        type: Array
	    },
	});

	const form = useForm({
	    description: '1',
	    item_number: '1660896',
	    type: 'Clothing',
	    pallet: 2287,
    	image: [],
	    quantity: '4',
	    msrp: '5',
	    upc_code: [],
	    retail_price: '7',
	    errors: []
	});

	const palletsData = ref(props.pallets)

	const close = () => {
	    if (props.closeable) {
	        emit('close');
	    }
	};

	const closeOnEscape = (e) => {
	    if (e.key === 'Escape' && props.show) {
	        close();
	    }
	};

	onMounted(() => document.addEventListener('keydown', closeOnEscape));

	onUnmounted(() => {
	    document.removeEventListener('keydown', closeOnEscape);
	    document.body.style.overflow = null;
	});

	const emit = defineEmits(['close']);

	const showModal = ref(true)

	const image = ref('');
  	const imageThumb = ref([]);
  	const MAX_WIDTH = 620;
  	const MAX_HEIGHT = 880;
  	const fileUpload = ref(null);
  	let onChange = (event) => {
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
		form.post(route('admin.addItem'), {
	        onSuccess: () => {
	          form.get(route('admin'), {
	            onSuccess: () => {
	              toaster.info('Item saved successfully.', {
	                position: "top-right",
	              });
	            }
	          });
	        },
	        onError: () => {
	        	toaster.warning('Item not saved.', {
	                position: "top-right",
	            });
	        }
	    });
	}

  	const toaster = createToaster({ /* options */ });

  	const addMore = () => {
	    form.upc_code.push([]);
	}

	const removeElement = (index) => {
	    form.upc_code.splice(index, 1);
	}
</script>

<template>
	<div>
		<Modal :show="showModal">
	        <div class="p-6">
              	<div class="mt-6">
                    <InputLabel class="dark:text-black" for="item_number" value="Item Number"/>
                    <TextInput
                        id="item_number"
                        v-model="form.item_number"
                        type="number"
                        class="border-solid border-2 border-black-600 p-2 mt-1 block block w-full"
                        placeholder="Item Number"
                    />
                    <InputError :message="form.errors.item_number" class="mt-2" />
                </div>

                <div class="mt-6">
                	<InputLabel class="dark:text-black" for="description" value="Item Description"/>
                    <TextInput
                        id="description"
                        v-model="form.description"
                        type="text"
                        class="border-solid border-2 border-black-600 p-2 mt-1 block block w-full"
                        placeholder="Item Description"
                    />
                </div>

                <div class="mt-6">
                    <InputLabel class="dark:text-black" for="upc_code" value="Item UPC Code"/>
                    <!-- <TextInput
                        id="upc_code"
                        v-model="form.upc_code[0]"
                        type="number"
                        class="border-solid border-2 border-black-600 p-2 mt-1 block block w-full"
                        placeholder="UPC Code"
                    /> -->
                    <div v-for="(row, index) in form.upc_code">
                        <TextInput
                            id="upc"
                            :value="row"
                            @input="event => form.upc_code[index] = event.target.value"
                            type="text"
                            class="mt-2 block w-full"
                            placeholder="UPC Code"
                        />
                        <a v-on:click="removeElement(index);" style="cursor: pointer" class="mt-2 inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-red-500 hover:text-white dark:hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">Remove</a>
                	</div>
                	<hr class="my-2 font-bold"/>
                	<button
				        type="button"
				        class="flex justify-start ml-2 rounded-md border px-3 py-2 bg-gray-600 text-white"
				        @click="addMore()"
			      	>
				        Add More
			      	</button>
                	<hr class="my-2 font-bold"/>
                </div>

                <div class="mt-6">
                    <InputLabel class="dark:text-black" for="quantity" value="Item Quantity"/>
                    <TextInput
                        id="quantity"
                        v-model="form.quantity"
                        type="number"
                        class="border-solid border-2 border-black-600 p-2 mt-1 block block w-full"
                        placeholder="Item Quantity"
                    />
                </div>

                <div class="mt-6">
                    <InputLabel class="dark:text-black" for="msrp" value="Item MSRP"/>
                    <TextInput
                        id="msrp"
                        v-model="form.msrp"
                        type="number"
                        class="border-solid border-2 border-black-600 p-2 mt-1 block block w-full"
                        placeholder="Item MSRP"
                    />
                </div>

                <div class="mt-6">
                    <InputLabel class="dark:text-black" for="retail_price" value="Item Retail Price"/>
                    <TextInput
                        id="retail_price"
                        v-model="form.retail_price"
                        type="number"
                        class="border-solid border-2 border-black-600 p-2 mt-1 block block w-full"
                        placeholder="Item Retail Price"
                    />
                </div>

                <div class="mt-6">
                    <InputLabel class="dark:text-black" for="pallet" value="Pallet #"/>
                    <select
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm border-solid border-2 border-black-600 p-2 mt-1 block block w-full"
                        v-model="form.pallet"
                        name="type"
                      >
                        <option :value="pallet.pallet" v-for="pallet in pallets">{{pallet.pallet}}</option>
                      </select>
                </div>

                <div class="mt-6">
                    <InputLabel class="dark:text-black" for="type" value="Item Type"/>
                    <select
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm border-solid border-2 border-black-600 p-2 mt-1 block block w-full"
                        v-model="form.type"
                        name="type"
                      >
                        <option value="Clothing">Clothing</option>
                        <option value="Mixed">Mixed</option>
                      </select>
                </div>

                <div class="mt-6">
                	<InputLabel class="dark:text-black text-xl pb-2" for="item" value="Upload Item Image(s)"/>
                    <input class="block text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 w-full" id="file_input" type="file"  multiple="multiple" accept="image/*" @change="onChange" ref="fileUpload">

                    <div class="grid grid-cols-3 gap-1 pt-4 pb-4" v-if="imageThumb">
		            	<img v-for="image in imageThumb" :src="image" width="100">
		          	</div>
                </div>

	           	<div class="mt-6 flex justify-end">
	                <SecondaryButton class="mr-4" @click="close"> Close </SecondaryButton>
	                <PrimaryButton class="mr-4" @click.prevent="submit()"> Submit </PrimaryButton>
	            </div>
	        </div>
      	</Modal>
	</div>
</template>