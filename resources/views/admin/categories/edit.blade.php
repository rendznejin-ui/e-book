<x-admin-layout>
    <x-slot name="title">Edit Category</x-slot>

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="max-w-lg">
        @csrf @method('PUT')
        @include('admin.categories._fields')
    </form>
</x-admin-layout>
