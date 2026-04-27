<x-layouts.dashboard>
    <x-slot name="header">Notifikasi</x-slot>

    <div class="p-6 max-w-3xl mx-auto space-y-5">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-800">Notifikasi</h2>
                <p class="text-xs text-slate-400 mt-1">Update terbaru & pesan penting</p>
            </div>
            <button id="btn-mark-all"
                class="text-xs font-medium px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 transition">
                Tandai semua
            </button>
        </div>

        {{-- Filter Tabs --}}
        <div class="flex gap-2 text-xs">
            <button class="px-3 py-1.5 rounded-lg bg-slate-900 text-white">Semua</button>
            <button class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200">Belum dibaca</button>
            <button class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200">Alert</button>
        </div>

        {{-- Notification List --}}
        <div class="bg-white border border-slate-200 rounded-2xl divide-y">

            @forelse ($notifs as $notif)
                @php
                    $isUnread = is_null($notif->dibaca_at);
                    $isAlert  = $notif->tipe === 'alert';
                    $meta     = $notif->meta ?? [];
                @endphp

                <div id="notif-{{ $notif->id }}"
                    class="p-5 flex gap-4 transition hover:bg-slate-50 cursor-pointer">

                    {{-- Icon --}}
                    <div class="mt-1">
                        <div class="w-9 h-9 flex items-center justify-center rounded-xl
                            {{ $isAlert ? 'bg-red-100 text-red-500' : 'bg-blue-100 text-blue-500' }}">
                            
                            @if ($isAlert)
                                ⚠️
                            @else
                                🔔
                            @endif
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1">

                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-semibold
                                    {{ $isUnread ? 'text-slate-900' : 'text-slate-500' }}">
                                    {{ $notif->judul }}
                                </h3>

                                @if ($isUnread)
                                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                @endif
                            </div>

                            <span class="text-[10px] text-slate-400">
                                {{ $notif->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-400 mt-1">
                            {{ $meta['dikirim_oleh'] ?? 'Sistem' }}
                        </p>

                        <p class="text-sm text-slate-600 mt-2 leading-relaxed">
                            {{ $notif->pesan }}
                        </p>

                        {{-- SLA Badge --}}
                        @if (!empty($meta['jumlah_over_sla']))
                            <div class="mt-3 text-xs inline-block px-2 py-1 rounded-lg
                                bg-red-100 text-red-600 font-medium">
                                {{ $meta['jumlah_over_sla'] }} melewati SLA
                            </div>
                        @endif

                        {{-- Actions --}}
                        <div class="flex gap-4 mt-3 text-xs">
                            <a href="{{ route('pengaduan.sla') }}"
                               class="text-blue-600 hover:underline">
                                Lihat detail
                            </a>

                            @if ($isUnread)
                                <button data-id="{{ $notif->id }}"
                                    class="btn-mark-one text-slate-400 hover:text-slate-600">
                                    Tandai dibaca
                                </button>
                            @endif
                        </div>

                    </div>
                </div>

            @empty
                <div class="py-20 text-center">
                    <div class="text-4xl mb-3">📭</div>
                    <p class="text-sm text-slate-500 font-medium">Tidak ada notifikasi</p>
                    <p class="text-xs text-slate-400 mt-1">Semua update akan muncul di sini</p>
                </div>
            @endforelse

        </div>

        <div>{{ $notifs->links() }}</div>

    </div>
    <x-slot name="scripts">
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

/* ===============================
   HELPER: animasi remove notif
================================ */
function fadeOutAndRemove(el) {
    el.style.transition = 'all 0.3s ease';
    el.style.opacity = '0';
    el.style.transform = 'translateX(20px)';
    setTimeout(() => el.remove(), 300);
}

/* ===============================
   MARK ONE (tanpa reload)
================================ */
document.querySelectorAll('.btn-mark-one').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        e.stopPropagation();

        const id   = btn.dataset.id;
        const card = document.getElementById('notif-' + id);

        try {
            await fetch(`/notifikasi/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            // UI Update
            card.classList.remove('bg-blue-50');
            card.querySelector('h3')?.classList.replace('text-slate-900','text-slate-500');
            card.querySelector('.bg-blue-500')?.remove();

            btn.remove();

            updateBadge();

        } catch (err) {
            console.error('Gagal update notif:', err);
        }
    });
});

/* ===============================
   MARK ALL (tanpa reload)
================================ */
document.getElementById('btn-mark-all')?.addEventListener('click', async () => {
    try {
        await fetch('/notifikasi/read-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        // Update semua UI tanpa reload
        document.querySelectorAll('[id^="notif-"]').forEach(card => {
            card.classList.remove('bg-blue-50');

            const title = card.querySelector('h3');
            if (title) title.classList.replace('text-slate-900','text-slate-500');

            const dot = card.querySelector('.bg-blue-500');
            if (dot) dot.remove();

            const btn = card.querySelector('.btn-mark-one');
            if (btn) btn.remove();
        });

        updateBadge();

    } catch (err) {
        console.error('Gagal mark all:', err);
    }
});

/* ===============================
   UPDATE BADGE (REAL-TIME)
================================ */
async function updateBadge() {
    try {
        const res  = await fetch('/notifikasi/count');
        const data = await res.json();

        const badge = document.getElementById('notif-badge');

        if (badge) {
            badge.textContent = data.count > 99 ? '99+' : data.count;
            badge.style.display = data.count > 0 ? 'inline-flex' : 'none';
        }

    } catch (err) {
        console.error('Gagal update badge:', err);
    }
}

/* ===============================
   CLICK CARD → OPEN DETAIL
================================ */
document.querySelectorAll('[id^="notif-"]').forEach(card => {
    card.addEventListener('click', () => {
        const link = card.querySelector('a');
        if (link) window.location.href = link.href;
    });
});

/* ===============================
   AUTO REFRESH (optional realtime ringan)
================================ */
setInterval(updateBadge, 15000); // tiap 15 detik

</script>
</x-slot>


</x-layouts.dashboard>