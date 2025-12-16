<div class="space-y-6">
    <!-- Email -->
    <div class="flex items-end gap-4">
        <div class="flex-1">
            <x-ui.input label="Email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}"
                :error="$errors->first('email')" disabled />
        </div>
        <x-ui.button type="button" variant="secondary" class="text-2xl mb-7 text-gray-700 whitespace-nowrap">
            Change email
        </x-ui.button>
    </div>

    <!-- Password -->
    <div class="flex items-end gap-4">
        <div class="flex-1">
            <x-ui.input label="Password" name="password_display" type="password" value="••••••••••••" :error="$errors->first('password')"
                disabled />
        </div>
        <x-ui.button type="button" variant="secondary" class="text-2xl mb-7 text-gray-700 whitespace-nowrap">
            Change password
        </x-ui.button>
    </div>
</div>
