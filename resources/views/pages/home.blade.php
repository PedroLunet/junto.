@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto space-y-6">
            @foreach($posts as $post)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 cursor-pointer"
                    onclick="openPostModal({{ json_encode($post) }})">

                    <!-- edit button (vai ser mudado pro modal) -->
                    @if(auth()->check() && $post->username === auth()->user()->username)
                        <div class="absolute top-3 right-3 z-10">
                            @if($post->post_type === 'review')
                                <button
                                    onclick="event.stopPropagation(); openEditReviewModal({{ $post->id }}, '{{ addslashes($post->content) }}', {{ $post->rating }}, '{{ addslashes($post->media_title) }}', '{{ $post->media_poster }}', '{{ $post->media_year }}', '{{ addslashes($post->media_creator) }}')"
                                    class="text-gray-500 hover:text-gray-700 p-1">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @else
                                <button
                                    onclick="event.stopPropagation(); openEditModal({{ $post->id }}, '{{ addslashes($post->content) }}', '{{ $post->image_url ? asset('storage/' . $post->image_url) : '' }}')"
                                    class="text-gray-500 hover:text-gray-700 p-1">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @endif
                        </div>
                    @endif


                    <!-- profile + name -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <!-- provisorio antes de ter foto -->
                            <div class="w-12 h-12 bg-gray-200 rounded-full"></div>

                            <div class="flex flex-col leading-tight">
                                <span class="font-semibold text-gray-900">
                                    {{ $post->author_name }}
                                </span>
                                <span class="text-gray-500 text-base">
                                    @<span>{{$post->username}}</span>
                                </span>
                            </div>
                        </div>

                        <!-- timestamp -->
                        <div class="text-base text-gray-500">
                            {{ \Carbon\Carbon::parse($post->created_at)->format('H:i') }} <br>
                            {{ \Carbon\Carbon::parse($post->created_at)->format('d/m/Y') }}
                        </div>
                    </div>


                    <!-- image -->
                    @if($post->image_url)
                        <div class="w-full bg-gray-200 rounded-xl overflow-hidden mb-4">
                            <img src="{{ asset('storage/' . $post->image_url) }}" class="w-full h-auto object-cover">
                        </div>
                    @endif


                    <!-- text -->
                    <p class="text-black">
                        {{ $post->content }}
                    </p>


                    <!-- interactions -->
                    <div class="flex justify-end items-center gap-4 mt-4 text-gray-600">

                        <!-- likes -->
                        <div class="flex items-center gap-1">
                            <span class="text-lg">{{ $post->likes_count ?? 0 }}</span>
                            <i class="far fa-heart text-lg"></i>
                        </div>

                        <!-- comments -->
                        <div class="flex items-center gap-1">
                            <span class="text-lg">{{ $post->comments_count ?? 0 }}</span>
                            <i class="far fa-comment text-lg"></i>
                        </div>
                    </div>

                </div>

            @endforeach
        </div>
    </div>

    <x-post-modal />
    <x-edit-regular-modal />
    <x-edit-review-modal />

    @yield('modal-overlay')
@endsection