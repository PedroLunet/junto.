@extends('layouts.app')

@section('title', 'Main Features')

@section('content')
    <div class="container mx-auto min-h-screen flex items-center justify-center">
        <div class="grid grid-cols-1 md:grid-cols-4 grid-rows-3 gap-6 bento-grid">
            <!-- Box 1: Large vertical -->
            <div
                class="bg-purple-100 rounded-2xl p-20 row-span-2 col-span-2 flex items-center justify-center text-2xl font-bold shadow">
                box 1</div>
            <!-- Box 2: Top right -->
            <div class="bg-pink-100 rounded-2xl p-20 col-span-2 flex items-center justify-center text-2xl font-bold shadow">
                box 2</div>
            <!-- Box 3: Middle right -->
            <div class="bg-yellow-100 rounded-2xl p-20 flex items-center justify-center text-2xl font-bold shadow">box 3</div>
            <!-- Box 4: Middle far right -->
            <div class="bg-green-100 rounded-2xl p-20 flex items-center justify-center text-2xl font-bold shadow">box 4</div>
            <!-- Box 5: Bottom left -->
            <div class="bg-orange-100 rounded-2xl p-20 col-span-2 flex items-center justify-center text-2xl font-bold shadow">
                box 5</div>
            <!-- Box 6: Bottom right -->
            <div class="bg-blue-100 rounded-2xl p-20 col-span-2 flex items-center justify-center text-2xl font-bold shadow">
                box 6</div>
        </div>
    </div>
@endsection
