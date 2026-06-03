<x-admin-layout>
    <x-slot name="title">New Book</x-slot>
    @include('admin.books._form', ['action' => route('admin.books.store'), 'method' => 'POST'])
</x-admin-layout>
