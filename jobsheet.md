## Persiapan

**1. Install laravel**

```bash
composer global require laravel/installer
laravel new gallery-app --breeze --stack blade --dark --git --pest --database sqlite
cd gallery-app
```

## Membuat Model, Migration dan Controller

**1. Membuat Model**

```bash
php artisan make:model Album -mcr
php artisan make:model Photo -mcr
```

model album di `app\Models\Album.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Album extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
        'user_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }
}
```

model photo di `app\Models\Photo.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photo extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
        'album_id',
        'user_id'
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

dan juga edit User.php

```php
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function albums(): HasMany
    {
        return $this->hasMany(Album::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }
}
```

**2. Membuat migration**

migration album di `database\migrations\2025_01_14_013515_create_albums_table.php`

```php
Schema::create('albums',  function  (Blueprint $table)  {
	$table->id();
	$table->string('name');
	$table->string('description')->nullable();
	$table->string('image');
	$table->foreignId('user_id')->constrained()->onDelete('cascade');
	$table->timestamps();
});
```

migration photo di `database\migrations\2025_01_14_013515_create_photos_table.php`

```php
Schema::create('photos',  function  (Blueprint  $table)  {
	$table->id();
	$table->string('name');
	$table->string('description');
	$table->string('image');
	$table->foreignId('album_id')->constrained()->onDelete('cascade');
	$table->foreignId('user_id')->constrained()->onDelete('cascade');
	$table->timestamps();
});
```

**3. Membuat Controller**

Album Controller

```php
/**
 * Display a listing of the resource.
 */
public function index()
{
    $albums = Album::all();
    return view('albums.index', compact('albums'));
}

/**
 * Show the form for creating a new resource.
 */
public function create()
{
    return view('albums.create');
}

/**
 * Store a newly created resource in storage.
 */
public function store(Request $request)
{
    $request->validate([
        'name' => 'required',
        'description' => 'required',
        'image' => 'required|image',
    ]);

    $request->user()->albums()->create([
        'name' => $request->name,
        'description' => $request->description,
        'image' => $request->image->store('albums'),
    ]);

    return to_route('albums.index');
}

/**
 * Display the specified resource.
 */
public function show(Album $album)
{
    return view('albums.show', compact('album'));
}

/**
 * Show the form for editing the specified resource.
 */
public function edit(Album $album)
{
    return view('albums.edit', compact('album'));
}

/**
 * Update the specified resource in storage.
 */
public function update(Request $request, Album $album)
{
    $request->validate([
        'name' => 'required',
        'description' => 'required',
        'image' => 'nullable|image',
    ]);
    $input = [
        'name' => $request->name,
        'description' => $request->description,
    ];
    if ($request->image) {
        $input['image'] = $request->image->store('albums');
    }
    $album->update($input);

    return to_route('albums.show', $album);
}

/**
 * Remove the specified resource from storage.
 */
public function destroy(Album $album)
{
    $album->delete();
    return to_route('albums.index');
}
```

Photo Controller

```php
/**
 * Display a listing of the resource.
 */
public function index()
{
    $photos = Photo::all();

    return view('photos.index', compact('photos'));
}
/**
 * Show the form for creating a new resource.
 */
public function create(Album $album)
{
    return view('photos.create', compact('album'));
}

/**
 * Store a newly created resource in storage.
 */
public function store(Request $request, Album $album)
{
    $request->validate([
        'name' => 'required',
        'description' => 'required',
        'image' => 'required|image',
    ]);

    $request->user()->photos()->create([
        'name' => $request->name,
        'description' => $request->description,
        'image' => $request->image->store('photos'),
        'album_id' => $album->id,
    ]);

    return redirect()->route('albums.show', $album->id);
}

/**
 * Display the specified resource.
 */
public function show(Photo $photo)
{
    return view('photos.show', compact('photo'));
}

/**
 * Show the form for editing the specified resource.
 */
public function edit(Photo $photo, Album $album)
{
    return view('photos.edit', compact('photo'));
}

/**
 * Update the specified resource in storage.
 */
public function update(Request $request, Photo $photo)
{
    $request->validate([
        'name' => 'required',
        'description' => 'required',
        'image' => 'nullable|image',
    ]);
    $input = [
        'name' => $request->name,
        'description' => $request->description,
    ];
    if ($request->hasFile('image')) {
        $input['image'] = $request->image->store('photos');
    }
    $photo->update(
        $input
    );

    return redirect()->route('photos.show', $photo->id);
}

/**
 * Remove the specified resource from storage.
 */
