<div class="space-y-8">
    <div>
        <h2 class="text-2xl font-semibold text-gray-900 py-3 mb-4 border-b border-[#7a5466]">Account Settings</h2>

        <!-- Password -->
        <div class="flex items-end gap-4">
            <div class="flex-1">
                <x-ui.input label="Password" name="password_display" type="password" value="••••••••••••"
                    :error="$errors->first('password')" disabled />
            </div>
            <x-ui.button type="button" variant="secondary" class="text-base mb-5 text-gray-700 whitespace-nowrap"
                onclick="openPasswordModal()">
                Change password
            </x-ui.button>
        </div>
    </div>

    <div>
        <h2 class="text-2xl font-semibold text-gray-900 py-3 mb-4 border-b border-[#7a5466]">Privacy & Security</h2>

        <!-- Make account private -->
        <div class="flex items-center justify-between py-4 border-b border-gray-200">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Make account private</h3>
                <p class="text-base text-gray-500 mt-1">Only your friends can see your posts.</p>
            </div>
            <x-ui.toggle id="privacy-toggle" :checked="$user->isprivate" />
        </div>

        <!-- Log out of all devices -->
        <div class="flex items-center justify-between py-4 border-b border-gray-200">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Log out</h3>
                <p class="text-base text-gray-500 mt-1">Log out of this device.</p>
            </div>
            <x-ui.button href="{{ url('/logout') }}" type="button" variant="secondary"
                class="text-base text-gray-700 whitespace-nowrap">
                Log out
            </x-ui.button>
        </div>

        <!-- Delete my account -->
        <div class="flex items-center justify-between py-4">
            <div>
                <h3 class="text-lg font-medium text-red-700">Delete my account</h3>
                <p class="text-base text-gray-500 mt-1">Permanently delete the account.</p>
            </div>
            <x-ui.button type="button" variant="danger" class="text-base whitespace-nowrap" onclick="openDeleteAccountModal()">
                Delete Account
            </x-ui.button>
        </div>
    </div>
</div>

<div id="deleteAccountModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4">
        <h3 class="text-2xl font-semibold text-gray-900 mb-4">Delete Account</h3>
        <p class="text-gray-600 mb-4">This action cannot be undone. Enter your password to confirm deletion.</p>
        <input type="password" id="deleteAccountPassword" placeholder="Enter your password" class="w-full px-4 py-2 border border-gray-300 rounded mb-4">
        <div class="flex gap-4">
            <button type="button" onclick="closeDeleteAccountModal()" class="flex-1 px-4 py-2 bg-gray-300 text-gray-900 rounded hover:bg-gray-400">Cancel</button>
            <button type="button" onclick="confirmDeleteAccount()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
        </div>
    </div>
</div>

<script>
function openDeleteAccountModal() {
    document.getElementById('deleteAccountModal').classList.remove('hidden');
}

function closeDeleteAccountModal() {
    document.getElementById('deleteAccountModal').classList.add('hidden');
    document.getElementById('deleteAccountPassword').value = '';
}

function confirmDeleteAccount() {
    const password = document.getElementById('deleteAccountPassword').value;
    if (!password) {
        alert('Please enter your password');
        return;
    }

    fetch('{{ route("profile.delete-account") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ password })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect_url;
        } else {
            alert(data.message);
            closeDeleteAccountModal();
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
        closeDeleteAccountModal();
    });
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' && !document.getElementById('deleteAccountModal').classList.contains('hidden')) {
        closeDeleteAccountModal();
    }
});
</script>