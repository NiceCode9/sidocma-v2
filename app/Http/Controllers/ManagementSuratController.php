<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Surat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
            $data = Surat::with('user')->select('*');

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
                        <button type="button" class="btn btn-info btn-sm" onclick="viewSurat(' . $row->id . ')">
                            <i class="fas fa-eye"></i> Lihat
                        </button>
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
            $data = Document::with(['creator', 'category'])
                ->where('is_letter', true)
                ->select('*');

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
            $data = Surat::with('user')->select('*');

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
                            <button type="button" class="btn btn-info btn-sm" onclick="viewSurat(' . $row->id . ')" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </button>
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

        Surat::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Surat berhasil dikirim'
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
        $surat = Surat::findOrFail($id);

        if (!$surat->file) {
            abort(404, 'File tidak ditemukan');
        }

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
    }
}
