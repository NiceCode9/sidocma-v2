<?php

namespace App\Http\Controllers;

use App\Events\SuratCreate;
use App\Models\Disposisi;
use App\Models\Document;
use App\Models\DocumentPermission;
use App\Models\Surat;
use App\Models\User;
use App\Notifications\SuratNotification;
use App\Services\PermissionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use Yajra\DataTables\Facades\DataTables;

class ManagementSuratController extends Controller
{
    public function index()
    {
        return view('surat.manage.index');
    }

    public function suratMasukData(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();

            // Super admin: hanya lihat Surat (perilaku asli)
            if ($user->hasRole('super admin') && !$user->hasRole('direktur')) {
                $data = Surat::with('user.unit')
                    ->accessibleTo($user)
                    ->select('*');

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('tipe', fn($row) => '<span class="badge badge-primary">Surat</span>')
                    ->addColumn('user_name', fn($row) => $row->user ? $row->user->name : '-')
                    ->addColumn('pengirim', fn($row) => $row->user ? $row->user->name : '-')
                    ->addColumn('unit', fn($row) => $row->user && $row->user->unit ? $row->user->unit->name : '-')
                    ->addColumn('status_badge', function ($row) {
                        return $row->read_at
                            ? '<span class="badge badge-success">Dibaca</span>'
                            : '<span class="badge badge-warning">Belum Dibaca</span>';
                    })
                    ->addColumn('file', function ($row) {
                        if (!$row->file) return '';
                        return '<a href="' . route('kirim-surat.download', $row->id) . '" class="btn btn-success btn-sm" title="Download">
                            <i class="fas fa-download"></i>
                        </a>';
                    })
                    ->addColumn('action', function ($row) {
                        $actionBtn = '<div class="btn-group" role="group">
                                <a href="' . route('surat.view', $row->id) . '" class="btn btn-info btn-sm" title="Lihat Surat">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>';
                        return $actionBtn;
                    })
                    ->addColumn('tanggal_dibuat', function($row){
                        return Carbon::parse($row->created_at)->format('d-m-Y H:i:s');
                    })
                    ->addColumn('disposisi_badge', fn($row) => '<span class="badge badge-secondary">-</span>')
                ->rawColumns(['tipe', 'status_badge', 'disposisi_badge', 'file', 'action'])
                    ->make(true);
            }

            // Ambil semua disposisi untuk lookup
            $disposisiSuratIds = \App\Models\Disposisi::whereNotNull('surat_id')->pluck('surat_id', 'id');
            $disposisiDocIds = \App\Models\Disposisi::whereNotNull('document_id')->pluck('document_id', 'id');
            $disposisiStatuses = \App\Models\Disposisi::select('id', 'surat_id', 'document_id', 'status')->get()->keyBy(function ($item) {
                return $item->surat_id ? 'surat_' . $item->surat_id : 'doc_' . $item->document_id;
            });

            // Direktur: gabungan Surat + Document (is_letter) yang butuh disposisi
            $suratList = Surat::with('user.unit')
                ->where('needs_disposisi', true)
                ->accessibleTo($user)
                ->get()
                ->map(function ($item) use ($disposisiStatuses) {
                $key = 'surat_' . $item->id;
                $disposisi = $disposisiStatuses->get($key);
                return [
                    'id' => $key,
                    'source_type' => 'surat',
                    'source_id' => $item->id,
                    'no_surat' => $item->no_surat,
                    'perihal' => $item->perihal,
                    'pengirim' => $item->user ? $item->user->name : '-',
                    'unit' => $item->user && $item->user->unit ? $item->user->unit->name : '-',
                    'status_badge' => $item->read_at
                        ? '<span class="badge badge-success">Dibaca</span>'
                        : '<span class="badge badge-warning">Belum Dibaca</span>',
                    'disposisi_badge' => $disposisi
                        ? '<a href="' . route('disposisi.show', $disposisi->id) . '" class="badge ' . $disposisi->status->badgeClass() . '">' . $disposisi->status->label() . '</a>'
                        : '<span class="badge badge-secondary">Belum Disposisi</span>',
                    'read_at' => $item->read_at,
                    'has_file' => !empty($item->file),
                    'file_path' => $item->file,
                    'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                    'tanggal_dibuat' => $item->created_at->format('d-m-Y H:i:s'),
                    'created_at_raw' => $item->created_at,
                ];
            });

            $docList = Document::with('creator.unit')
                ->where('is_letter', true)
                ->where('needs_disposisi', true)
                ->where('is_active', true)
                ->get()
                ->map(function ($item) use ($disposisiStatuses) {
                    $key = 'doc_' . $item->id;
                    $disposisi = $disposisiStatuses->get($key);
                    $isRead = $item->shares && $item->shares->is_read;
                    return [
                        'id' => $key,
                        'source_type' => 'document',
                        'source_id' => $item->id,
                        'no_surat' => $item->document_number ?? '-',
                        'perihal' => $item->title,
                        'pengirim' => $item->creator ? $item->creator->name : '-',
                        'unit' => $item->creator && $item->creator->unit ? $item->creator->unit->name : '-',
                        'status_badge' => $isRead
                            ? '<span class="badge badge-success">Dibaca</span>'
                            : '<span class="badge badge-warning">Belum Dibaca</span>',
                        'disposisi_badge' => $disposisi
                            ? '<a href="' . route('disposisi.show', $disposisi->id) . '" class="badge ' . $disposisi->status->badgeClass() . '">' . $disposisi->status->label() . '</a>'
                            : '<span class="badge badge-secondary">Belum Disposisi</span>',
                        'read_at' => $item->shares ? $item->shares->read_at : null,
                        'has_file' => !empty($item->file_path),
                        'file_path' => $item->file_path,
                        'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                        'tanggal_dibuat' => $item->created_at->format('d-m-Y H:i:s'),
                        'created_at_raw' => $item->created_at,
                    ];
                });

            $combined = $suratList->toBase()->merge($docList)->sortByDesc('created_at_raw')->values();

            return DataTables::of($combined)
                ->addIndexColumn()
                ->addColumn('tipe', function ($row) {
                    return $row['source_type'] === 'surat'
                        ? '<span class="badge badge-primary">Surat</span>'
                        : '<span class="badge badge-info">Dokumen</span>';
                })
                ->addColumn('file', function ($row) {
                    if (!$row['has_file']) return '';
                    if ($row['source_type'] === 'surat') {
                        return '<a href="' . route('kirim-surat.download', $row['source_id']) . '" class="btn btn-success btn-sm" title="Download">
                            <i class="fas fa-download"></i>
                        </a>';
                    }
                    return '<a href="' . route('documents.download', $row['source_id']) . '" class="btn btn-success btn-sm" title="Download">
                        <i class="fas fa-download"></i>
                    </a>';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<div class="btn-group" role="group">';
                    if ($row['source_type'] === 'surat') {
                        $actionBtn .= '<a href="' . route('surat.view', $row['source_id']) . '" class="btn btn-info btn-sm" title="Lihat Surat">
                            <i class="fas fa-eye"></i>
                        </a>';
                        $actionBtn .= '<a href="' . route('disposisi.create', ['type' => 'surat', 'id' => $row['source_id']]) . '" class="btn btn-primary btn-sm" title="Buat Disposisi">
                            <i class="fas fa-tasks"></i>
                        </a>';
                    } else {
                        $actionBtn .= '<a href="' . route('documents.view-file', $row['source_id']) . '" class="btn btn-info btn-sm" title="Lihat Dokumen" target="_blank">
                            <i class="fas fa-eye"></i>
                        </a>';
                        $actionBtn .= '<a href="' . route('disposisi.create', ['type' => 'document', 'id' => $row['source_id']]) . '" class="btn btn-primary btn-sm" title="Buat Disposisi">
                            <i class="fas fa-tasks"></i>
                        </a>';
                    }
                    $actionBtn .= '</div>';
                    return $actionBtn;
                })
                ->rawColumns(['tipe', 'status_badge', 'disposisi_badge', 'file', 'action'])
                ->make(true);
        }
    }

    public function suratMasukStats()
    {
        $user = Auth::user();

        // Super admin: hanya Surat
        if ($user->hasRole('super admin') && !$user->hasRole('direktur')) {
            $suratQuery = Surat::accessibleTo($user);

            return response()->json([
                'totalSuratMasuk' => (clone $suratQuery)->count(),
                'suratMasukDibaca' => (clone $suratQuery)->whereNotNull('read_at')->count(),
                'suratMasukBelumDibaca' => (clone $suratQuery)->whereNull('read_at')->count(),
                'suratMasukHariIni' => (clone $suratQuery)->whereDate('created_at', today())->count(),
            ]);
        }

        // Direktur: gabungan (hanya yang butuh disposisi)
        $suratBase = Surat::where('needs_disposisi', true)->accessibleTo($user);
        $suratTotal = (clone $suratBase)->count();
        $suratDibaca = (clone $suratBase)->whereNotNull('read_at')->count();
        $suratBelum = (clone $suratBase)->whereNull('read_at')->count();
        $suratHariIni = (clone $suratBase)->whereDate('created_at', today())->count();

        $docTotal = Document::where('is_letter', true)->where('needs_disposisi', true)->where('is_active', true)->count();
        $docDibaca = Document::where('is_letter', true)->where('needs_disposisi', true)->where('is_active', true)
            ->whereHas('shares', fn($q) => $q->where('is_read', true))
            ->count();
        $docBelum = Document::where('is_letter', true)->where('needs_disposisi', true)->where('is_active', true)
            ->where(function ($q) {
                $q->whereHas('shares', fn($sq) => $sq->where('is_read', false))
                  ->orWhereDoesntHave('shares');
            })
            ->count();
        $docHariIni = Document::where('is_letter', true)->where('needs_disposisi', true)->where('is_active', true)
            ->whereDate('created_at', today())
            ->count();

        return response()->json([
            'totalSuratMasuk' => $suratTotal + $docTotal,
            'suratMasukDibaca' => $suratDibaca + $docDibaca,
            'suratMasukBelumDibaca' => $suratBelum + $docBelum,
            'suratMasukHariIni' => $suratHariIni + $docHariIni,
        ]);
    }

    public function suratKeluarData(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();

            // Direktur (bukan super admin): data disposisi yang dikirim oleh direktur
            if ($user->hasRole('direktur') && !$user->hasRole('super admin')) {
                $data = Disposisi::with(['creator', 'surat', 'document', 'targets.unit'])
                    ->where('created_by', $user->id)
                    ->select('*');

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('no_agenda', function ($row) {
                        return $row->no_agenda;
                    })
                    ->addColumn('asal', function ($row) {
                        if ($row->surat) {
                            return $row->surat->perihal;
                        }
                        if ($row->document) {
                            return $row->document->title;
                        }
                        return $row->asal_naskah ?? '-';
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
                        return $row->created_at->format('d-m-Y H:i:s');
                    })
                    ->addColumn('action', function ($row) {
                        $btn = '<div class="btn-group" role="group">';
                        $btn .= '<a href="' . route('disposisi.show', $row->id) . '" class="btn btn-info btn-sm" title="Lihat"><i class="fas fa-eye"></i></a>';
                        $btn .= '<a href="' . route('disposisi.cetak', $row->id) . '" class="btn btn-secondary btn-sm" target="_blank" title="Cetak"><i class="fas fa-print"></i></a>';
                        $btn .= '</div>';
                        return $btn;
                    })
                    ->rawColumns(['sifat_badge', 'status_badge', 'action'])
                    ->make(true);
            }

            $query = Document::with(['creator', 'category', 'folder' => function ($query) {
                $query->select('id', 'name');
            }])
                ->where('is_letter', true);

            $data = $query->select('*')->orderBy('created_at', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('creator_name', function ($row) {
                    return $row->creator ? $row->creator->name : '-';
                })
                ->addColumn('category_name', function ($row) {
                    return $row->category ? $row->category->name : '-';
                })
                ->addColumn('is_read', function ($row) {
                    if ($row->shares) {
                        return $row->shares->is_read
                            ? '<span class="badge badge-success"><i class="fas fa-check"></i> Dibaca</span>'
                            : '<span class="badge badge-warning"><i class="fas fa-times"></i> Belum Dibaca</span>';
                    }
                    return '-';
                })
                ->addColumn('read_at', function ($row) {
                    if ($row->shares) {
                        return $row->shares->read_at ? $row->shares->read_at->format('d-m-Y H:i:s') : '-';
                    }
                    return '-';
                })
                ->addColumn('tanggal_dibuat', function ($row) {
                    return $row->created_at->format('d-m-Y H:i:s');
                })
                ->addColumn('jumlah_download', function ($row) {
                    return $row->shares ? $row->shares->download_count : 0;
                })
                ->addColumn('opened_by', function ($row) {
                    return $row->shares->opened_by ?? '-';
                })
                // ->addColumn('status', function ($row) {
                //     $statusClass = '';
                //     switch ($row->status) {
                //         case 'draft':
                //             $statusClass = 'badge-secondary';
                //             break;
                //         case 'review':
                //             $statusClass = 'badge-warning';
                //             break;
                //         case 'approved':
                //             $statusClass = 'badge-success';
                //             break;
                //         case 'rejected':
                //             $statusClass = 'badge-danger';
                //             break;
                //         default:
                //             $statusClass = 'badge-info';
                //     }
                //     return '<span class="badge ' . $statusClass . '">' . ucfirst($row->status) . '</span>';
                // })
                // ->addColumn('file_size', function ($row) {
                //     return $row->formatted_file_size;
                // })
                // ->addColumn('file', function ($row) {
                //     if ($row->file_path) {
                //         return '<a href="' . asset('storage/' . $row->file_path) . '" target="_blank" class="btn btn-sm btn-primary">
                //                     <i class="fas fa-download"></i> Download
                //                 </a>';
                //     }
                //     return '-';
                // })
                // ->addColumn('confidential', function ($row) {
                //     return $row->is_confidential
                //         ? '<span class="badge badge-danger"><i class="fas fa-lock"></i> Rahasia</span>'
                //         : '<span class="badge badge-info"><i class="fas fa-unlock"></i> Publik</span>';
                // })
                // ->addColumn('action', function ($row) {
                //     $actionBtn = '<div class="btn-group" role="group">
                //         <button type="button" class="btn btn-info btn-sm" onclick="viewDocument(' . $row->id . ')">
                //             <i class="fas fa-eye"></i> Lihat
                //         </button>
                //         <button type="button" class="btn btn-warning btn-sm" onclick="editDocument(' . $row->id . ')">
                //             <i class="fas fa-edit"></i> Edit
                //         </button>
                //         <button type="button" class="btn btn-danger btn-sm" onclick="deleteDocument(' . $row->id . ')">
                //             <i class="fas fa-trash"></i> Hapus
                //         </button>
                //     </div>';
                //     return $actionBtn;
                // })
                ->rawColumns(['is_read'])
                ->make(true);
        }
    }

    public function suratKeluarStats(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();

            // Direktur (bukan super admin): stats disposisi yang dikirim oleh direktur
            if ($user->hasRole('direktur') && !$user->hasRole('super admin')) {
                $baseQuery = Disposisi::where('created_by', $user->id);

                return response()->json([
                    'total' => (clone $baseQuery)->count(),
                    'dibaca' => (clone $baseQuery)->where('status', \App\Enums\DisposisiStatus::Selesai)->count(),
                    'belum_dibaca' => (clone $baseQuery)->where('status', \App\Enums\DisposisiStatus::Diproses)->count(),
                    'hari_ini' => (clone $baseQuery)->whereDate('created_at', today())->count(),
                ]);
            }

            $baseQuery = Document::where('is_letter', true);

            $total = (clone $baseQuery)->count();

            // Query dengan whereHas untuk relasi hasOne
            $dibaca = (clone $baseQuery)
                ->whereHas('shares', function ($query) {
                    $query->where('is_read', true);
                })
                ->count();

            // Atau untuk dokumen yang belum punya shares sama sekali
            $belumDibacaTotal = (clone $baseQuery)
                ->where(function ($query) {
                    $query->whereHas('shares', function ($subQuery) {
                        $subQuery->where('is_read', false);
                    })->orWhereDoesntHave('shares');
                })
                ->count();

            $hariIni = (clone $baseQuery)
                ->whereDate('created_at', today())
                ->count();

            return response()->json([
                'total' => $total,
                'dibaca' => $dibaca,
                'belum_dibaca' => $belumDibacaTotal,
                'hari_ini' => $hariIni
            ]);
        }
    }

    public function kirimSurat()
    {
        // Penerima: user ber-role super admin ATAU user is_legal = true (non super admin)
        $recipients = User::where(function ($q) {
            $q->role('super admin')
                ->orWhere('is_legal', true);
        })
            ->with('unit')
            ->orderBy('name')
            ->get();

        return view('surat.staff.index', compact('recipients'));
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $userUnitId = Auth::user()->unit_id;

            $data = Surat::with(['user', 'recipients'])
                ->whereHas('user', function ($query) use ($userUnitId) {
                    $query->where('unit_id', $userUnitId);
                })
                ->accessibleTo(Auth::user())
                ->select('*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('laporan_dibaca', function ($row) {
                    $total = $row->recipients->count();

                    if ($total > 0) {
                        $read = $row->recipients->filter(fn($r) => !is_null($r->pivot->read_at))->count();

                        return $read === $total
                            ? '<span class="badge badge-success"><i class="fas fa-check"></i> Dibaca (' . $read . '/' . $total . ')</span>'
                            : '<span class="badge badge-warning"><i class="fas fa-times"></i> Belum Dibaca (' . $read . '/' . $total . ')</span>';
                    }

                    return $row->is_read
                        ? '<span class="badge badge-success"><i class="fas fa-check"></i> Dibaca</span>'
                        : '<span class="badge badge-warning"><i class="fas fa-times"></i> Belum Dibaca</span>';
                })
                ->addColumn('waktu_dibaca', function ($row) {
                    $readTimes = $row->recipients
                        ->map(fn($r) => $r->pivot->read_at)
                        ->filter()
                        ->map(fn($readAt) => Carbon::parse($readAt));

                    if ($readTimes->isNotEmpty()) {
                        return $readTimes->max()->format('d-m-Y H:i:s');
                    }

                    return $row->read_at ? Carbon::parse($row->read_at)->format('d-m-Y H:i:s') : '-';
                })
                ->addColumn('tanggal_dikirim', function ($row) {
                    return $row->created_at->format('d-m-Y H:i:s');
                })
                ->addColumn('action', function ($row) {
                    $downloadBtn = '';
                    if ($row->file) {
                        $downloadBtn = '<a href="' . route('kirim-surat.download', $row->id) . '" class="btn btn-success btn-sm" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>';
                    }

                    $forwardBtn = '<button type="button" class="btn btn-info btn-sm" onclick="forwardSurat(' . $row->id . ')" title="Teruskan ke Super Admin">
                        <i class="fas fa-share"></i>
                    </button>';

                    return '
                <div class="btn-group">
                    ' . $downloadBtn . '
                    <a href="' . route('surat.view', $row->id) . '" class="btn btn-info btn-sm" title="Lihat Surat">
                        <i class="fas fa-eye"></i>
                    </a>
                    ' . $forwardBtn . '
                    <button type="button" class="btn btn-warning btn-sm" onclick="editSurat(' . $row->id . ')" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteSurat(' . $row->id . ')" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>';
                })
                ->rawColumns(['laporan_dibaca', 'action'])
                ->make(true);
        }
    }

    // public function suratMasukStaff(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $data = Document::where('is_letter', true);
    //     }
    // }

    public function store(Request $request)
    {
        $maxSizeMb = config('documents.max_upload_size_mb');

        $request->validate([
            'no_surat' => 'required|unique:surats',
            'perihal' => 'required',
            'keterangan' => 'nullable',
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:' . ($maxSizeMb * 1024)],
            'needs_disposisi' => 'nullable|boolean',
            'disposisi_no_agenda' => 'nullable|string|max:255',
            'disposisi_tgl_naskah' => 'nullable|date',
            'disposisi_masuk_tu' => 'nullable',
            'disposisi_tgl_no_naskah' => 'nullable|string|max:255',
            'disposisi_asal_naskah' => 'nullable|string|max:255',
            'disposisi_informasi_naskah' => 'nullable|string',
            'recipient_ids' => 'required|array|min:1',
            'recipient_ids.*' => 'exists:users,id',
        ], [
            'file.max' => 'File :input melebihi batas maksimal ' . $maxSizeMb . 'MB.',
        ]);

        $data = [
            'user_id' => Auth::user()->id,
            'no_surat' => $request->no_surat,
            'perihal' => $request->perihal,
            'keterangan' => $request->keterangan,
            'is_read' => false,
            'needs_disposisi' => $request->boolean('needs_disposisi'),
            'disposisi_no_agenda' => $request->input('disposisi_no_agenda'),
            'disposisi_tgl_naskah' => $request->input('disposisi_tgl_naskah'),
            'disposisi_masuk_tu' => $request->input('disposisi_masuk_tu'),
            'disposisi_tgl_no_naskah' => $request->input('disposisi_tgl_no_naskah'),
            'disposisi_asal_naskah' => $request->input('disposisi_asal_naskah'),
            'disposisi_informasi_naskah' => $request->input('disposisi_informasi_naskah'),
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('surat', $fileName, 'public');
            $data['file'] = $filePath;
        }

        // Buat surat baru
        $surat = DB::transaction(function () use ($data) {
            return Surat::create($data);
        });

        // Kirim ke user terpilih (super admin ATAU is_legal = true)
        $recipients = User::where(function ($q) {
            $q->role('super admin')
                ->orWhere('is_legal', true);
        })
            ->whereIn('id', $request->recipient_ids)
            ->get();

        // Catat penerima sebagai accessor surat privat
        $surat->recipients()->sync($recipients->pluck('id'));

        // Broadcast event dengan data surat dan users
        broadcast(new SuratCreate($surat, $recipients));

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new SuratNotification($surat, 'surat_masuk', Auth::user()));
        }

        return response()->json([
            'success' => true,
            'message' => 'Surat berhasil dikirim',
            'data' => $surat
        ]);
    }

    public function forwardToSuperAdmin($id)
    {
        $surat = Surat::findOrFail($id);

        if (!Gate::allows('view', $surat)) {
            abort(403, 'Anda tidak memiliki akses ke surat ini.');
        }

        $superAdmins = User::role('super admin')->get();

        if ($superAdmins->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada user super admin yang dapat dipilih.',
            ], 422);
        }

        $newRecipients = $superAdmins->reject(
            fn(User $admin) => $surat->isRecipient($admin)
        );

        $surat->recipients()->syncWithoutDetaching($newRecipients->pluck('id'));

        if ($newRecipients->isNotEmpty()) {
            broadcast(new SuratCreate($surat->fresh(), $newRecipients));
            Notification::send($newRecipients, new SuratNotification($surat, 'surat_masuk', Auth::user()));
        }

        return response()->json([
            'success' => true,
            'message' => $newRecipients->isEmpty()
                ? 'Surat sudah diteruskan ke super admin.'
                : 'Surat berhasil diteruskan ke ' . $newRecipients->count() . ' super admin.',
        ]);
    }

    public function show($id)
    {
        $surat = Surat::with('user')->findOrFail($id);

        $this->authorize('view', $surat);

        return response()->json([
            'success' => true,
            'data' => $surat
        ]);
    }

    public function update(Request $request, $id)
    {
        $surat = Surat::findOrFail($id);

        $this->authorize('update', $surat);

        $maxSizeMb = config('documents.max_upload_size_mb');

        $request->validate([
            'no_surat' => 'required|unique:surats,no_surat,' . $id,
            'perihal' => 'required',
            'keterangan' => 'nullable',
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:' . ($maxSizeMb * 1024)],
        ], [
            'file.max' => 'File :input melebihi batas maksimal ' . $maxSizeMb . 'MB.',
        ]);

        $data = [
            'no_surat' => $request->no_surat,
            'perihal' => $request->perihal,
            'keterangan' => $request->keterangan,
        ];

        if ($request->hasFile('file')) {
            // Delete old file
            if ($surat->file) {
                Storage::disk('public')->delete($surat->file);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('surat', $fileName, 'public');
            $data['file'] = $filePath;
        }

        $surat->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Surat berhasil diupdate'
        ]);
    }

    public function destroy($id)
    {
        $surat = Surat::findOrFail($id);

        $this->authorize('delete', $surat);

        // Delete file if exists
        if ($surat->file) {
            Storage::disk('public')->delete($surat->file);
        }

        $surat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Surat berhasil dihapus'
        ]);
    }

    public function download($id)
    {
        try {
            $surat = Surat::find($id);

            $this->authorize('canDownload', $surat);

            $user = Auth::user();

            if (!$surat->file) {
                abort(404, 'File tidak ditemukan');
            }

            $filePath = storage_path('app/public/' . $surat->file);

            if (!file_exists($filePath)) {
                abort(404, 'File tidak ditemukan di server');
            }

            // Mark as read oleh pembuka (per penerima)
            $surat->markAsReadBy($user);

            // Get original filename without timestamp prefix
            $originalName = preg_replace('/^\d+_/', '', basename($surat->file));

            return response()->download($filePath, $originalName);
        } catch (\Exception $e) {
            Log::error('Error downloading file: ' . $e->getMessage());
            abort(500, 'Error downloading file');
        }
    }

    /**
     * Get unread notifications count for bell icon
     */
    public function getUnreadCount()
    {
        $unreadCount = Surat::whereHas('recipients', function ($q) {
            $q->where('users.id', Auth::id())
                ->whereNull('surat_recipients.read_at');
        })->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Get recent notifications for dropdown
     */
    public function getNotifications(Request $request)
    {
        $limit = $request->get('limit', 10);

        $notifications = Surat::with(['user', 'recipients'])
            ->orderBy('created_at', 'desc')
            ->whereHas('recipients', function ($q) {
                $q->where('users.id', Auth::id())
                    ->whereNull('surat_recipients.read_at');
            })
            ->limit($limit)
            ->get()
            ->map(function ($surat) {
                $recipient = $surat->recipients->first()->pivot ?? null;
                return [
                    'id' => $surat->id,
                    'title' => 'Surat Masuk Baru',
                    'message' => $surat->perihal,
                    'sender' => $surat->user->name ?? 'Unknown',
                    'no_surat' => $surat->no_surat,
                    'created_at' => $surat->created_at,
                    'time_ago' => $surat->created_at->diffForHumans(),
                    'is_read' => !is_null($recipient?->read_at),
                    'url' => route('surat.view', $surat->id) ?? ''
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead($id)
    {
        $surat = Surat::findOrFail($id);

        $this->authorize('view', $surat);

        $surat->markAsReadBy(Auth::user());

        if ($surat->user_id) {
            event(new \App\Events\SuratReaded($surat, auth()->user()));
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $surats = Surat::with('recipients')
            ->whereHas('recipients', fn($q) => $q->where('users.id', Auth::id()))
            ->get();

        Surat::whereHas('recipients', fn($q) => $q->where('users.id', Auth::id()))
            ->get()
            ->each(fn($surat) => $surat->recipients()
                ->updateExistingPivot(Auth::id(), ['read_at' => now()]));

        foreach ($surats as $surat) {
            if ($surat->user_id) {
                event(new \App\Events\SuratReaded($surat, auth()->user()));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    public function viewFile(string $id)
    {
        $surat = Surat::find($id);
        $user = Auth::user();

        $this->authorize('canDownload', $surat);

        $filePath = Storage::disk('public')->path($surat->file);

        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan');
        }

        $fileExtension = strtolower(pathinfo($surat->file, PATHINFO_EXTENSION));
        $mimeType = $this->getMimeType($fileExtension);

        $docxHtml = null;
        if (in_array($fileExtension, ['docx', 'doc'])) {
            $docxHtml = $this->convertDocxToHtml($surat->id);
        }

        // Mark as read oleh pembuka (per penerima)
        $surat->markAsReadBy($user);

        return view('view-file', compact('surat', 'fileExtension', 'docxHtml'));
    }

    public function streamFile(string $id)
    {
        $surat = Surat::find($id);
        $user = Auth::user();

        $this->authorize('canDownload', $surat);

        $filePath = Storage::disk('public')->path($surat->file);

        if (!file_exists($filePath)) {
            abort(404);
        }

        $fileExtension = strtolower(pathinfo($surat->file, PATHINFO_EXTENSION));
        $mimeType = $this->getMimeType($fileExtension);

        // Set headers to prevent download
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $surat->file . '"',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    private function getMimeType($extension)
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc' => 'application/msword',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Convert DOCX to HTML
     */
    private function convertDocxToHtml(string $id)
    {
        $surat = Surat::find($id);
        $filePath = storage_path('app/public/' . $surat->file);
        $cacheFile = storage_path('app/public/docx_cache/' . $surat->id . '.html');

        // Check if HTML cache exists and is newer than original file
        if (file_exists($cacheFile) && filemtime($cacheFile) > filemtime($filePath)) {
            return file_get_contents($cacheFile);
        }

        try {
            // Create cache directory
            $cacheDir = dirname($cacheFile);
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }

            // Load DOCX file
            $phpWord = IOFactory::load($filePath);

            // Convert to HTML
            $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');

            // Save to cache
            $htmlWriter->save($cacheFile);

            // Read and clean HTML
            $html = file_get_contents($cacheFile);

            // Clean up HTML (remove unwanted styles, scripts)
            $html = $this->cleanDocxHtml($html);

            // Save cleaned HTML
            file_put_contents($cacheFile, $html);

            return $html;
        } catch (\Exception $e) {
            Log::error('DOCX to HTML conversion failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Clean DOCX HTML output
     */
    private function cleanDocxHtml($html)
    {
        // Remove head section and keep only body content
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
            $html = $matches[1];
        }

        // Clean up inline styles (optional)
        $html = preg_replace('/style="[^"]*"/i', '', $html);

        // Add custom styling
        $html = '<div class="docx-content" style="
        font-family: Arial, sans-serif;
        line-height: 1.6;
        color: #333;
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        ">' . $html . '</div>';

        return $html;
    }

    /**
     * Serve DOCX as HTML
     */
    public function viewDocxHtml(string $id)
    {
        $surat = Surat::find($id);

        $this->authorize('canDownload', $surat);

        $cacheFile = storage_path('app/public/docx_cache/' . $surat->id . '.html');

        if (!file_exists($cacheFile)) {
            $html = $this->convertDocxToHtml($surat->id);
            if (!$html) {
                abort(404, 'Tidak dapat mengkonversi dokumen');
            }
        } else {
            $html = file_get_contents($cacheFile);
        }

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }

    public function suratMasukStaff(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();

            // Ambil semua dokumen surat masuk aktif
            $allDocuments = Document::with([
                'folder',
                'category',
                'creator',
                'permissions'
            ])
                ->where('is_letter', true)
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->get();

            // Filter dokumen berdasarkan permission menggunakan helper method
            // $documents = $allDocuments->filter(function ($document) use ($user) {
            //     return app(PermissionService::class)->canAccessDocument($user, $document, 'read');
            // });

            // OPSI 1: Filter dokumen yang bisa read ATAU download (OR logic)
            $documents = $allDocuments->filter(function ($document) use ($user) {
                return app(PermissionService::class)
                    ->canAccessDocument($user, $document, ['read', 'download']);
            });

            // OPSI 2: Filter dokumen yang bisa read DAN download (AND logic)
            // $documents = $allDocuments->filter(function ($document) use ($user) {
            //     return app(PermissionService::class)
            //         ->canAccessDocumentWithAllActions($user, $document, ['read', 'download']);
            // });

            // OPSI 3: Filter hanya yang bisa read saja
            // $documents = $allDocuments->filter(function ($document) use ($user) {
            //     return app(PermissionService::class)
            //         ->canAccessDocument($user, $document, 'read');
            // });

            $docRows = $documents->values()->map(function ($document) {
                return [
                    'source_type' => 'document',
                    'source_id' => $document->id,
                    'document_number' => $document->document_number ?? '-',
                    'title' => $document->title,
                    'title_suffix' => $document->is_confidential
                        ? ' <span class="badge badge-danger ml-1">Confidential</span>'
                        : '',
                    'category' => $document->category ? $document->category->name : '-',
                    'tanggal' => $document->created_at->format('Y-m-d H:i'),
                    'created_at_raw' => $document->created_at,
                    'creator' => $document->creator ? $document->creator->name : '-',
                    'is_read' => (bool) ($document->shares && $document->shares->is_read),
                    'has_file' => !empty($document->file_path),
                    'file_name' => $document->file_name ?? ($document->file_path ? basename($document->file_path) : '-'),
                    'file_size' => $document->formatted_file_size ?? null,
                ];
            });

            // Surat kiriman yang ditujukan ke user login (kotak masuk penerima)
            $receivedSurats = Surat::with(['user', 'recipients'])
                ->whereHas('recipients', fn($q) => $q->where('users.id', $user->id))
                ->orderByDesc('created_at')
                ->get();

            $suratRows = $receivedSurats->map(function ($surat) use ($user) {
                $recipient = $surat->recipients->firstWhere('id', $user->id);

                return [
                    'source_type' => 'surat',
                    'source_id' => $surat->id,
                    'document_number' => $surat->no_surat,
                    'title' => $surat->perihal,
                    'title_suffix' => '',
                    'category' => '-',
                    'tanggal' => $surat->created_at->format('Y-m-d H:i'),
                    'created_at_raw' => $surat->created_at,
                    'creator' => $surat->user ? $surat->user->name : '-',
                    'is_read' => !is_null($recipient?->pivot?->read_at),
                    'has_file' => !empty($surat->file),
                    'file_name' => $surat->file ? basename($surat->file) : '-',
                    'file_size' => null,
                ];
            });

            $combined = $docRows->toBase()->merge($suratRows)->sortByDesc('created_at_raw')->values();

            return DataTables::of($combined)
                ->addIndexColumn()
                ->addColumn('tipe', function ($row) {
                    return $row['source_type'] === 'surat'
                        ? '<span class="badge badge-primary">Surat</span>'
                        : '<span class="badge badge-info">Dokumen</span>';
                })
                ->addColumn('title', function ($row) {
                    return $row['title'] . ($row['title_suffix'] ?? '');
                })
                ->addColumn('status', function ($row) {
                    return $row['is_read']
                        ? '<span class="badge badge-success"><i class="fas fa-check"></i> Dibaca</span>'
                        : '<span class="badge badge-warning"><i class="fas fa-times"></i> Belum Dibaca</span>';
                })
                ->addColumn('file_info', function ($row) {
                    if (!$row['has_file']) return '-';
                    $size = $row['file_size']
                        ? '<div class="text-muted">' . $row['file_size'] . '</div>'
                        : '';
                    return '<div class="text-sm">' .
                        '<div>' . e($row['file_name']) . '</div>' .
                        $size .
                        '</div>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group" role="group">';
                    if ($row['source_type'] === 'surat') {
                        $btn .= '<a href="' . route('surat.view', $row['source_id']) . '"
                         class="btn btn-info btn-sm" title="Lihat Surat">
                         <i class="fas fa-eye"></i>
                         </a>';
                        if ($row['has_file']) {
                            $btn .= '<a href="' . route('kirim-surat.download', $row['source_id']) . '"
                             class="btn btn-success btn-sm" title="Download">
                             <i class="fas fa-download"></i>
                             </a>';
                        }
                        $btn .= '<button type="button" class="btn btn-warning btn-sm" onclick="forwardSurat(' . $row['source_id'] . ')" title="Teruskan ke Super Admin">
                         <i class="fas fa-share"></i>
                         </button>';
                    } else {
                        $btn .= '<a href="' . route('documents.view-file', $row['source_id']) . '"
                         class="btn btn-info btn-sm" title="View" target="_blank">
                         <i class="fas fa-eye"></i>
                         </a>';
                        $btn .= '<a href="' . route('documents.download', $row['source_id']) . '"
                         class="btn btn-success btn-sm" title="Download">
                         <i class="fas fa-download"></i>
                         </a>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['tipe', 'title', 'status', 'file_info', 'action'])
                ->make(true);
        }

        return view('surat.staff.surat-masuk');
    }
}