public function destroy(Photo $photo, Album $album)
{
    $photo->delete();

    return redirect()->route('albums.show', $photo->album_id);
}
```

## Membuat route dan storage

**1. Membuat route**

pada web.php tambahkan

```php
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('albums', \App\Http\Controllers\AlbumController::class);
    Route::resource('albums.photos', \App\Http\Controllers\PhotoController::class)
        ->shallow()
        ->except('index');
    Route::get('photos', [\App\Http\Controllers\PhotoController::class, 'index'])->name('photos.index');
});
```

lalu pada `navigation.blade.php` tambahkan route albums dan photos

```php
<!-- Navigation Links -->
<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
        {{ __('Dashboard') }}
    </x-nav-link>
    <x-nav-link :href="route('albums.index')" :active="request()->routeIs('albums.*')">
        {{ __('Albums') }}
    </x-nav-link>
    <x-nav-link :href="route('photos.index')" :active="request()->routeIs('photos.*')">
        {{ __('Photos') }}
    </x-nav-link>
</div>
```

```php
<!-- Responsive Navigation Menu -->
<div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
    <div class="pt-2 pb-3 space-y-1">
        <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            {{ __('Dashboard') }}
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('albums.index')" :active="request()->routeIs('albums.*')">
            {{ __('Albums') }}
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('photos.index')" :active="request()->routeIs('photos.*')">
            {{ __('Photos') }}
        </x-responsive-nav-link>
    </div>
```

**2. Setting bagian storage**

ubah di dalam `FILESYSTEM_DISK` di `.env` menjadi `public`

```env
FILESYSTEM_DISK=public
```

lalu jalankan command

```bash
php artisan storage:link
```

## Membuat view

buat file dengan struktur seperti berikut

```
resources/views/
├── albums/
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
└── photos/
    ├── index.blade.php
    ├── show.blade.php
    ├── create.blade.php
    └── edit.blade.php
