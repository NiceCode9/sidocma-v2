<?php

namespace App\Http\Controllers;

use App\Enums\DisposisiStatus;
use App\Models\Disposisi;
use App\Models\DisposisiTarget;
use App\Models\Document;
use App\Models\Surat;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\DisposisiNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Yajra\DataTables\Facades\DataTables;

class DisposisiController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->hasRole(['super admin', 'direktur'])) {
                abort(403, 'Hanya direktur dan super admin yang dapat mengakses fitur ini.');
            }
            return $next($request);
        })->except(['masuk', 'tandaiParaf', 'show', 'cetak']);
    }

    public function index()
    {
        return view('surat.disposisi.index');
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $data = Disposisi::with(['creator', 'surat', 'document', 'targets.unit'])
                ->select('*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('no_agenda', function ($row) {
                    $icon = $row->forwarded_from
                        ? ' <i class="fas fa-share text-primary" title="Hasil teruskan dari disposisi lain"></i>'
                        : '';
                    return $row->no_agenda . $icon;
                })
                ->addColumn('asal', function ($row) {
                    if ($row->surat) {
                        return $row->surat->perihal;
                    }
                    if ($row->document) {
                        return $row->document->title;
                    }
                    return $row->asal_naskah;
                })
                ->addColumn('target_units', function ($row) {
                    return $row->targets->map(function ($t) {
                        return $t->unit->name;
                    })->implode(', ');
                })
                ->addColumn('sifat_badge', function ($row) {
                    $labels = [
                        'sangat_segera' => 'Sangat Segera',
                        'segera' => 'Segera',
                        'rahasia' => 'Rahasia',
                        'biasa' => 'Biasa',
                    ];
                    $classes = [
                        'sangat_segera' => 'badge-danger',
                        'segera' => 'badge-warning',
                        'rahasia' => 'badge-dark',
                        'biasa' => 'badge-info',
                    ];
                    $label = $labels[$row->sifat] ?? $row->sifat;
                    $class = $classes[$row->sifat] ?? 'badge-secondary';
                    return '<span class="badge ' . $class . '">' . $label . '</span>';
                })
                ->addColumn('status_badge', function ($row) {
                    return '<span class="badge ' . $row->status->badgeClass() . '">' . $row->status->label() . '</span>';
                })
                ->addColumn('tanggal', function ($row) {
                    return $row->created_at->format('d-m-Y');
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('disposisi.show', $row->id) . '" class="btn btn-info btn-sm" title="Lihat"><i class="fas fa-eye"></i></a>';
                    if (auth()->user()->hasRole('super admin')) {
                        $btn .= '<button type="button" class="btn btn-primary btn-sm" onclick="teruskanDisposisi(' . $row->id . ')" title="Teruskan"><i class="fas fa-share"></i></button>';
                    }
                    if ($row->isEditable()) {
                        $btn .= '<a href="' . route('disposisi.edit', $row->id) . '" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>';
                        $btn .= '<button type="button" class="btn btn-success btn-sm" onclick="selesaikanDisposisi(' . $row->id . ')" title="Selesaikan"><i class="fas fa-check"></i></button>';
                        $btn .= '<button type="button" class="btn btn-danger btn-sm" onclick="hapusDisposisi(' . $row->id . ')" title="Hapus"><i class="fas fa-trash"></i></button>';
                    }
                    $btn .= '<a href="' . route('disposisi.cetak', $row->id) . '" class="btn btn-secondary btn-sm" target="_blank" title="Cetak"><i class="fas fa-print"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['no_agenda', 'sifat_badge', 'status_badge', 'action'])
                ->make(true);
        }
    }

    public function create(Request $request)
    {
        $type = $request->type;
        $id = $request->id;

        $surat = null;
        $document = null;
        $prefill = [];

        if ($type === 'surat') {
            $surat = Surat::with('user')->findOrFail($id);
            $prefill = [
                'tanggal_naskah' => $surat->disposisi_tgl_naskah,
                'masuk_tu' => $surat->disposisi_masuk_tu,
                'tgl_no_naskah' => $surat->disposisi_tgl_no_naskah,
                'asal_naskah' => $surat->disposisi_asal_naskah ?? $surat->user?->name,
                'isi_informasi' => $surat->disposisi_informasi_naskah,
            ];
        } elseif ($type === 'document') {
            $document = Document::with('creator')->findOrFail($id);
            $prefill = [
                'tanggal_naskah' => $document->disposisi_tgl_naskah,
                'masuk_tu' => $document->disposisi_masuk_tu,
                'tgl_no_naskah' => $document->disposisi_tgl_no_naskah,
                'asal_naskah' => $document->disposisi_asal_naskah ?? $document->creator?->name,
                'isi_informasi' => $document->disposisi_informasi_naskah,
            ];
        }

        $units = Unit::all();

        $disposisi = null;
        return view('surat.disposisi.form', compact('type', 'surat', 'document', 'units', 'disposisi', 'prefill'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:surat,document',
            'surat_id' => 'nullable|required_if:type,surat|exists:surats,id',
            'document_id' => 'nullable|required_if:type,document|exists:documents,id',
            'no_agenda' => 'required|string|max:255',
            'tanggal_naskah' => 'required|date',
            'masuk_tu' => 'required',
            'tgl_no_naskah' => 'required',
            'asal_naskah' => 'required',
            'isi_informasi' => 'required',
            'sifat' => 'required|in:sangat_segera,segera,rahasia,biasa',
            'catatan_lain' => 'nullable',
            'batas_waktu' => 'nullable|date',
            'targets' => 'required|array|min:1',
            'targets.*.unit_id' => 'required|exists:units,id',
            'targets.*.instruksi' => 'required|array|min:1',
            'targets.*.instruksi.*' => 'string',
            'targets.*.keterangan' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $data = [
                'surat_id' => $request->type === 'surat' ? $request->surat_id : null,
                'document_id' => $request->type === 'document' ? $request->document_id : null,
                'no_agenda' => $request->no_agenda,
                'tanggal_naskah' => $request->tanggal_naskah,
                'masuk_tu' => Carbon::parse($request->masuk_tu),
                'tgl_no_naskah' => $request->tgl_no_naskah,
                'asal_naskah' => $request->asal_naskah,
                'isi_informasi' => $request->isi_informasi,
                'sifat' => $request->sifat,
                'catatan_lain' => $request->catatan_lain,
                'batas_waktu' => $request->batas_waktu,
                'status' => DisposisiStatus::Diproses,
                'created_by' => Auth::id(),
            ];

            $disposisi = Disposisi::create($data);

            foreach ($request->targets as $target) {
                DisposisiTarget::create([
                    'disposisi_id' => $disposisi->id,
                    'unit_id' => $target['unit_id'],
                    'instruksi' => $target['instruksi'],
                    'keterangan' => $target['keterangan'] ?? null,
                ]);
            }

            DB::commit();

            // Kirim notifikasi ke semua user di unit target
            $targetUnitIds = $disposisi->getTargetUnitIds();
            $targetUsers = User::whereIn('unit_id', $targetUnitIds)->get();

            if ($targetUsers->isNotEmpty()) {
                Notification::send($targetUsers, new DisposisiNotification($disposisi, 'baru', Auth::user()));
            }

            // Broadcast event
            broadcast(new \App\Events\DisposisiCreate($disposisi));

            return response()->json([
                'success' => true,
                'message' => 'Disposisi berhasil dibuat',
                'data' => $disposisi,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat disposisi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $disposisi = Disposisi::with(['surat', 'document', 'targets.unit', 'creator', 'approver', 'forwardedFrom'])->findOrFail($id);
        $this->authorizeDisposisiAccess($disposisi);
        return view('surat.disposisi.show', compact('disposisi'));
    }

    public function edit($id)
    {
        $disposisi = Disposisi::with(['surat', 'document', 'targets'])->findOrFail($id);

        if (!$disposisi->isEditable()) {
            return redirect()->route('disposisi.show', $id)
                ->with('error', 'Disposisi sudah diproses dan tidak dapat diedit.');
        }

        $units = Unit::all();
        $surat = $disposisi->surat;
        $document = $disposisi->document;
        $type = $disposisi->surat_id ? 'surat' : 'document';
        $prefill = [];

        return view('surat.disposisi.form', compact('disposisi', 'type', 'surat', 'document', 'units', 'prefill'));
    }

    public function update(Request $request, $id)
    {
        $disposisi = Disposisi::findOrFail($id);

        if (!$disposisi->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Disposisi sudah diproses dan tidak dapat diedit.',
            ], 422);
        }

        $request->validate([
            'no_agenda' => 'required|string|max:255',
            'tanggal_naskah' => 'required|date',
            'masuk_tu' => 'required',
            'tgl_no_naskah' => 'required',
            'asal_naskah' => 'required',
            'isi_informasi' => 'required',
            'sifat' => 'required|in:sangat_segera,segera,rahasia,biasa',
            'catatan_lain' => 'nullable',
            'batas_waktu' => 'nullable|date',
            'targets' => 'required|array|min:1',
            'targets.*.unit_id' => 'required|exists:units,id',
            'targets.*.instruksi' => 'required|array|min:1',
            'targets.*.instruksi.*' => 'string',
            'targets.*.keterangan' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $disposisi->update([
                'no_agenda' => $request->no_agenda,
                'tanggal_naskah' => $request->tanggal_naskah,
                'masuk_tu' => Carbon::parse($request->masuk_tu),
                'tgl_no_naskah' => $request->tgl_no_naskah,
                'asal_naskah' => $request->asal_naskah,
                'isi_informasi' => $request->isi_informasi,
                'sifat' => $request->sifat,
                'catatan_lain' => $request->catatan_lain,
                'batas_waktu' => $request->batas_waktu,
            ]);

            // Hapus target lama, buat baru
            $disposisi->targets()->delete();

            foreach ($request->targets as $target) {
                DisposisiTarget::create([
                    'disposisi_id' => $disposisi->id,
                    'unit_id' => $target['unit_id'],
                    'instruksi' => $target['instruksi'],
                    'keterangan' => $target['keterangan'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Disposisi berhasil diupdate',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate disposisi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $disposisi = Disposisi::findOrFail($id);

        if (!$disposisi->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Disposisi sudah diproses dan tidak dapat dihapus.',
            ], 422);
        }

        $disposisi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Disposisi berhasil dihapus',
        ]);
    }

    public function selesaikan($id)
    {
        $disposisi = Disposisi::findOrFail($id);

        if ($disposisi->status === DisposisiStatus::Selesai) {
            return response()->json([
                'success' => false,
                'message' => 'Disposisi sudah selesai sebelumnya.',
            ], 422);
        }

        $disposisi->update([
            'status' => DisposisiStatus::Selesai,
            'approved_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Disposisi berhasil diselesaikan',
        ]);
    }

    public function cetak($id)
    {
        $disposisi = Disposisi::with(['surat', 'document', 'targets.unit', 'creator'])->findOrFail($id);
        $this->authorizeDisposisiAccess($disposisi);
        return view('surat.disposisi.cetak', compact('disposisi'));
    }

    public function masuk()
    {
        $user = Auth::user();
        $disposisiTargets = DisposisiTarget::with(['disposisi.creator', 'disposisi.targets.unit', 'unit'])
            ->where('unit_id', $user->unit_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('surat.disposisi.masuk', compact('disposisiTargets'));
    }

    public function tandaiParaf($id)
    {
        $target = DisposisiTarget::findOrFail($id);
        $user = Auth::user();

        if ($target->unit_id !== $user->unit_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke target ini.',
            ], 403);
        }

        $target->update([
            'paraf' => true,
            'paraf_at' => now(),
        ]);

        // Hybrid: cek apakah semua target sudah paraf → auto selesai
        $allParaf = $target->disposisi->targets()->where('paraf', false)->doesntExist();
        if ($allParaf && $target->disposisi->status !== DisposisiStatus::Selesai) {
            $target->disposisi->update([
                'status' => DisposisiStatus::Selesai,
                'approved_by' => $user->id,
            ]);

            Notification::send(
                $target->disposisi->creator,
                new DisposisiNotification($target->disposisi, 'selesai')
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Paraf berhasil',
        ]);
    }

    private function authorizeDisposisiAccess(Disposisi $disposisi): void
    {
        $user = Auth::user();

        if ($user->hasRole(['super admin', 'direktur'])) {
            return;
        }

        $isTarget = $disposisi->targets()->where('unit_id', $user->unit_id)->exists();

        if (!$isTarget) {
            abort(403, 'Anda tidak memiliki akses ke disposisi ini.');
        }
    }

    public function forward($id)
    {
        $disposisi = Disposisi::with(['surat', 'document', 'targets.unit', 'creator'])->findOrFail($id);

        $asal = $disposisi->surat?->perihal
            ?? $disposisi->document?->title
            ?? $disposisi->asal_naskah
            ?? '-';

        $targetUnitIds = $disposisi->targets->pluck('unit_id')->toArray();
        $units = Unit::whereNotIn('id', $targetUnitIds)->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $disposisi->id,
                'no_agenda' => $disposisi->no_agenda,
                'asal' => $asal,
                'sifat' => $disposisi->sifat,
                'status' => $disposisi->status->label(),
            ],
            'units' => $units,
        ]);
    }

    public function processForward(Request $request, $id)
    {
        if (!Auth::user()->hasRole('super admin')) {
            abort(403, 'Hanya super admin yang dapat meneruskan disposisi.');
        }

        $source = Disposisi::with('targets')->findOrFail($id);

        $request->validate([
            'no_agenda' => 'required|string|max:255',
            'unit_ids' => 'required|array|min:1',
            'unit_ids.*' => 'exists:units,id',
        ]);

        try {
            DB::beginTransaction();

            $newDisposisi = Disposisi::create([
                'surat_id' => $source->surat_id,
                'document_id' => $source->document_id,
                'no_agenda' => $request->no_agenda,
                'tanggal_naskah' => $source->tanggal_naskah,
                'masuk_tu' => $source->masuk_tu,
                'tgl_no_naskah' => $source->tgl_no_naskah,
                'asal_naskah' => $source->asal_naskah,
                'isi_informasi' => $source->isi_informasi,
                'sifat' => $source->sifat,
                'catatan_lain' => $source->catatan_lain,
                'batas_waktu' => $source->batas_waktu,
                'status' => DisposisiStatus::Diproses,
                'created_by' => Auth::id(),
                'forwarded_from' => $source->id,
            ]);

            $instruction = $source->targets->first()?->instruksi ?? ['perhatian'];

            foreach ($request->unit_ids as $unitId) {
                DisposisiTarget::create([
                    'disposisi_id' => $newDisposisi->id,
                    'unit_id' => $unitId,
                    'instruksi' => $instruction,
                    'keterangan' => 'Diteruskan oleh Super Admin',
                ]);
            }

            DB::commit();

            // Notifikasi ke unit tujuan baru
            $targetUsers = User::whereIn('unit_id', $request->unit_ids)->get();
            if ($targetUsers->isNotEmpty()) {
                Notification::send($targetUsers, new DisposisiNotification($newDisposisi, 'baru', Auth::user()));
            }

            broadcast(new \App\Events\DisposisiCreate($newDisposisi));

            return response()->json([
                'success' => true,
                'message' => 'Disposisi berhasil diteruskan',
                'data' => $newDisposisi,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal meneruskan disposisi: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function generateNoAgenda(): string
    {
        $prefix = 'AGND';
        $year = date('Y');
        $last = Disposisi::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->value('no_agenda');

        if ($last) {
            $parts = explode('/', $last);
            $num = (int) end($parts) + 1;
        } else {
            $num = 1;
        }

        return sprintf('%s/%s/%04d', $prefix, $year, $num);
    }
}
