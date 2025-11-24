@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto space-y-6">
            @foreach($posts as $post)
                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200"
                    onclick="openPostModal({{ json_encode($post) }})">
                    <!-- author info -->
                    <div class="flex items-center mb-3">
                        <div class="font-semibold text-gray-900">{{ $post->author_name }}</div>
                        <div class="text-gray-500 text-base ml-2">@ {{$post->username}}</div>
                    </div>

                    <!-- review data -->
                    @if($post->rating)
                        <div class="mb-3 flex items-center">
                            <span class="text-yellow-500 font-medium">⭐ {{ $post->rating }}/5</span>
                            <span class="text-gray-600 ml-2">for {{ $post->media_title }}</span>
                        </div>
                    @endif

                    <!-- post content -->
                    <div class="text-gray-800 leading-relaxed">
                        {{ $post->content }}
                    </div>

                    <!-- post image -->
                    @if($post->image_url)
                        <div class="mt-4">
                            <img src="{{ asset('storage/' . $post->image_url) }}" alt="image"
                                class="w-full max-w-md rounded-lg shadow-sm border border-gray-200 mx-auto">
                        </div>
                    @endif

                </div>
            @endforeach
        </div>
    </div>
    <x-post-modal />
@endsection