```

lalu isi file nya seperti berikut

`albums/index.blade.php`

```php
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Albums') }}
            </h2>
            <a class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                href="{{ route('albums.create') }}">Create Album</a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                @foreach ($albums as $album)
                    <div class="album bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden relative">
                        <a class="inset-0 size-full absolute" href="{{ route('albums.show', $album) }}"></a>
                        <img src="{{ Storage::url($album->image) }}" class="w-full h-48 object-cover">
                        <div class="p-4">
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $album->name }}</h2>
                            <p class="text-gray-600 dark:text-gray-400">{{ $album->description }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
```

`albums/create.blade.php`

```php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Album') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Create Album</h2>
                    <p class="text-gray-700 dark:text-gray-400">
                        Fill in the form below to create a new album.
                    </p>
                </div>
                <form action="{{ route('albums.store') }}" method="POST" class="grid space-y-4"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <x-input-label for="name" value="Album Name" />
                        <x-text-input type="text" class="w-full" id="name" name="name" required />
                        <x-input-error for="name" class="mt-2" :messages="$errors->get('name')" />
                    </div>
                    <div class="mb-3">
                        <x-input-label for="description" value="Description" />
                        <x-text-input class="w-full" id="description" name="description" rows="3" required />
                        <x-input-error for="description" class="mt-2" :messages="$errors->get('description')" />

                    </div>
                    <div class="pb-3">
                        <x-input-label for="image" value="Cover Image" />
                        <x-text-input type="file" class="w-full" id="image" name="image" required />
                        <x-input-error for="image" class="mt-2" :messages="$errors->get('image')" />
                    </div>
                    <div class="flex">
                        <x-primary-button type="submit" class="btn btn-primary">Create Album</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
```

`albums/edit.blade.php`

```php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Album') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Edit Album</h2>
                    <p class="text-gray-700 dark:text-gray-400">
                        Fill in the form below to edit a new album.
                    </p>
                </div>
                <form action="{{ route('albums.update', $album) }}" method="POST" class="grid space-y-4"
                    enctype="multipart/form-data">
                    @method('PUT')
                    @csrf
                    <div class="mb-3">
                        <x-input-label for="name" value="Album Name" />
                        <x-text-input type="text" class="w-full" :value="old('name', $album->name)" id="name" name="name" required />
                        <x-input-error for="name" class="mt-2" :messages="$errors->get('name')" />
                    </div>
                    <div class="mb-3">
                        <x-input-label for="description" value="Description" />
                        <x-text-input class="w-full" id="description" :value="old('description', $album->description)" name="description" rows="3" required />
                        <x-input-error for="description" class="mt-2" :messages="$errors->get('description')" />

                    </div>
                    <div class="pb-3">
                        <img src="{{Storage::url($album->image)}}" class=" rounded-lg w-[300px]" alt="">
                        <x-input-label for="image" value="Cover Image" />
                        <x-text-input type="file" class="w-full" :value="old('image', $album->image)" id="image" name="image"/>
                        <x-input-error for="image" class="mt-2" :messages="$errors->get('image')" />
                    </div>
                    <div class="flex">
                        <x-primary-button type="submit" class="btn btn-primary">Edit Album</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
```

`albums/show.blade.php`

```php
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
            <div class="">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __($album->name) }}
                </h2>
                <p class="text-gray-500 dark:text-gray-400">{{ $album->description }}</p>
            </div>
            <a class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                href="{{ route('albums.photos.create', $album) }}">Upload Photo</a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid sm:grid-cols-3 gap-4 mb-6">
                <img class="rounded-lg" src="{{ Storage::url($album->image) }}" alt="">
                <div class="sm:col-span-2">
                    <div class="flex justify-between">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $album->name }}</h2>
                        <div class="flex gap-4">
                            <a href="{{ route('albums.edit', $album) }}"
                                class="text-gray-800 dark:text-gray-200 hover:text-gray-600 dark:hover:text-gray-400">Edit</a>
                            <form action="{{ route('albums.destroy', $album) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-red-600 dark:text-red-400 hover:text-red-500 dark:hover:text-red-300">Delete</button>
                            </form>
                        </div>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">{{ $album->description }}</p>
                </div>
            </div>

            <h3 class="text-2xl dark:text-gray-200 text-gray-800 mb-4">Photos</h3>
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                @foreach ($album->photos as $photo)
                    <div class="album bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden relative">
                        <a class="inset-0 size-full absolute" href="{{ route('photos.show', $photo) }}"></a>
                        <img src="{{ Storage::url($photo->image) }}" class="w-full h-48 object-cover">
                        <div class="p-4">
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $photo->name }}
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400">{{ $photo->description }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
```

`photos/index.blade.php`

```php
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('photos') }}
            </h2>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @foreach ($albums as $album)
                <div class="mb-6 border-b pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ $album->name }}</h2>
                    <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                        @foreach ($album->photos as $photo)
                            <div class="photo bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden relative">
                                <a class="inset-0 size-full absolute" href="{{ route('photos.show', $photo) }}"></a>
                                <img src="{{ Storage::url($photo->image) }}" class="w-full h-48 object-cover">
                                <div class="p-4">
                                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                        {{ $photo->name }}
                                    </h2>
                                    <p class="text-gray-600 dark:text-gray-400">{{ $photo->description }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
```

`photos/create.blade.php`

```php
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('photos') }}
            </h2>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @foreach ($albums as $album)
                <div class="mb-6 border-b pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ $album->name }}</h2>
                    <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                        @foreach ($album->photos as $photo)
                            <div class="photo bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden relative">
                                <a class="inset-0 size-full absolute" href="{{ route('photos.show', $photo) }}"></a>
                                <img src="{{ Storage::url($photo->image) }}" class="w-full h-48 object-cover">
                                <div class="p-4">
                                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                        {{ $photo->name }}
                                    </h2>
                                    <p class="text-gray-600 dark:text-gray-400">{{ $photo->description }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
```

`photos/edit.blade.php`

```php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit photo') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Edit photo</h2>
                    <p class="text-gray-700 dark:text-gray-400">
                        Fill in the form below to edit a new photo.
                    </p>
                </div>
                <form action="{{ route('photos.update', $photo) }}" method="POST" class="grid space-y-4"
                    enctype="multipart/form-data">
                    @method('PUT')
                    @csrf
                    <div class="mb-3">
                        <x-input-label for="name" value="photo Name" />
                        <x-text-input type="text" class="w-full" :value="old('name', $photo->name)" id="name" name="name"
                            required />
                        <x-input-error for="name" class="mt-2" :messages="$errors->get('name')" />
                    </div>
                    <div class="mb-3">
                        <x-input-label for="description" value="Description" />
                        <x-text-input class="w-full" id="description" :value="old('description', $photo->description)" name="description"
                            rows="3" required />
                        <x-input-error for="description" class="mt-2" :messages="$errors->get('description')" />

                    </div>
                    <div class="pb-3">
                        <img src="{{ Storage::url($photo->image) }}" class=" rounded-lg w-[300px]" alt="">
                        <x-input-label for="image" value="Cover Image" />
                        <x-text-input type="file" class="w-full" :value="old('image', $photo->image)" id="image" name="image" />
                        <x-input-error for="image" class="mt-2" :messages="$errors->get('image')" />
                    </div>
                    <div class="flex">
                        <x-primary-button type="submit" class="btn btn-primary">Edit photo</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
```

`photos/show.blade.php`

```php
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
```
