<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
            <div class="">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __($photo->name) }}
                </h2>
                <p class="text-gray-500 dark:text-gray-400">{{ $photo->description }}</p>
            </div>
            <a class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                href="{{ route('albums.photos.create', $photo) }}">Upload Photo</a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid sm:grid-cols-3 gap-4 mb-6">
                <img class="rounded-lg" src="{{ Storage::url($photo->image) }}" alt="">
                <div class="sm:col-span-2">
                    <div class="flex justify-between">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $photo->name }}</h2>
                        <div class="flex gap-4">
                            <a href="{{ route('photos.edit', $photo) }}"
                                class="text-gray-800 dark:text-gray-200 hover:text-gray-600 dark:hover:text-gray-400">Edit</a>
                            <form action="{{ route('photos.destroy', $photo) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-red-600 dark:text-red-400 hover:text-red-500 dark:hover:text-red-300">Delete</button>
                            </form>
                        </div>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">{{ $photo->description }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
