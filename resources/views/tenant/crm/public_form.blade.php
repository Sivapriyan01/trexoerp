<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $form->title ?? 'Submit Lead' }} | {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: {{ $form->settings['backgroundColor'] ?? '#f8fafc' }};
            color: {{ $form->settings['textColor'] ?? '#0f172a' }};
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.05);
        }
        .primary-btn {
            background-color: {{ $form->settings['primaryColor'] ?? '#4f46e5' }};
            color: white;
            transition: all 0.3s ease;
        }
        .primary-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px {{ ($form->settings['primaryColor'] ?? '#4f46e5') }}80;
        }
        .input-field {
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        .input-field:focus {
            background: white;
            border-color: {{ $form->settings['primaryColor'] ?? '#4f46e5' }};
            ring: 4px solid {{ ($form->settings['primaryColor'] ?? '#4f46e5') }}10;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">
    <div x-data="leadForm" class="w-full max-w-xl">
        <!-- Success State -->
        <div x-show="submitted" x-transition class="glass-card rounded-[3rem] p-12 text-center space-y-8">
            <div class="w-24 h-24 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="space-y-4">
                <h1 class="text-3xl font-black uppercase tracking-tight">Success!</h1>
                <p class="text-slate-500 font-bold uppercase tracking-widest text-xs" x-text="successMessage"></p>
            </div>
            <button @click="submitted = false; formData = {}" class="primary-btn px-10 py-4 rounded-2xl text-xs font-black uppercase tracking-widest">Submit Another</button>
        </div>

        <!-- Form State -->
        <form x-show="!submitted" x-transition @submit.prevent="submitForm" class="glass-card rounded-[3rem] overflow-hidden">
            <!-- Form Header -->
            <div class="p-12 pb-6 space-y-4">
                <div class="flex items-center gap-4 mb-8">
                    @if(isset($form->settings['logoUrl']))
                        <img src="{{ $form->settings['logoUrl'] }}" class="h-12 object-contain">
                    @else
                        <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-200">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-2xl font-black uppercase tracking-tight">{{ $form->title ?? 'New Lead Inquiry' }}</h1>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $form->workflow->name }}</p>
                    </div>
                </div>
                
                @if($form->description)
                    <p class="text-sm font-bold text-slate-500 leading-relaxed uppercase tracking-tight border-l-4 border-blue-600 pl-4 py-1">{{ $form->description }}</p>
                @endif
            </div>

            <!-- Form Fields -->
            <div class="px-12 pb-12 space-y-8">
                <div class="grid grid-cols-1 gap-6">
                    @foreach($form->fields as $fieldName => $config)
                        @if(!empty($config['enabled']))
                            @php 
                                $workflowField = $form->workflow->fields->where('name', $fieldName)->first() 
                                                ?? $form->workflow->fields->where('label', $fieldName)->first();
                                if (!$workflowField) continue;
                            @endphp
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">
                                    {{ $workflowField->label }}
                                    @if(!empty($config['required'])) <span class="text-rose-500">*</span> @endif
                                </label>
                                
                                @if($workflowField->type == 'textarea')
                                    <textarea x-model="formData.{{ $fieldName }}" 
                                              rows="3" 
                                              placeholder="Enter {{ strtolower($workflowField->label) }}..."
                                              class="w-full input-field rounded-2xl px-6 py-4 text-sm font-bold outline-none resize-none"
                                              @if(!empty($config['required'])) required @endif></textarea>
                                @elseif($workflowField->type == 'select')
                                    <select x-model="formData.{{ $fieldName }}" 
                                            class="w-full input-field rounded-2xl px-6 py-4 text-sm font-bold outline-none appearance-none"
                                            @if(!empty($config['required'])) required @endif>
                                        <option value="">Select {{ $workflowField->label }}</option>
                                        @foreach($workflowField->options ?? [] as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="{{ $workflowField->type == 'number' ? 'number' : ($workflowField->type == 'date' ? 'date' : 'text') }}" 
                                           x-model="formData.{{ $fieldName }}" 
                                           placeholder="Enter {{ strtolower($workflowField->label) }}..."
                                           class="w-full input-field rounded-2xl px-6 py-4 text-sm font-bold outline-none"
                                           @if(!empty($config['required'])) required @endif>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="pt-4">
                    <button type="submit" 
                            :disabled="loading"
                            class="w-full primary-btn py-5 rounded-[1.5rem] text-sm font-black uppercase tracking-widest shadow-xl flex items-center justify-center gap-3">
                        <template x-if="!loading">
                            <span>{{ $form->settings['submitButtonName'] ?? 'Submit Application' }}</span>
                        </template>
                        <template x-if="loading">
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>Processing...</span>
                            </div>
                        </template>
                    </button>
                    <p class="text-center text-[9px] font-black text-slate-400 uppercase tracking-widest mt-6">Secure Form Powered by TrexoERP CRM</p>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('leadForm', () => ({
                formData: {},
                loading: false,
                submitted: false,
                successMessage: '',
                
                async submitForm() {
                    this.loading = true;
                    try {
                        const response = await fetch('{{ route('crm.public.submit', $form->slug) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(this.formData)
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            this.submitted = true;
                            this.successMessage = result.message;
                        } else {
                            alert(result.message || 'Something went wrong. Please try again.');
                        }
                    } catch (error) {
                        console.error('Submission error:', error);
                        alert('Connection error. Please check your internet and try again.');
                    } finally {
                        this.loading = false;
                    }
                }
            }));
        });
    </script>
</body>
</html>

