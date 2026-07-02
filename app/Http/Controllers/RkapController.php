<?php

namespace App\Http\Controllers;

use App\Exports\RkapExport;
use App\Services\RkapRepository;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class RkapController extends Controller
{
    public function __construct(private RkapRepository $repo) {}

    // ── Halaman utama (routing per role) ──────────────────────────────────────

    public function index(Request $request): View
    {
        $tahun = (int) $request->input('tahun', now()->year);
        $nik   = (string) session('nik');

        return match (Role::current()) {
            Role::MANAGER    => $this->managerIndex($nik, $tahun),
            Role::GM         => $this->gmIndex($nik, $tahun),
            Role::DIREKSI    => $this->direksiIndex($tahun),
            default          => $this->adminIndex($tahun),
        };
    }

    private function managerIndex(string $nik, int $tahun): View
    {
        return view('rkap.manager', [
            'rkaps'   => $this->repo->listByManager($nik, $tahun),
            'tahun'   => $tahun,
            'years'   => $this->yearRange(),
        ]);
    }

    private function gmIndex(string $nik, int $tahun): View
    {
        return view('rkap.gm', [
            'rkaps' => $this->repo->listForGm($nik, $tahun),
            'tahun' => $tahun,
            'years' => $this->yearRange(),
        ]);
    }

    private function direksiIndex(int $tahun): View
    {
        return view('rkap.direksi', [
            'rkaps' => $this->repo->listForDireksi($tahun),
            'tahun' => $tahun,
            'years' => $this->yearRange(),
        ]);
    }

    private function adminIndex(int $tahun): View
    {
        return view('rkap.admin', [
            'rkaps' => $this->repo->listAll($tahun),
            'tahun' => $tahun,
            'years' => $this->yearRange(),
        ]);
    }

    // ── Form: buat RKAP baru ─────────────────────────────────────────────────

    public function create(): View
    {
        return view('rkap.form', [
            'rkap'    => null,
            'details' => [],
            'tahun'   => now()->year,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'produk'          => ['required', 'string', 'max:200'],
            'tahun'           => ['required', 'integer', 'min:2020', 'max:2100'],
            'qty'             => ['nullable', 'array'],
            'nilai'           => ['nullable', 'array'],
        ]);

        $months = $this->buildMonths($request);
        $id = $this->repo->create(
            (string) session('nik'),
            (int) $request->input('tahun'),
            $request->string('produk')->trim()->toString(),
            $months
        );

        if ($request->input('action') === 'submit') {
            $this->repo->submit($id);
            return redirect()->route('rkap.index')->with('success', 'RKAP berhasil diajukan ke GM.');
        }

        return redirect()->route('rkap.index')->with('success', 'RKAP berhasil disimpan sebagai draft.');
    }

    public function edit(int $id): View
    {
        $rkap = $this->repo->find($id);
        abort_if(! $rkap, 404);
        abort_if($rkap->NIK_MANAGER !== (string) session('nik'), 403);
        abort_if(! in_array($rkap->STATUS, ['draft', 'gm_rejected', 'rejected']), 403, 'RKAP tidak dapat diubah dalam status ini.');

        return view('rkap.form', [
            'rkap'    => $rkap,
            'details' => $this->repo->detailsByMonth($id),
            'tahun'   => (int) $rkap->TAHUN,
        ]);
    }

    public function updateRkap(Request $request, int $id): RedirectResponse
    {
        $rkap = $this->repo->find($id);
        abort_if(! $rkap, 404);
        abort_if($rkap->NIK_MANAGER !== (string) session('nik'), 403);
        abort_if(! in_array($rkap->STATUS, ['draft', 'gm_rejected', 'rejected']), 403);

        $request->validate([
            'produk' => ['required', 'string', 'max:200'],
        ]);

        $months = $this->buildMonths($request);
        $this->repo->update($id, $request->string('produk')->trim()->toString(), $months);

        if ($request->input('action') === 'submit') {
            $this->repo->submit($id);
            return redirect()->route('rkap.index')->with('success', 'RKAP berhasil diajukan ulang ke GM.');
        }

        return redirect()->route('rkap.index')->with('success', 'RKAP berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $rkap = $this->repo->find($id);
        abort_if(! $rkap, 404);
        abort_if($rkap->NIK_MANAGER !== (string) session('nik') && ! Role::isSuperadmin(), 403);
        abort_if($rkap->STATUS !== 'draft', 403, 'Hanya draft yang bisa dihapus.');

        $this->repo->delete($id);
        return redirect()->route('rkap.index')->with('success', 'Draft RKAP dihapus.');
    }

    // ── GM: validasi ─────────────────────────────────────────────────────────

    public function gmValidate(Request $request, int $id): RedirectResponse
    {
        abort_if(! Role::isGm(), 403);

        $request->validate([
            'action'  => ['required', 'in:approve,reject'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $rkap = $this->repo->find($id);
        abort_if(! $rkap || $rkap->STATUS !== 'submitted', 404);

        $this->repo->gmValidate(
            $id,
            (string) session('nik'),
            $request->input('action'),
            (string) $request->input('catatan', '')
        );

        $msg = $request->input('action') === 'approve'
            ? 'RKAP disetujui dan diteruskan ke Direksi.'
            : 'RKAP ditolak. Manager dapat mengajukan ulang.';

        return redirect()->route('rkap.index')->with('success', $msg);
    }

    // ── Direksi: pengesahan ───────────────────────────────────────────────────

    public function direksiValidate(Request $request, int $id): RedirectResponse
    {
        abort_if(! Role::isDireksi(), 403);

        $request->validate([
            'action'  => ['required', 'in:approve,reject'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $rkap = $this->repo->find($id);
        abort_if(! $rkap || $rkap->STATUS !== 'gm_approved', 404);

        $this->repo->direksiValidate(
            $id,
            (string) session('nik'),
            $request->input('action'),
            (string) $request->input('catatan', '')
        );

        $msg = $request->input('action') === 'approve'
            ? 'RKAP telah disahkan.'
            : 'RKAP ditolak oleh Direksi.';

        return redirect()->route('rkap.index')->with('success', $msg);
    }

    // ── Detail RKAP (JSON, untuk modal) ──────────────────────────────────────

    public function detail(int $id): \Illuminate\Http\JsonResponse
    {
        $rkap    = $this->repo->find($id);
        $details = $this->repo->detailsByMonth($id);

        return response()->json(compact('rkap', 'details'));
    }

    // ── Export Excel ──────────────────────────────────────────────────────────

    public function export(int $id)
    {
        $data = $this->repo->exportData($id);
        $filename = 'RKAP_' . ($data['rkap']->PRODUK ?? $id) . '_' . ($data['rkap']->TAHUN ?? now()->year) . '.xlsx';
        return Excel::download(new RkapExport($data), $filename);
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    private function buildMonths(Request $request): array
    {
        $months = [];
        for ($b = 1; $b <= 12; $b++) {
            $months[$b] = [
                'qty'   => (float) ($request->input("qty.{$b}",   0)),
                'nilai' => (float) ($request->input("nilai.{$b}", 0)),
            ];
        }
        return $months;
    }

    private function yearRange(): array
    {
        $current = now()->year;
        return range($current - 2, $current + 2);
    }
}
