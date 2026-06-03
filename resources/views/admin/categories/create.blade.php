<x-admin-layout>
    <x-slot name="title">New Category</x-slot>

    <form method="POST" action="{{ route('admin.categories.store') }}" class="max-w-lg">
        @csrf
        @include('admin.categories._fields')
    </form>
</x-admin-layout>
