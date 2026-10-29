@extends('layouts.admin')

@section('title', 'Quản Lý Buồng Bệnh & Điều Phối Giường Trống Smart Bed')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>🚪</span> QUẢN LÝ BUỒNG BỆNH, KHỬ KHUẨN & ĐIỀU PHỐI GIƯỜNG TRỐNG
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Sơ đồ buồng bệnh thời gian thực, quy trình luân chuyển giường khử khuẩn vô trùng trước khi nhận bệnh mới.
            </p>
        </div>
        <div class="flex gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-600 font-bold text-xs border border-emerald-500/20">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Giường Trống: {{ $stats['available_clean'] }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-red-500/10 text-red-600 font-bold text-xs border border-red-500/20">
                <span class="w-2 h-2 rounded-full bg-red-500"></span> Đang Nằm: {{ $stats['occupied'] }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500/10 text-amber-600 font-bold text-xs border border-amber-500/20">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span> Chờ Khử Trùng: {{ $stats['dirty_pending'] }}
            </span>
        </div>
    </div>

    <!-- Rooms and Beds Visual Grid -->
    <div class="space-y-6">
        @forelse($rooms as $room)
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <div class="flex items-center gap-3">
                    <span class="px-2.5 py-1 rounded-lg bg-indigo-100 text-indigo-800 font-black text-xs font-mono">
                        {{ $room->room_number }}
                    </span>
                    <div>
                        <h3 class="font-bold text-sm text-slate-800 dark:text-white">{{ $room->department }}</h3>
                        <span class="text-xs text-slate-400">Tầng {{ $room->floor }} • Loại: {{ $room->room_type }}</span>
                    </div>
                </div>
                <span class="text-xs text-slate-500">Sức chứa: {{ $room->beds->count() }} giường</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                @foreach($room->beds as $bed)
                <div class="p-4 rounded-xl border {{ $bed->status === 'available_clean' ? 'border-emerald-500/40 bg-emerald-500/5' : ($bed->status === 'occupied' ? 'border-red-500/40 bg-red-500/5' : 'border-amber-500/40 bg-amber-500/5') }} space-y-2 relative">
                    <div class="flex justify-between items-start">
                        <span class="font-black font-mono text-sm {{ $bed->status === 'available_clean' ? 'text-emerald-700 dark:text-emerald-300' : ($bed->status === 'occupied' ? 'text-red-700 dark:text-red-300' : 'text-amber-700 dark:text-amber-300') }}">
                            {{ $bed->bed_code }}
                        </span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $bed->status === 'available_clean' ? 'bg-emerald-600 text-white' : ($bed->status === 'occupied' ? 'bg-red-600 text-white' : 'bg-amber-600 text-white animate-pulse') }}">
                            {{ $bed->status === 'available_clean' ? 'Trống Sạch' : ($bed->status === 'occupied' ? 'Có Người' : 'Chờ Lau') }}
                        </span>
                    </div>

                    @if($bed->status === 'occupied' && $bed->currentPatient)
                        <div class="text-xs py-1">
                            <span class="text-[10px] text-slate-400 block">Bệnh nhân:</span>
                            <strong class="text-slate-800 dark:text-white block">{{ $bed->currentPatient->full_name }}</strong>
                            <span class="text-[10px] text-slate-500">{{ $bed->currentPatient->patient_code }}</span>
                        </div>
                        <form action="{{ route('reception.bed-board.discharge', $bed) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-1.5 rounded-lg bg-slate-700 text-white font-bold text-[11px] hover:bg-slate-600 transition">
                                📤 Xuất Viện / Rời Giường
                            </button>
                        </form>
                    @elseif($bed->status === 'dirty_pending')
                        <div class="text-xs py-1 text-amber-800 dark:text-amber-200">
                            ⚠️ Bệnh nhân vừa trả giường. Cần nhân viên lau khử trùng dung dịch Cloramin B trước khi xếp người mới.
                        </div>
                        <form action="{{ route('reception.bed-board.clean', $bed) }}" method="POST" class="space-y-1">
                            @csrf
                            <input type="hidden" name="cleaner_name" value="NV. Nguyễn Thị Lan (Hộ lý)">
                            <input type="hidden" name="disinfectant_chemical" value="Cloramin B 0.5% + Aniospray 29M">
                            <button type="submit" class="w-full py-1.5 rounded-lg bg-amber-600 text-white font-bold text-[11px] hover:bg-amber-500 transition">
                                🧹 Đã Phun Khử Khuẩn Xong
                            </button>
                        </form>
                    @else
                        <div class="text-xs py-1 text-emerald-800 dark:text-emerald-200">
                            ✓ Đã khử trùng vô khuẩn lúc {{ $bed->last_disinfected_at ? $bed->last_disinfected_at->format('H:i') : '08:00' }}. Sẵn sàng đón bệnh nhân.
                        </div>
                        <button onclick="openAssignModal({{ $bed->id }}, '{{ $bed->bed_code }}')" class="w-full py-1.5 rounded-lg bg-emerald-600 text-white font-bold text-[11px] hover:bg-emerald-500 transition">
                            📥 Xếp Bệnh Nhân Vào
                        </button>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-slate-400 bg-white rounded-2xl border">
            Chưa có buồng bệnh nào được thiết lập.
        </div>
        @endforelse
    </div>
</div>

<!-- Modal Xếp Bệnh Nhân -->
<div id="modal-assign-bed" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="font-bold text-base text-slate-800 dark:text-white">Xếp Bệnh Nhân Vào Giường <span id="assign-bed-code" class="text-indigo-600"></span></h3>
            <button onclick="document.getElementById('modal-assign-bed').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form id="form-assign-bed" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="font-bold block mb-1">Chọn Bệnh Nhân Nội Trú / Hồi Tỉnh *</label>
                <select name="patient_profile_id" required class="w-full border rounded-xl p-2.5 bg-slate-50 dark:bg-slate-700">
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->patient_code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modal-assign-bed').classList.add('hidden')" class="px-4 py-2 border rounded-xl font-bold">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-500">Xác Nhận Nhận Giường</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAssignModal(bedId, bedCode) {
        document.getElementById('assign-bed-code').innerText = bedCode;
        document.getElementById('form-assign-bed').action = '/reception/bed-board/assign/' + bedId;
        document.getElementById('modal-assign-bed').classList.remove('hidden');
    }
</script>
@endsection
