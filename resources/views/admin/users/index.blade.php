<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-200 leading-tight">
            Kelola Pengguna
        </h2>
    </x-slot>

    <div class="pb-12" x-data="userManagement()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('status'))
                <div class="mb-4 bg-green-500/20 border border-green-500 text-green-300 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-500/20 border border-red-500 text-red-300 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Filter Section -->
            <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden shadow-sm sm:rounded-lg border border-slate-700/50 mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="flex-1 w-full">
                            <x-input-label for="search" value="Cari Pengguna/Email/Keluarga" class="text-slate-300" />
                            <x-text-input id="search" name="search" type="text" class="block mt-1 w-full bg-slate-900 border-slate-600 text-white focus:border-blue-500 focus:ring-blue-500" value="{{ request('search') }}" placeholder="Ketik kata kunci..." />
                        </div>
                        <div class="w-full md:w-48">
                            <x-input-label for="role" value="Filter Role" class="text-slate-300" />
                            <select id="role" name="role" class="block mt-1 w-full rounded-md shadow-sm bg-slate-900 border-slate-600 text-white focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua Role</option>
                                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="owner" {{ request('role') == 'owner' ? 'selected' : '' }}>Owner</option>
                                <option value="member" {{ request('role') == 'member' ? 'selected' : '' }}>Member</option>
                            </select>
                        </div>
                        <div class="w-full md:w-auto flex gap-2">
                            <x-primary-button class="bg-blue-600 hover:bg-blue-500 whitespace-nowrap">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                Filter
                            </x-primary-button>
                            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-600 active:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-slate-800/50 backdrop-blur-md overflow-hidden shadow-sm sm:rounded-lg border border-slate-700/50">
                <div class="p-6 text-slate-200">
                    <!-- Desktop Table View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-300">
                            <thead class="text-xs text-slate-400 uppercase bg-slate-900/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Pengguna</th>
                                    <th scope="col" class="px-6 py-3">Role</th>
                                    <th scope="col" class="px-6 py-3">Keluarga</th>
                                    <th scope="col" class="px-6 py-3">Verifikasi</th>
                                    <th scope="col" class="px-6 py-3">Login Terakhir</th>
                                    <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr class="border-b border-slate-700/50 hover:bg-slate-700/30">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-white">{{ $user->name }}</div>
                                            <div class="text-xs text-slate-400">{{ $user->email }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $user->role === 'admin' ? 'bg-purple-900/50 text-purple-300 border border-purple-700/50' : 
                                                  ($user->role === 'owner' ? 'bg-blue-900/50 text-blue-300 border border-blue-700/50' : 
                                                  'bg-slate-700/50 text-slate-300 border border-slate-600') }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $user->family ? $user->family->name : '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($user->email_verified_at)
                                                <svg class="w-5 h-5 text-green-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Terverifikasi"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            @else
                                                <svg class="w-5 h-5 text-red-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Belum Terverifikasi"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-xs">
                                            {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : 'Belum Pernah' }}
                                        </td>
                                        <td class="px-6 py-4 text-right flex justify-end gap-2">
                                            <!-- Edit Icon -->
                                            <button @click="openEditModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ $user->role }}', {{ $user->allow_ai_receipt ? 'true' : 'false' }})" class="p-1 text-blue-400 hover:text-blue-300 hover:bg-blue-900/30 rounded transition-colors" title="Edit Pengguna">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </button>

                                            <!-- Reset Password Icon -->
                                            <button @click="openPasswordModal({{ $user->id }}, '{{ addslashes($user->name) }}')" class="p-1 text-yellow-400 hover:text-yellow-300 hover:bg-yellow-900/30 rounded transition-colors" title="Reset Password">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                            </button>
                                            
                                            <!-- Delete Icon -->
                                            @if(auth()->id() !== $user->id)
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="confirmDelete(event, this)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 text-red-400 hover:text-red-300 hover:bg-red-900/30 rounded transition-colors" title="Hapus Pengguna">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                            @else
                                                <div class="w-7"></div> <!-- Spacer for alignment -->
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                            Tidak ada pengguna yang ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="grid grid-cols-1 gap-4 md:hidden">
                        @forelse($users as $user)
                            <div class="bg-slate-700/30 rounded-xl p-4 border border-slate-600/50 flex flex-col gap-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-bold text-white text-base flex items-center gap-2">
                                            {{ $user->name }}
                                            @if($user->email_verified_at)
                                                <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            @endif
                                        </h3>
                                        <p class="text-sm text-slate-400">{{ $user->email }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $user->role === 'admin' ? 'bg-purple-900/50 text-purple-300 border border-purple-700/50' : 
                                          ($user->role === 'owner' ? 'bg-blue-900/50 text-blue-300 border border-blue-700/50' : 
                                          'bg-slate-700/50 text-slate-300 border border-slate-600') }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <span class="text-slate-500 text-xs block">Keluarga</span>
                                        <span class="text-slate-200">{{ $user->family ? $user->family->name : '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-500 text-xs block">Login Terakhir</span>
                                        <span class="text-slate-200 text-xs">{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : 'Belum Pernah' }}</span>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-2 pt-3 mt-1 border-t border-slate-600/50">
                                    <button @click="openEditModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ $user->role }}', {{ $user->allow_ai_receipt ? 'true' : 'false' }})" class="flex items-center gap-1 px-3 py-1.5 text-sm bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        Edit
                                    </button>
                                    
                                    <button @click="openPasswordModal({{ $user->id }}, '{{ addslashes($user->name) }}')" class="flex items-center gap-1 px-3 py-1.5 text-sm bg-yellow-600 hover:bg-yellow-500 text-white rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                        Reset
                                    </button>

                                    @if(auth()->id() !== $user->id)
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="confirmDelete(event, this)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="flex items-center gap-1 px-3 py-1.5 text-sm bg-red-600 hover:bg-red-500 text-white rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Hapus
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-slate-400 bg-slate-700/30 rounded-xl border border-slate-600/50">
                                Tidak ada pengguna yang ditemukan.
                            </div>
                        @endforelse
                    </div>
                    
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div x-show="editModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="editModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="editModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-700">
                    <form :action="`/admin/users/${editData.id}`" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-white mb-4" id="modal-title">Edit Pengguna</h3>
                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="edit_name" value="Nama" class="text-slate-300" />
                                    <x-text-input id="edit_name" name="name" type="text" class="mt-1 block w-full bg-slate-900 border-slate-600 text-white" x-model="editData.name" required />
                                </div>
                                <div>
                                    <x-input-label for="edit_email" value="Email" class="text-slate-300" />
                                    <x-text-input id="edit_email" name="email" type="email" class="mt-1 block w-full bg-slate-900 border-slate-600 text-white" x-model="editData.email" required />
                                </div>
                                <div>
                                    <x-input-label for="edit_role" value="Role" class="text-slate-300" />
                                    <select id="edit_role" name="role" x-model="editData.role" class="mt-1 block w-full rounded-md shadow-sm bg-slate-900 border-slate-600 text-white focus:border-blue-500 focus:ring-blue-500">
                                        <option value="member">Member</option>
                                        <option value="owner">Owner</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                                <div class="flex items-center gap-3 pt-2">
                                    <input type="hidden" name="allow_ai_receipt" value="0">
                                    <input id="edit_allow_ai_receipt" name="allow_ai_receipt" type="checkbox" value="1" class="w-5 h-5 rounded border-slate-600 bg-slate-900 text-blue-600 focus:ring-blue-500 focus:ring-offset-slate-800" x-model="editData.allow_ai_receipt" />
                                    <div class="flex flex-col">
                                        <label for="edit_allow_ai_receipt" class="text-sm font-medium text-slate-300">Izinkan Fitur AI (Struk)</label>
                                        <p class="text-xs text-slate-500">Jika dicentang, pengguna ini dapat menggunakan AI untuk membaca gambar struk otomatis.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-800/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-700">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Simpan
                            </button>
                            <button type="button" @click="editModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-600 shadow-sm px-4 py-2 bg-slate-700 text-base font-medium text-slate-300 hover:bg-slate-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Password Reset Modal -->
        <div x-show="passwordModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="passwordModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="passwordModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="passwordModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-700">
                    <form :action="`/admin/users/${passwordData.id}/reset-password`" method="POST">
                        @csrf
                        <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="p-2 bg-yellow-500/20 rounded-full text-yellow-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                </div>
                                <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">Reset Password</h3>
                            </div>
                            <p class="text-sm text-slate-400 mb-4">Atur ulang password untuk pengguna <strong class="text-white" x-text="passwordData.name"></strong>.</p>
                            
                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="new_password" value="Password Baru" class="text-slate-300" />
                                    <x-text-input id="new_password" name="password" type="password" class="mt-1 block w-full bg-slate-900 border-slate-600 text-white" required minlength="8" />
                                </div>
                                <div>
                                    <x-input-label for="password_confirmation" value="Konfirmasi Password Baru" class="text-slate-300" />
                                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full bg-slate-900 border-slate-600 text-white" required minlength="8" />
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-800/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-700">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Reset Password
                            </button>
                            <button type="button" @click="passwordModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-600 shadow-sm px-4 py-2 bg-slate-700 text-base font-medium text-slate-300 hover:bg-slate-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function userManagement() {
            return {
                editModalOpen: false,
                passwordModalOpen: false,
                editData: { id: null, name: '', email: '', role: '', allow_ai_receipt: false },
                passwordData: { id: null, name: '' },
                
                openEditModal(id, name, email, role, allow_ai_receipt) {
                    this.editData = { id, name, email, role, allow_ai_receipt };
                    this.editModalOpen = true;
                },
                
                openPasswordModal(id, name) {
                    this.passwordData = { id, name };
                    this.passwordModalOpen = true;
                }
            }
        }

        window.confirmDelete = function(e, form) {
            e.preventDefault();
            Swal.fire({
                title: 'Hapus Pengguna',
                text: 'Apakah Anda yakin ingin menghapus pengguna ini? Semua data terkait (kecuali data keluarga) mungkin akan terpengaruh.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#475569',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                background: '#1e293b',
                color: '#fff',
                customClass: {
                    popup: 'border border-slate-700/50 rounded-2xl shadow-2xl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
</x-app-layout>
