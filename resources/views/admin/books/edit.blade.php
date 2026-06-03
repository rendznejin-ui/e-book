<x-admin-layout>
    <x-slot name="title">Edit: {{ $book->title }}</x-slot>
    @include('admin.books._form', ['action' => route('admin.books.update', $book), 'method' => 'PUT'])
</x-admin-layout>
