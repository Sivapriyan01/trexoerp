<x-app-layout>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-bold mb-6">Super Admin Settings</h2>

                @if(session('success'))
                    <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('superadmin.settings.update') }}">
                    @csrf
                    
                    <div class="mb-6">
                        <label for="rapidapi_key" class="block text-sm font-medium text-gray-700 mb-2">GST API Keys (RapidAPI)</label>
                        <textarea name="rapidapi_key" id="rapidapi_key" rows="5"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter keys separated by commas or newlines">{{ old('rapidapi_key', str_replace(',', "\n", $rapidApiKey)) }}</textarea>
                        <p class="mt-2 text-sm text-gray-500">You can enter up to 20 keys, separated by commas or new lines. The system will randomly pick one for each API request to load balance.</p>
                    </div>

                    <div class="mb-6">
                        <label for="gst_return_target_months" class="block text-sm font-medium text-gray-700 mb-2">Filing Health Target (Months)</label>
                        <input type="number" name="gst_return_target_months" id="gst_return_target_months" min="1" max="24"
                               class="w-full sm:w-1/3 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="{{ old('gst_return_target_months', $gstReturnTargetMonths ?? 8) }}">
                        <p class="mt-2 text-sm text-gray-500">Number of months to compare against when calculating the filing health percentage (e.g. 8 or 12).</p>
                    </div>

                    <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="session_driver" class="block text-sm font-medium text-gray-700 mb-2">Session Driver</label>
                            <select name="session_driver" id="session_driver" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="database" {{ old('session_driver', $sessionDriver ?? '') === 'database' ? 'selected' : '' }}>Database</option>
                                <option value="file" {{ old('session_driver', $sessionDriver ?? '') === 'file' ? 'selected' : '' }}>File</option>
                                <option value="redis" {{ old('session_driver', $sessionDriver ?? '') === 'redis' ? 'selected' : '' }}>Redis</option>
                                <option value="cookie" {{ old('session_driver', $sessionDriver ?? '') === 'cookie' ? 'selected' : '' }}>Cookie</option>
                            </select>
                            <p class="mt-2 text-sm text-gray-500">Select where session data should be stored.</p>
                        </div>
                        <div>
                            <label for="session_lifetime" class="block text-sm font-medium text-gray-700 mb-2">Session Lifetime (Minutes)</label>
                            <input type="number" name="session_lifetime" id="session_lifetime" min="1"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   value="{{ old('session_lifetime', $sessionLifetime ?? 120) }}">
                            <p class="mt-2 text-sm text-gray-500">Number of minutes before a session expires.</p>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
