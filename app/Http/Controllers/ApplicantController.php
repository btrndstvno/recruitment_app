<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ApplicantController extends Controller
{
    /**
     * Display a listing of the applicants.
     */
    public function index(Request $request)
    {
        $query = Applicant::query();

        // Filter archived by default (show non-archived, or show archived only)
        if ($request->filled('archived') && $request->archived == '1') {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('no_hp_1', 'like', "%{$search}%")
                  ->orWhere('no_hp_2', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('kota', 'like', "%{$search}%")
                  ->orWhere('provinsi', 'like', "%{$search}%")
                  ->orWhere('no_ktp', 'like', "%{$search}%")
                  ->orWhere('nama_sekolah', 'like', "%{$search}%")
                  ->orWhere('jurusan', 'like', "%{$search}%");
            });
        }

        // Type filter (Guru/PKL/eguler)
        if ($request->filled('tipe')) {
            if ($request->tipe === 'guru') {
                $query->where('is_guru', true);
            } elseif ($request->tipe === 'pkl') {
                $query->where('is_pkl', true);
            } elseif ($request->tipe === 'reguler') {
                $query->where('is_guru', false)->where('is_pkl', false);
            } 

            
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date filter - flexible: tanggal spesifik, bulan, atau tahun
        if ($request->filled('tanggal')) {
            // Filter by specific date
            $query->whereDate('tanggal_lamaran', $request->tanggal);
        } else {
            // Filter by month if provided
            if ($request->filled('bulan')) {
                $query->whereMonth('tanggal_lamaran', $request->bulan);
            }
            // Filter by year if provided
            if ($request->filled('tahun')) {
                $query->whereYear('tanggal_lamaran', $request->tahun);
            }
        }
        // Logika Sorting Dinamis
        $sortColumn = $request->get('sort', 'nama_lengkap'); // Default column
        $sortDirection = $request->get('direction', 'asc'); // Default direction

        // Whitelist kolom yang diizinkan untuk mencegah error/sql injection
        $allowedColumns = ['nama_lengkap', 'tanggal_lamaran', 'created_at'];

        if (in_array($sortColumn, $allowedColumns)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('nama_lengkap', 'asc');
        }

        $applicants = $query->paginate(10)->appends($request->query());

        return view('applicants.index', compact('applicants'));
    }

    /**
     * menunjukan form untuk membuat pelamar baru.
     */
    public function create()
    {
        return view('applicants.create');
    }

    /**
     * store applicant yang baru dibuat
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'alamat' => 'required|string',
            'kota' => 'required|string|max:255',
            'provinsi' => 'required|string|max:255',
            'no_hp_1' => 'required|string|max:20',
            'no_hp_2' => 'nullable|string|max:20',
            'no_ktp' => 'required|digits:16',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'umur' => 'required|integer|min:15|max:100',
            'tanggal_lamaran' => 'required|date',
            'nama_sekolah' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'tahun_lulus' => 'required|integer|min:1950',
            'ipk' => 'nullable|numeric|min:0|max:4',
            'is_guru' => 'boolean',
            'is_pkl' => 'boolean',
            'pkl_awal' => 'nullable|required_if:is_pkl,1|date',
            'pkl_akhir' => 'nullable|required_if:is_pkl,1|date|after_or_equal:pkl_awal',
            'pkl_asal_sekolah' => 'nullable|required_if:is_pkl,1|string|max:255',
            'pkl_jurusan' => 'nullable|required_if:is_pkl,1|string|max:255',
            'pkl_tempat' => 'nullable|required_if:is_pkl,1|string|max:255',
            'catatan' => 'nullable|string',
        ],[
            'no_ktp.digits' => 'No. KTP harus terdiri dari 16 digit angka.',
        ]);

        // Cek apakah pelamar dengan No KTP yang sama pernah ditolak dalam 1 tahun terakhir
        $rejectedApplicant = Applicant::where('no_ktp', $validated['no_ktp'])
            ->where('status', 'rejected')
            ->where('updated_at', '>=', Carbon::now()->subYear())
            ->first();

        if ($rejectedApplicant) {
            $canApplyAgain = Carbon::parse($rejectedApplicant->updated_at)->addYear();
            $remainingDays = (int) ceil(Carbon::now()->diffInDays($canApplyAgain, false)); //int agar tidak ada desimal (pembulatan keatas)
            
            return redirect()->back()
                ->withInput()
                ->with('error', "Pelamar dengan No. KTP {$validated['no_ktp']} pernah ditolak pada " . 
                    $rejectedApplicant->updated_at->format('d M Y') . 
                    ". Dapat melamar kembali pada " . $canApplyAgain->format('d M Y') . 
                    " ({$remainingDays} hari lagi).");
        }

        // Cek apakah ada lamaran aktif (pending/tested) dengan No KTP yang sama
        $activeApplicant = Applicant::where('no_ktp', $validated['no_ktp'])
            ->whereIn('status', ['pending', 'tested'])
            ->first();

        if ($activeApplicant) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Pelamar dengan No. KTP {$validated['no_ktp']} sudah memiliki lamaran aktif dengan status: " . 
                    ucfirst($activeApplicant->status) . ".");
        }

        $validated['is_guru'] = $request->has('is_guru');
        $validated['is_pkl'] = $request->has('is_pkl');

        Applicant::create($validated);

        return redirect()->route('applicants.index')
            ->with('success', 'Data pelamar berhasil ditambahkan!');
    }

    /**
     * Display the specified applicant.
     */
    public function show(Applicant $applicant)
    {
        $applicant->load('psikotestReport');
        return view('applicants.show', compact('applicant'));
    }

    /**
     * Show the form for editing the specified applicant.
     */
    public function edit(Applicant $applicant)
    {
        return view('applicants.edit', compact('applicant'));
    }

    /**
     * Update the specified applicant in storage.
     */
    public function update(Request $request, Applicant $applicant)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'alamat' => 'required|string',
            'kota' => 'required|string|max:255',
            'provinsi' => 'required|string|max:255',
            'no_hp_1' => 'required|string|max:20',
            'no_hp_2' => 'nullable|string|max:20',
            'no_ktp' => 'required|digits:16|unique:applicants,no_ktp,' . $applicant->id,
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'umur' => 'required|integer|min:15|max:100',
            'tanggal_lamaran' => 'required|date',
            'nama_sekolah' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'tahun_lulus' => 'required|integer|min:1950',
            'ipk' => 'nullable|numeric|min:0|max:4',
            'is_guru' => 'boolean',
            'is_pkl' => 'boolean',
            'pkl_awal' => 'nullable|required_if:is_pkl,1|date',
            'pkl_akhir' => 'nullable|required_if:is_pkl,1|date|after_or_equal:pkl_awal',
            'pkl_asal_sekolah' => 'nullable|required_if:is_pkl,1|string|max:255',
            'pkl_jurusan' => 'nullable|required_if:is_pkl,1|string|max:255',
            'pkl_tempat' => 'nullable|required_if:is_pkl,1|string|max:255',
            'catatan' => 'nullable|string',
            'status' => 'required|in:pending,tested,accepted,rejected',
        ], [
            'no_ktp.unique' => 'No. KTP ini sudah digunakan oleh pelamar lain.',
            'no_ktp.digits' => 'No. KTP harus terdiri dari 16 digit angka.',
        ]);

        $validated['is_guru'] = $request->has('is_guru');
        $validated['is_pkl'] = $request->has('is_pkl');

        $applicant->update($validated);

        // Preserve query parameters (page, filters) when redirecting
        $queryParams = $request->only(['page', 'search', 'status', 'tipe', 'tanggal', 'bulan', 'tahun']);
        
        return redirect()->route('applicants.show', array_merge(['applicant' => $applicant], $queryParams))
            ->with('success', 'Data pelamar berhasil diperbarui!');
    }

    /**
     * Remove the specified applicant from storage.
     */
    public function destroy(Applicant $applicant)
    {
        $applicant->delete();

        return redirect()->route('applicants.index')
            ->with('success', 'Data pelamar berhasil dihapus!');
    }

    /**
     * Update the status of the specified applicant.
     */
    public function updateStatus(Request $request, Applicant $applicant)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,tested,accepted,rejected',
        ]);

        $oldStatus = $applicant->status;
        $applicant->update(['status' => $validated['status']]);

        $statusLabels = [
            'pending' => 'Pending',
            'tested' => 'Sudah Test',
            'accepted' => 'Diterima',
            'rejected' => 'Ditolak',
        ];

        $message = "Status pelamar berhasil diubah dari {$statusLabels[$oldStatus]} menjadi {$statusLabels[$validated['status']]}!";

        return redirect()->route('applicants.show', $applicant)
            ->with('success', $message);
    }

    /**
     * Bulk delete multiple applicants.
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:applicants,id',
        ]);

        try {
            $count = Applicant::whereIn('id', $validated['ids'])->delete();
            
            return response()->json([
                'success' => true,
                'message' => "{$count} data pelamar berhasil dihapus!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data.'
            ], 500);
        }
    }

    /**
     * Bulk archive multiple applicants.
     */
    public function bulkArchive(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:applicants,id',
        ]);

        try {
            $count = Applicant::whereIn('id', $validated['ids'])
                ->update(['archived_at' => now()]);
            
            return response()->json([
                'success' => true,
                'message' => "{$count} data pelamar berhasil diarsipkan!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengarsipkan data.'
            ], 500);
        }
    }

    /**
     * Bulk unarchive multiple applicants.
     */
    public function bulkUnarchive(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:applicants,id',
        ]);

        try {
            $count = Applicant::whereIn('id', $validated['ids'])
                ->update(['archived_at' => null]);
            
            return response()->json([
                'success' => true,
                'message' => "{$count} data pelamar berhasil dipulihkan dari arsip!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memulihkan data.'
            ], 500);
        }
    }

    /**
     * Get all applicant IDs based on current filters.
     */
    public function getAllIds(Request $request)
    {
        $query = Applicant::query();

        // Filter archived
        if ($request->filled('archived') && $request->archived == '1') {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('no_hp_1', 'like', "%{$search}%")
                  ->orWhere('no_hp_2', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('kota', 'like', "%{$search}%")
                  ->orWhere('provinsi', 'like', "%{$search}%")
                  ->orWhere('no_ktp', 'like', "%{$search}%")
                  ->orWhere('nama_sekolah', 'like', "%{$search}%")
                  ->orWhere('jurusan', 'like', "%{$search}%");
            });
        }

        // Type filter
        if ($request->filled('tipe')) {
            if ($request->tipe === 'guru') {
                $query->where('is_guru', true);
            } elseif ($request->tipe === 'pkl') {
                $query->where('is_pkl', true);
            } elseif ($request->tipe === 'reguler') {
                $query->where('is_guru', false)->where('is_pkl', false);
            }
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date filters
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_lamaran', $request->tanggal);
        } else {
            if ($request->filled('bulan')) {
                $query->whereMonth('tanggal_lamaran', $request->bulan);
            }
            if ($request->filled('tahun')) {
                $query->whereYear('tanggal_lamaran', $request->tahun);
            }
        }

        $ids = $query->pluck('id')->toArray();

        return response()->json([
            'success' => true,
            'ids' => $ids,
            'count' => count($ids)
        ]);
    }
}
