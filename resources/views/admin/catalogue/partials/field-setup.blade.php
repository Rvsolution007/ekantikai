<!-- Field Setup Tab Content -->
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-white">Configure Catalogue Fields</h2>
            <p class="text-gray-400 text-sm mt-1">Define the fields for your product catalogue. These fields will be used when adding products manually or importing from Excel.</p>
        </div>
        <button @click="$dispatch('open-add-field')" class="btn-primary px-6 py-3 rounded-xl text-white font-medium flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span>Add Field</span>
        </button>
    </div>

    @if($fields->isEmpty())
        <!-- Empty State -->
        <div class="glass rounded-2xl p-12 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
            <h3 class="text-lg font-medium text-white mb-2">No Fields Configured</h3>
            <p class="text-gray-400 mb-6">Start by adding fields like Product Name, Model Number, Size, Finish, etc.</p>
            <button @click="$dispatch('open-add-field')" class="btn-primary inline-block px-6 py-3 rounded-xl text-white font-medium">
                Add Your First Field
            </button>
        </div>
    @else
        <!-- Fields List -->
        <div class="glass rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-dark-300/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Order</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Field Name</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Key</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Unique</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        @foreach($fields as $index => $field)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2 text-gray-400">
                                        <svg class="w-5 h-5 cursor-move" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                        </svg>
                                        <span>{{ $index + 1 }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-white font-medium">{{ $field->field_name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <code class="text-sm text-gray-400 bg-dark-300 px-2 py-1 rounded">{{ $field->field_key }}</code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 text-xs rounded-lg 
                                        @if($field->field_type === 'text') bg-blue-500/20 text-blue-400
                                        @elseif($field->field_type === 'number') bg-purple-500/20 text-purple-400
                                        @else bg-orange-500/20 text-orange-400 @endif">
                                        {{ ucfirst($field->field_type) }}
                                        @if($field->field_type === 'select' && $field->options)
                                            ({{ count($field->options) }} options)
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($field->is_unique)
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-yellow-500/20 text-yellow-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-500/20 text-gray-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button @click="$dispatch('open-edit-field', {{ json_encode($field) }})" class="p-2 rounded-lg bg-blue-500/20 text-blue-400 hover:bg-blue-500/30 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <form action="{{ route('admin.catalogue-fields.destroy', $field) }}" method="POST" class="inline" onsubmit="return confirm('Delete this field? This may affect existing data!')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Field Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="glass rounded-xl p-6">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                    </div>
                    <h4 class="text-white font-medium">Text Fields</h4>
                </div>
                <p class="text-gray-400 text-sm">For names, descriptions, notes, and other text content.</p>
            </div>
            <div class="glass rounded-xl p-6">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                        </svg>
                    </div>
                    <h4 class="text-white font-medium">Number Fields</h4>
                </div>
                <p class="text-gray-400 text-sm">For quantities, prices, measurements, and numeric values.</p>
            </div>
            <div class="glass rounded-xl p-6">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 rounded-lg bg-orange-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <h4 class="text-white font-medium">Dropdown Fields</h4>
                </div>
                <p class="text-gray-400 text-sm">For predefined options like categories, status, finishes, etc.</p>
            </div>
        </div>
    @endif
</div>
