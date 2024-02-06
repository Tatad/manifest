<script setup>
import { computed } from 'vue';
const path = (new URL(location.href).pathname);
console.log(path)

</script>

<template>
    <div class="fixed bottom-0 left-0 z-50 w-full h-16 bg-white border-t border-gray-200 dark:bg-gray-700 dark:border-gray-600">
	    <div class="grid h-full max-w-lg grid-cols-4 mx-auto font-medium">
	      <a href="/scanner" class="border-r-2 border-gray-400 inline-flex flex-col items-center justify-center px-2 hover:bg-gray-50 dark:hover:bg-gray-800 group" :class="(path == '/scanner') ? 'bg-gray-400 hover:bg-gray-600' : ''">
	        <button type="button">
	            <span class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500" :class="(path == '/scanner') ? 'text-white group-hover:text-white' : ''">Scan Items</span>
	        </button>
	      </a>
	      <a href="/scanned-list" class="border-r-2 border-gray-400 inline-flex flex-col items-center justify-center px-2 hover:bg-gray-50 dark:hover:bg-gray-800 group" :class="(path == '/scanned-list') ? 'bg-gray-400 hover:bg-gray-600' : ''">
	        <button type="button">
	            <span class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500" :class="(path == '/scanned-list') ? 'text-white group-hover:text-white' : ''">Review Scanned Item</span>
	        </button>
	      </a>
	      <a href="/upc-lookup" class="inline-flex flex-col items-center justify-center px-2 hover:bg-gray-50 dark:hover:bg-gray-800 group" :class="(path == '/upc-lookup') ? 'bg-gray-400 hover:bg-gray-600' : ''">
	        <button type="button">
	            <span class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500" :class="(path == '/upc-lookup') ? 'text-white group-hover:text-white' : ''">UPC Lookup</span>
	        </button>
	      </a>
	      <a href="/lookup" class="border-l-2 border-gray-400 inline-flex flex-col items-center justify-center px-2 hover:bg-gray-50 dark:hover:bg-gray-800 group" :class="(path == '/lookup') ? 'bg-gray-400 hover:bg-gray-600' : ''">
	        <button type="button">
	            <span class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500" :class="(path == '/lookup') ? 'text-white group-hover:text-white' : ''">Item # Lookup</span>
	        </button>
	      </a>
	    </div>
	  </div>
</template>

