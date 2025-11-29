<div id="reportModal" class="fixed inset-0 z-50 hidden" onclick="closeReportModal()">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-6">
            <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:w-full sm:max-w-2xl p-6"
                onclick="event.stopPropagation()">

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-4xl font-bold text-gray-900">Report Post</h2>
                    <button onclick="closeReportModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <p class="text-gray-600 mb-4 font-medium">Please provide a reason for reporting this post. Our team will
                    review it.</p>

                <textarea id="reportReason" placeholder="Describe why you're reporting this post (minimum 10 characters)..."
                    class="w-full min-h-[120px] p-3 border border-gray-300 rounded-lg focus:border-[#38157a] focus:ring-[#38157a] mb-6 resize-none"
                    maxlength="1000"></textarea>

                <div class="flex justify-end gap-3">
                    <x-ui.button onclick="closeReportModal()" variant="secondary">
                        Cancel
                        </x-button>
                        <x-ui.button onclick="submitReport()" variant="danger">
                            Submit Report
                            </x-button>
                </div>
            </div>
        </div>
    </div>
</div>
