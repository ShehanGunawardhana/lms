<div class="max-w-7xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 pb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Child Safety, Health & Pickups') }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ __('Manage encrypted health records, emergency contacts, and verified pickup permissions (§6.5)') }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('parent.dashboard') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-xs transition-colors inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Parent Dashboard') }}
            </a>
            <a href="{{ route('parent.activities') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-xs transition-colors inline-flex items-center gap-1.5">
                {{ __('Learning Activities') }}
            </a>
        </div>
    </div>

    <!-- Feedback messages -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-900 flex items-center justify-between animate-fade-in shadow-xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if ($children->isEmpty())
        <div class="p-12 text-center bg-white rounded-3xl border border-slate-200 shadow-xs space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-900">{{ __('No Registered Children Found') }}</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto">
                {{ __('There are no active child records linked to your parent account. If you just registered, your application may still be in the verification queue.') }}
            </p>
        </div>
    @else
        <!-- Multi-Child Switcher -->
        @if ($children->count() > 1)
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-1">{{ __('Select Child:') }}</span>
                @foreach ($children as $c)
                    <button 
                        type="button" 
                        wire:click="selectStudent({{ $c->id }})"
                        class="px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-2 {{ $selectedStudentId === $c->id ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
                        <span>{{ $c->full_name }}</span>
                        <span class="text-[10px] opacity-75 font-mono">({{ $c->student_code }})</span>
                    </button>
                @endforeach
            </div>
        @endif

        @if ($currentStudent)
            <!-- Child Overview Banner -->
            <div class="p-5 rounded-3xl bg-gradient-to-r from-slate-900 to-slate-800 text-white shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-xs flex items-center justify-center font-black text-xl text-brand-300 border border-white/15 shrink-0">
                        {{ strtoupper(substr($currentStudent->first_name, 0, 1)) }}{{ strtoupper(substr($currentStudent->last_name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-lg font-black tracking-tight">{{ $currentStudent->full_name }}</h2>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-white/15 text-slate-200">
                                {{ $currentStudent->student_code }}
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 mt-1 text-xs text-slate-300">
                            <span>🎂 {{ format_portal_datetime($currentStudent->dob, 'Y-m-d') }} ({{ $currentStudent->age_years }} {{ __('years old') }})</span>
                            @if ($currentStudent->classes->isNotEmpty())
                                <span>🏫 {{ $currentStudent->classes->pluck('title')->join(', ') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div class="px-3.5 py-1.5 rounded-xl bg-white/10 border border-white/15 text-[11px] font-semibold text-emerald-300 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                        <span>{{ __('AES-256 Encrypted at Rest (§6.5)') }}</span>
                    </div>
                </div>
            </div>

            <!-- Two-Column Grid: Medical & Emergency Contacts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Card 1: Medical & Allergies -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-sm">🩺</span>
                            <h3 class="text-sm font-black text-slate-900">{{ __('Medical & Dietary Records') }}</h3>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                            {{ __('Restricted Access') }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Blood Group') }}</label>
                            <select wire:model="bloodGroup" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium">
                                <option value="">{{ __('Select Blood Group...') }}</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                            @error('bloodGroup') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Known Allergies (Encrypted)') }}</label>
                            <input 
                                type="text" 
                                wire:model="allergies" 
                                placeholder="{{ __('e.g. Peanuts, Dairy, Penicillin') }}"
                                class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium" 
                            />
                            @error('allergies') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Medical Conditions & Special Care Notes (Encrypted)') }}</label>
                        <textarea 
                            wire:model="medicalNotes" 
                            rows="3" 
                            placeholder="{{ __('Include asthma inhaler instructions, dietary intolerances, recurring fever, or medication schedules...') }}"
                            class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium"></textarea>
                        @error('medicalNotes') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Emergency Physician / Hospital Instructions') }}</label>
                        <textarea 
                            wire:model="emergencyNotes" 
                            rows="2" 
                            placeholder="{{ __('e.g. Family doctor: Dr. Silva (+94 11 234 5678), Preferred clinic: Asiri Central') }}"
                            class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium"></textarea>
                        @error('emergencyNotes') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button 
                            type="button" 
                            wire:click="saveMedicalNotes" 
                            class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 cursor-pointer shadow-xs inline-flex items-center gap-1.5 btn-interactive">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('Save Health Information') }}
                        </button>
                    </div>
                </div>

                <!-- Card 2: Emergency Contacts -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">📞</span>
                            <h3 class="text-sm font-black text-slate-900">{{ __('Emergency Contacts') }}</h3>
                        </div>
                        <span class="text-[11px] text-slate-400 font-semibold">{{ $currentStudent->emergencyContacts->count() }} {{ __('registered') }}</span>
                    </div>

                    <!-- Existing Contacts List -->
                    <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                        @forelse ($currentStudent->emergencyContacts as $contact)
                            <div class="p-3 rounded-2xl border border-slate-200 bg-slate-50/70 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="w-7 h-7 rounded-xl font-black text-xs flex items-center justify-center {{ $contact->priority === 1 ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-slate-200 text-slate-700' }}">
                                        P{{ $contact->priority }}
                                    </span>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900 text-xs">{{ $contact->name }}</span>
                                            <span class="text-[10px] font-semibold text-slate-500 uppercase px-1.5 py-0.5 rounded bg-white border border-slate-200">
                                                {{ $contact->relationship }}
                                            </span>
                                            @if ($contact->isPrimary())
                                                <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded">{{ __('Primary') }}</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-600 mt-0.5 flex flex-wrap items-center gap-2">
                                            <span>📱 {{ $contact->phone_primary }}</span>
                                            @if ($contact->phone_secondary)
                                                <span class="text-slate-400">Alt: {{ $contact->phone_secondary }}</span>
                                            @endif
                                            @if ($contact->notes)
                                                <span class="text-slate-500 italic">({{ $contact->notes }})</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    wire:click="deleteEmergencyContact({{ $contact->id }})"
                                    wire:confirm="{{ __('Remove this emergency contact?') }}"
                                    class="text-xs text-rose-600 hover:text-rose-800 font-bold p-1 cursor-pointer">
                                    &times;
                                </button>
                            </div>
                        @empty
                            <div class="p-4 rounded-xl border border-dashed border-slate-200 text-center text-xs text-slate-400">
                                {{ __('No emergency contacts added yet. Please add at least one primary contact.') }}
                            </div>
                        @endforelse
                    </div>

                    <!-- Add Contact Mini-Form -->
                    <div class="p-4 rounded-2xl bg-slate-50/60 border border-slate-200/80 space-y-3">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">{{ __('Add New Contact') }}</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-0.5">{{ __('Full Name') }} *</label>
                                <input type="text" wire:model="contactName" class="w-full px-2.5 py-1.5 rounded-lg text-xs border border-slate-200 bg-white" placeholder="{{ __('Name') }}" />
                                @error('contactName') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-0.5">{{ __('Relationship') }} *</label>
                                <input type="text" wire:model="contactRelationship" class="w-full px-2.5 py-1.5 rounded-lg text-xs border border-slate-200 bg-white" placeholder="{{ __('e.g. Aunt, Father') }}" />
                                @error('contactRelationship') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-0.5">{{ __('Priority') }} *</label>
                                <select wire:model="contactPriority" class="w-full px-2.5 py-1.5 rounded-lg text-xs border border-slate-200 bg-white font-medium">
                                    <option value="1">1 (Primary)</option>
                                    <option value="2">2 (Secondary)</option>
                                    <option value="3">3 (Backup)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-0.5">{{ __('Phone Number') }} *</label>
                                <input type="text" wire:model="contactPhonePrimary" class="w-full px-2.5 py-1.5 rounded-lg text-xs border border-slate-200 bg-white" placeholder="{{ __('+94 77 123 4567') }}" />
                                @error('contactPhonePrimary') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-0.5">{{ __('Alt Phone / Notes') }}</label>
                                <input type="text" wire:model="contactNotes" class="w-full px-2.5 py-1.5 rounded-lg text-xs border border-slate-200 bg-white" placeholder="{{ __('e.g. Works near school') }}" />
                            </div>
                        </div>

                        <div class="flex justify-end pt-1">
                            <button 
                                type="button" 
                                wire:click="addEmergencyContact"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 cursor-pointer shadow-xs">
                                + {{ __('Add Emergency Contact') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Full-Width Card: Authorized Pickups (§6.5) -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-base">🚗</span>
                        <div>
                            <h3 class="text-base font-black text-slate-900">{{ __('Authorized Pickup Persons (§6.5)') }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ __('Individuals allowed to collect your child from the classroom. Teachers verify photo, NIC, and authorization status.') }}
                            </p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                        {{ $currentStudent->authorizedPickups->count() }} {{ __('registered pickups') }}
                    </span>
                </div>

                <!-- Pickup Persons Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse ($currentStudent->authorizedPickups as $pickup)
                        <div class="p-4 rounded-2xl border {{ $pickup->is_active ? 'border-slate-200 bg-white' : 'border-slate-200 bg-slate-50/70 opacity-60' }} shadow-xs flex flex-col justify-between space-y-3">
                            <div class="flex items-start gap-3">
                                @if ($pickup->photoUrl())
                                    <img src="{{ $pickup->photoUrl() }}" alt="{{ $pickup->name }}" class="w-12 h-12 rounded-2xl object-cover border border-slate-200 shrink-0" />
                                @else
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-700 font-bold flex items-center justify-center text-sm shrink-0 border border-indigo-100">
                                        {{ strtoupper(substr($pickup->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5">
                                        <h4 class="font-bold text-slate-900 text-xs truncate">{{ $pickup->name }}</h4>
                                        <span class="text-[10px] font-semibold text-slate-500 uppercase px-1.5 py-0.2 rounded bg-slate-100 shrink-0">
                                            {{ $pickup->relationship }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-600 mt-1 space-y-0.5">
                                        <p>📱 {{ $pickup->phone }}</p>
                                        @if ($pickup->national_id)
                                            <p class="font-mono text-[10px] text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded inline-block">NIC: {{ $pickup->national_id }}</p>
                                        @endif
                                        @if ($pickup->notes)
                                            <p class="text-slate-400 italic text-[10px]">{{ $pickup->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                <div>
                                    @if ($pickup->is_active)
                                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            {{ __('Active Pickup') }}
                                        </span>
                                    @else
                                        <span class="text-[10px] font-bold text-slate-500 bg-slate-200 px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            {{ __('Suspended') }}
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <button 
                                        type="button" 
                                        wire:click="togglePickupActive({{ $pickup->id }})"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-colors cursor-pointer {{ $pickup->is_active ? 'text-amber-800 bg-amber-50 hover:bg-amber-100' : 'text-emerald-800 bg-emerald-50 hover:bg-emerald-100' }}">
                                        {{ $pickup->is_active ? __('Suspend') : __('Activate') }}
                                    </button>
                                    <button 
                                        type="button" 
                                        wire:click="deleteAuthorizedPickup({{ $pickup->id }})"
                                        wire:confirm="{{ __('Remove this authorized pickup person?') }}"
                                        class="p-1 rounded-lg text-slate-300 hover:text-rose-600 font-bold transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full p-6 rounded-2xl border border-dashed border-slate-200 text-center text-xs text-slate-400">
                            {{ __('No authorized pickup persons recorded yet. By default, parents with approved accounts may pick up their child.') }}
                        </div>
                    @endforelse
                </div>

                <!-- Form to Add Authorized Pickup Person -->
                <div class="p-5 rounded-2xl bg-slate-50/70 border border-slate-200 space-y-4">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                        <span>+ {{ __('Add Authorized Pickup Person') }}</span>
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">{{ __('Full Name') }} *</label>
                            <input type="text" wire:model="pickupName" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 bg-white" placeholder="{{ __('Person\'s name') }}" />
                            @error('pickupName') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">{{ __('Relationship to Child') }} *</label>
                            <input type="text" wire:model="pickupRelationship" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 bg-white" placeholder="{{ __('e.g. Grandparent, Nanny, Driver') }}" />
                            @error('pickupRelationship') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">{{ __('Phone Number') }} *</label>
                            <input type="text" wire:model="pickupPhone" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 bg-white" placeholder="{{ __('+94 77 123 4567') }}" />
                            @error('pickupPhone') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">{{ __('National ID / NIC') }}</label>
                            <input type="text" wire:model="pickupNationalId" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 bg-white" placeholder="{{ __('e.g. 198512345678') }}" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">{{ __('Notes & Verification Instructions') }}</label>
                            <input type="text" wire:model="pickupNotes" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 bg-white" placeholder="{{ __('e.g. Authorized on Tuesdays and Thursdays after daycare') }}" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">{{ __('Photo Identification (Optional)') }}</label>
                            <input type="file" wire:model="pickupPhoto" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer" />
                            @error('pickupPhoto') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button 
                            type="button" 
                            wire:click="addAuthorizedPickup"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 cursor-pointer shadow-xs inline-flex items-center gap-1.5 btn-interactive">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            {{ __('Save Authorized Pickup Person') }}
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
