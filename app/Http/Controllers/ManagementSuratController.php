<?php

namespace App\Http\Controllers;

use App\Events\SuratCreate;
use App\Models\Document;
use App\Models\DocumentPermission;
use App\Models\Surat;
use App\Models\User;
use App\Notifications\SuratNotification;
use App\Services\PermissionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $data = Surat::with('user.unit')->select('*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('user_name', function ($row) {
                    return $row->user ? $row->user->name : '-';
                })
                ->addColumn('status', function ($row) {
                    if ($row->read_at) {
                        return '<span class="badge badge-success">Dibaca</span>';
                    } else {
                        return '<span class="badge badge-warning">Belum Dibaca</span>';
                    }
                })
                ->addColumn('file', function ($row) {
                    $downloadBtn = '';
                    if ($row->file) {
                        $downloadBtn = '<a href="' . route('kirim-surat.download', $row->id) . '" class="btn btn-success btn-sm" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>';
                    }
                    return $downloadBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<div class="btn-group" role="group">
                            <a href="' . route('surat.view', $row->id) . '" class="btn btn-info btn-sm" title="Lihat Surat">
                                <i class="fas fa-eye"></i> Lihat
                            </a>
                        <button type="button" class="btn btn-warning btn-sm" onclick="editSurat(' . $row->id . ')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteSurat(' . $row->id . ')">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>';
                    return $actionBtn;
                })
                ->rawColumns(['status', 'file', 'action'])
                ->make(true);
        }
    }

    public function suratMasukStats()
    {
        $totalSuratMasuk = Surat::count();
        $suratMasukDibaca = Surat::where('read_at', '!=', null)->count();
        $suratMasukBelumDibaca = Surat::where('read_at', null)->count();
        $suratMasukHariIni = Surat::whereDate('created_at', today())->count();

        return response()->json([
            'totalSuratMasuk' => $totalSuratMasuk,
            'suratMasukDibaca' => $suratMasukDibaca,
            'suratMasukBelumDibaca' => $suratMasukBelumDibaca,
            'suratMasukHariIni' => $suratMasukHariIni,
        ]);
    }

    public function suratKeluarData(Request $request)
    {
        if ($request->ajax()) {
            $data = Document::with(['creator', 'category', 'folder' => function ($query) {
                $query->select('id', 'name');
            }])
                ->where('is_letter', true)
                ->select('*')
                ->orderBy('created_at', 'desc');

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
            $total = Document::where('is_letter', true)->count();

            // Query dengan whereHas untuk relasi hasOne
            $dibaca = Document::where('is_letter', true)
                ->whereHas('shares', function ($query) {
                    $query->where('is_read', true);
                })
                ->count();

            $belumDibaca = Document::where('is_letter', true)
                ->whereHas('shares', function ($query) {
                    $query->where('is_read', false);
                })
                ->count();

            // Atau untuk dokumen yang belum punya shares sama sekali
            $belumDibacaTotal = Document::where('is_letter', true)
                ->where(function ($query) {
                    $query->whereHas('shares', function ($subQuery) {
                        $subQuery->where('is_read', false);
                    })->orWhereDoesntHave('shares');
                })
                ->count();

            $hariIni = Document::where('is_letter', true)
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
        return view('surat.staff.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $userUnitId = Auth::user()->unit_id;

            $data = Surat::with('user')
                ->whereHas('user', function ($query) use ($userUnitId) {
                    $query->where('unit_id', $userUnitId);
                })
                ->select('*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('laporan_dibaca', function ($row) {
                    return $row->is_read
                        ? '<span class="badge badge-success"><i class="fas fa-check"></i> Dibaca</span>'
                        : '<span class="badge badge-warning"><i class="fas fa-times"></i> Belum Dibaca</span>';
                })
                ->addColumn('waktu_dibaca', function ($row) {
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

                    return '
                <div class="btn-group">
                    ' . $downloadBtn . '
                    <a href="' . route('surat.view', $row->id) . '" class="btn btn-info btn-sm" title="Lihat Surat">
                        <i class="fas fa-eye"></i>
                    </a>
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
        $request->validate([
            'no_surat' => 'required|unique:surats',
            'perihal' => 'required',
            'keterangan' => 'nullable',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:5120'
        ]);

        $data = [
            'user_id' => Auth::user()->id,
            'no_surat' => $request->no_surat,
            'perihal' => $request->perihal,
            'keterangan' => $request->keterangan,
            'is_read' => false,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('surat', $fileName, 'public');
            $data['file'] = $filePath;
        }

        // Buat surat baru
        $surat = Surat::create($data);
        // $surat = Surat::first();
        // Ambil semua user dengan role super admin
        $superAdmins = User::role('super admin')->get();

        // Broadcast event dengan data surat dan users
        broadcast(new SuratCreate($surat, $superAdmins));

        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, new SuratNotification($surat, 'surat_masuk', Auth::user()));
        }

        return response()->json([
            'success' => true,
            'message' => 'Surat berhasil dikirim',
            'data' => $surat
        ]);
    }

    public function show($id)
    {
        $surat = Surat::with('user')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $surat
        ]);
    }

    public function update(Request $request, $id)
    {
        $surat = Surat::findOrFail($id);

        $request->validate([
            'no_surat' => 'required|unique:surats,no_surat,' . $id,
            'perihal' => 'required',
            'keterangan' => 'nullable',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:5120'
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

            if (!$surat->file) {
                abort(404, 'File tidak ditemukan');
            }

            // Mark as read jika user adalah super admin
            if (Auth::user()->hasRole('super admin')) {
                $surat->markAsRead();
            }

            $filePath = storage_path('app/public/' . $surat->file);

            if (!file_exists($filePath)) {
                abort(404, 'File tidak ditemukan di server');
            }

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
        // $unreadCount = Surat::where('is_read', false)->count();
        $unreadCount = Surat::whereNull('read_at')->count();

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

        $notifications = Surat::with('user')
            ->orderBy('created_at', 'desc')
            ->whereNull('read_at')
            ->limit($limit)
            ->get()
            ->map(function ($surat) {
                return [
                    'id' => $surat->id,
                    'title' => 'Surat Masuk Baru',
                    'message' => $surat->perihal,
                    'sender' => $surat->user->name ?? 'Unknown',
                    'no_surat' => $surat->no_surat,
                    'created_at' => $surat->created_at,
                    'time_ago' => $surat->created_at->diffForHumans(),
                    'is_read' => !is_null($surat->read_at),
                    'url' => ''
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
        $surat->markAsRead();

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
        $surats = Surat::with('users')->whereNull('read_at')->get();

        Surat::whereNull('read_at')
            ->update([
                'read_at' => now(),
                'opened_by' => Auth::user()->id
            ]);

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

        // Mark as read
        if ($user->hasRole('super admin')) {
            $surat->markAsRead();
        }

        return view('view-file', compact('surat', 'fileExtension', 'docxHtml'));
    }

    public function streamFile(string $id)
    {
        $surat = Surat::find($id);
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
        // dd($surat);
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

            return DataTables::of($documents)
                ->addIndexColumn()
                ->addColumn('document_number', function ($row) {
                    return $row->document_number ?? '-';
                })
                ->addColumn('title', function ($row) {
                    $confidential = $row->is_confidential
                        ? '<span class="badge badge-danger ml-1">Confidential</span>'
                        : '';
                    return $row->title . $confidential;
                })
                ->addColumn('category', function ($row) {
                    return $row->category ? $row->category->name : '-';
                })
                ->addColumn('file_info', function ($row) {
                    return '<div class="text-sm">' .
                        '<div>' . $row->file_name . '</div>' .
                        '<div class="text-muted">' . $row->formatted_file_size . '</div>' .
                        '</div>';
                })
                ->addColumn('creator', function ($row) {
                    return $row->creator ? $row->creator->name : '-';
                })
                ->addColumn('status', function ($row) {
                    if ($row->shares) {
                        return $row->shares->is_read
                            ? '<span class="badge badge-success"><i class="fas fa-check"></i> Dibaca</span>'
                            : '<span class="badge badge-warning"><i class="fas fa-times"></i> Belum Dibaca</span>';
                    }
                    return '-';
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at->format('d M Y H:i');
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('documents.view-file', $row->id) . '"
                     class="btn btn-info btn-sm" title="View" target="_blank">
                     <i class="fas fa-eye"></i>
                     </a>';
                    $btn .= '<a href="' . route('documents.download', $row->id) . '"
                     class="btn btn-success btn-sm" title="Download">
                     <i class="fas fa-download"></i>
                     </a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['title', 'status', 'file_info', 'action'])
                ->make(true);
        }

        return view('surat.staff.surat-masuk');
    }
}
