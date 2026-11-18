<div>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Institutional User Directory') }}</h1>
                <p class="text-xs text-slate-500 mt-1">{{ __('Search, audit, suspend, reactivate, and manage user accounts across all campus roles (§4, §13)') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.teachers.invite') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-sm transition-all inline-flex items-center gap-1.5 btn-interactive">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM3 20a6 6 0 0 1 12 0v1H3v-1Z"/></svg>
                    {{ __('Invite Faculty') }}
                </a>
                <a href="{{ route('admin.approvals.parents') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-amber-900 bg-amber-100 hover:bg-amber-200 border border-amber-300 shadow-xs transition-colors inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                    {{ __('Parent Queue') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Toast / Session Alerts -->
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Quick Metrics Overview -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
            <x-card class="border-l-4 border-l-brand-600">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Total Users') }}</p>
                <p class="text-2xl font-black text-slate-900 mt-1">{{ $metrics['total'] }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Institution accounts') }}</p>
            </x-card>

            <x-card class="border-l-4 border-l-emerald-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Active Users') }}</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">{{ $metrics['active'] }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('In good standing') }}</p>
            </x-card>

            <x-card class="border-l-4 border-l-amber-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Pending Review') }}</p>
                <p class="text-2xl font-black text-amber-600 mt-1">{{ $metrics['pending'] }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Awaiting approval') }}</p>
            </x-card>

            <x-card class="border-l-4 border-l-rose-500">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Suspended') }}</p>
                <p class="text-2xl font-black text-rose-600 mt-1">{{ $metrics['suspended'] }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Access restricted') }}</p>
            </x-card>

            <x-card class="border-l-4 border-l-slate-400">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Deactivated') }}</p>
                <p class="text-2xl font-black text-slate-600 mt-1">{{ $metrics['deactivated'] }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Archived accounts') }}</p>
            </x-card>
        </div>

        <!-- Search and Filter Bar -->
        <x-card>
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                <!-- Search -->
                <div class="sm:col-span-5 relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    </span>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="{{ __('Search by name, email, phone number...') }}"
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all bg-white shadow-xs" />
                </div>

                <!-- Status Filter -->
                <div class="sm:col-span-3">
                    <select wire:model.live="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all bg-white shadow-xs">
                        <option value="all">{{ __('All Statuses') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="pending">{{ __('Pending Approval') }}</option>
                        <option value="suspended">{{ __('Suspended') }}</option>
                        <option value="rejected">{{ __('Rejected') }}</option>
                        <option value="deactivated">{{ __('Deactivated') }}</option>
                    </select>
                </div>

                <!-- Role Filter -->
                <div class="sm:col-span-3">
                    <select wire:model.live="role" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all bg-white shadow-xs">
                        <option value="all">{{ __('All Roles') }}</option>
                        @foreach ($rolesList as $roleItem)
                            <option value="{{ $roleItem->value }}">{{ $roleItem->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Reset -->
                <div class="sm:col-span-1 text-right">
                    @if ($search !== '' || $status !== 'all' || $role !== 'all')
                        <button type="button"
                                wire:click="$set('search', ''); $set('status', 'all'); $set('role', 'all');"
                                class="px-2.5 py-2 text-xs text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer"
                                title="{{ __('Reset Filters') }}">
                            {{ __('Reset') }}
                        </button>
                    @endif
                </div>
            </div>
        </x-card>

        <!-- Users Table -->
        <x-card>
            @if ($users->isEmpty())
                <div class="py-12 text-center space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center font-bold text-lg">
                        👥
                    </div>
                    <p class="text-sm font-bold text-slate-800">{{ __('No users found') }}</p>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">{{ __('Try adjusting your search criteria or filter options to locate users in this institution.') }}</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold text-[10px]">
                                <th class="py-3 px-3 cursor-pointer select-none hover:text-slate-900" wire:click="sortBy('name')">
                                    <div class="flex items-center gap-1">
                                        <span>{{ __('User / Contact') }}</span>
                                        @if ($sortField === 'name')
                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th class="py-3 px-3">{{ __('Roles') }}</th>
                                <th class="py-3 px-3 cursor-pointer select-none hover:text-slate-900" wire:click="sortBy('status')">
                                    <div class="flex items-center gap-1">
                                        <span>{{ __('Status') }}</span>
                                        @if ($sortField === 'status')
                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th class="py-3 px-3 cursor-pointer select-none hover:text-slate-900" wire:click="sortBy('last_login_at')">
                                    <div class="flex items-center gap-1">
                                        <span>{{ __('Last Login') }}</span>
                                        @if ($sortField === 'last_login_at')
                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th class="py-3 px-3 cursor-pointer select-none hover:text-slate-900" wire:click="sortBy('created_at')">
                                    <div class="flex items-center gap-1">
                                        <span>{{ __('Joined') }}</span>
                                        @if ($sortField === 'created_at')
                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th class="py-3 px-3 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($users as $user)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <!-- User info -->
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-800 font-bold flex items-center justify-center text-xs shrink-0">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                                    <span>{{ $user->name }}</span>
                                                    @if ($user->id === auth()->id())
                                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-brand-100 text-brand-700">
                                                            {{ __('You') }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-slate-500 font-mono">{{ $user->email }}</div>
                                                @if ($user->phone)
                                                    <div class="text-[10px] text-slate-400 font-mono">{{ $user->phone }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Roles -->
                                    <td class="py-3 px-3">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse ($user->roles as $role)
                                                @php
                                                    $roleBadgeColor = match ($role->name) {
                                                        'super_admin' => 'bg-purple-100 text-purple-800 border-purple-200',
                                                        'admin' => 'bg-brand-100 text-brand-800 border-brand-200',
                                                        'teacher' => 'bg-sky-100 text-sky-800 border-sky-200',
                                                        'parent' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                        'student' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                        'cashier' => 'bg-teal-100 text-teal-800 border-teal-200',
                                                        'printing_staff' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                                                        'attendance_marshal' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                                        default => 'bg-slate-100 text-slate-800 border-slate-200',
                                                    };
                                                @endphp
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $roleBadgeColor }}">
                                                    {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                                </span>
                                            @empty
                                                <span class="text-[11px] text-slate-400 italic">{{ __('No role assigned') }}</span>
                                            @endforelse
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="py-3 px-3">
                                        @php
                                            $statusBadge = match ($user->status) {
                                                'active' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                'suspended' => 'bg-rose-100 text-rose-800 border-rose-200',
                                                'rejected' => 'bg-rose-100 text-rose-800 border-rose-200',
                                                'deactivated' => 'bg-slate-100 text-slate-700 border-slate-200',
                                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge }}">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>

                                    <!-- Last Login -->
                                    <td class="py-3 px-3 text-slate-500 font-mono text-[11px]">
                                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : __('Never') }}
                                    </td>

                                    <!-- Joined -->
                                    <td class="py-3 px-3 text-slate-500 font-mono text-[11px]">
                                        {{ $user->created_at->format('M d, Y') }}
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-3 px-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5 whitespace-nowrap">
                                            @if ($user->id !== auth()->id() && (! $user->hasRole('super_admin') || auth()->user()?->hasRole('super_admin')))
                                                @if ($user->status === 'suspended')
                                                    <button type="button"
                                                            wire:click="reactivateUser({{ $user->id }})"
                                                            wire:confirm="{{ __('Are you sure you want to reactivate this user account?') }}"
                                                            class="px-2.5 py-1 rounded-lg text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 transition-colors shadow-xs cursor-pointer">
                                                        {{ __('Reactivate') }}
                                                    </button>
                                                @elseif ($user->status === 'active')
                                                    <button type="button"
                                                            wire:click="openSuspendModal({{ $user->id }})"
                                                            class="px-2.5 py-1 rounded-lg text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors cursor-pointer">
                                                        {{ __('Suspend') }}
                                                    </button>
                                                @endif

                                                @if ($user->status !== 'deactivated')
                                                    <button type="button"
                                                            wire:click="openDeactivateModal({{ $user->id }})"
                                                            class="px-2.5 py-1 rounded-lg text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors cursor-pointer"
                                                            title="{{ __('Deactivate account') }}">
                                                        {{ __('Deactivate') }}
                                                    </button>
                                                @else
                                                    <button type="button"
                                                            wire:click="reactivateUser({{ $user->id }})"
                                                            wire:confirm="{{ __('Reactivate this deactivated user?') }}"
                                                            class="px-2.5 py-1 rounded-lg text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors cursor-pointer">
                                                        {{ __('Reactivate') }}
                                                    </button>
                                                @endif
                                            @endif

                                            <a href="{{ route('admin.audit', ['search' => $user->email]) }}"
                                               class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                                               title="{{ __('View Audit Trail') }}">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pt-4 border-t border-slate-100">
                    {{ $users->links() }}
                </div>
            @endif
        </x-card>
    </div>

    <!-- Suspend User Modal -->
    @if ($showSuspendModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold">
                        ⚠️
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">{{ __('Suspend User Account') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('This will immediately revoke system access.') }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">{{ __('Reason for Suspension (Mandatory)') }}</label>
                    <textarea wire:model="suspensionReason"
                              rows="3"
                              class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"
                              placeholder="{{ __('Explain why this user account is being suspended (recorded in audit log)...') }}"></textarea>
                    @error('suspensionReason')
                        <p class="text-[11px] text-rose-600 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button"
                            wire:click="closeSuspendModal"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button"
                            wire:click="suspendUser"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 transition-all shadow-md shadow-rose-600/20 cursor-pointer">
                        {{ __('Confirm Suspension') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Deactivate User Modal -->
    @if ($showDeactivateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold">
                        🚫
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">{{ __('Deactivate User Account') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('The user will be archived and unable to sign in.') }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">{{ __('Deactivation Notes (Optional)') }}</label>
                    <textarea wire:model="deactivationReason"
                              rows="3"
                              class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-500"
                              placeholder="{{ __('Add any archival or departure notes...') }}"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button"
                            wire:click="closeDeactivateModal"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button"
                            wire:click="deactivateUser"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-800 hover:bg-slate-700 transition-all shadow-md cursor-pointer">
                        {{ __('Confirm Deactivation') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
