@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center mb-20 gap-10">
            <a href="{{ route('profile.show', $user->username) }}" class="mr-4 text-gray-600 hover:text-gray-800 p-3">
                <i class="fas fa-arrow-left text-3xl"></i>
            </a>
            <div>
                <h1 class="text-4xl font-bold text-gray-900">Edit Profile</h1>
            </div>
        </div>

        <x-ui.tabs :tabs="[
            'details' => [
                'title' => 'Details',
                'content' => view('components.profile.edit-details-tab', ['user' => $user])->render(),
            ],
            'security' => [
                'title' => 'Security',
                'content' => view('components.profile.edit-security-tab', ['user' => $user])->render(),
            ],
        ]" />
    </div>
@endsection
