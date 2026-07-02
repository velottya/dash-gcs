<?php

namespace App\Http\Controllers;

use App\Exports\RkapExport;
use App\Services\RkapRepository;
use App\Support\Role;
use Illuminate\Http\JsonResponse;
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

    public function create(Request $request): View|RedirectResponse
    {
        $tahun = (int) $request->query('tahun', now()->year);
        $nik   = (string) session('nik');

        $existing = $this->repo->findByManagerAndTahun($nik, $tahun);
        if ($existing) {
            return redirect()->route('rkap.edit', $existing->ID)
                ->with('info', "Anda sudah punya pengajuan RKAP tahun {$tahun}. Silakan edit pengajuan yang ada.");
        }

        return view('rkap.form', [
            'rkap'     => null,
            'products' => [],
            'tahun'    => $tahun,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'tahun'               => ['required', 'integer', 'min:2020', 'max:2100'],
            'products'            => ['required', 'array', 'min:1'],
            'products.*.stockid'  => ['required', 'string', 'max:10'],
            'products.*.produk'   => ['required', 'string', 'max:200'],
            'products.*.qty'      => ['nullable', 'array'],
            'products.*.nilai'    => ['nullable', 'array'],
        ]);

        $nik   = (string) session('nik');
        $tahun = (int) $request->input('tahun');

        $existing = $this->repo->findByManagerAndTahun($nik, $tahun);
        if ($existing) {
            return redirect()->route('rkap.edit', $existing->ID)
                ->with('info', "Anda sudah punya pengajuan RKAP tahun {$tahun}. Silakan edit pengajuan yang ada.");
        }

        $id = $this->repo->createHeader($nik, $tahun);
        $this->repo->saveProducts($id, $this->buildProducts($request));

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
            'rkap'     => $rkap,
            'products' => $this->repo->productsGrouped($id),
            'tahun'    => (int) $rkap->TAHUN,
        ]);
    }

    public function updateRkap(Request $request, int $id): RedirectResponse
    {
        $rkap = $this->repo->find($id);
        abort_if(! $rkap, 404);
        abort_if($rkap->NIK_MANAGER !== (string) session('nik'), 403);
        abort_if(! in_array($rkap->STATUS, ['draft', 'gm_rejected', 'rejected']), 403);

        $request->validate([
            'products'            => ['required', 'array', 'min:1'],
            'products.*.stockid'  => ['required', 'string', 'max:10'],
            'products.*.produk'   => ['required', 'string', 'max:200'],
            'products.*.qty'      => ['nullable', 'array'],
            'products.*.nilai'    => ['nullable', 'array'],
        ]);

        $this->repo->saveProducts($id, $this->buildProducts($request));
        $this->repo->markDraft($id);

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

    // ── Produk: pencarian untuk combobox (dbo.INVENTORY) ─────────────────────

    public function searchProduk(Request $request): JsonResponse
    {
        $keyword = $request->string('q')->trim()->toString();
        return response()->json($this->repo->searchProduk($keyword));
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

    public function detail(int $id): JsonResponse
    {
        $rkap     = $this->repo->find($id);
        $products = $this->repo->productsGrouped($id);

        return response()->json(compact('rkap', 'products'));
    }

    // ── Export Excel ──────────────────────────────────────────────────────────

    public function export(int $id)
    {
        $data = $this->repo->exportData($id);
        $filename = 'RKAP_' . ($data['rkap']->NIK_MANAGER ?? $id) . '_' . ($data['rkap']->TAHUN ?? now()->year) . '.xlsx';
        return Excel::download(new RkapExport($data), $filename);
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    /**
     * @return array<int, array{stockid: string, produk: string, months: array<int, array{qty: float, nilai: float}>}>
     */
    private function buildProducts(Request $request): array
    {
        $products = [];
        foreach ($request->input('products', []) as $p) {
            $months = [];
            for ($b = 1; $b <= 12; $b++) {
                $months[$b] = [
                    'qty'   => (float) ($p['qty'][$b]   ?? 0),
                    'nilai' => (float) ($p['nilai'][$b] ?? 0),
                ];
            }
            $products[] = [
                'stockid' => trim((string) $p['stockid']),
                'produk'  => trim((string) $p['produk']),
                'months'  => $months,
            ];
        }
        return $products;
    }

    private function yearRange(): array
    {
        $current = now()->year;
        return range($current - 2, $current + 2);
    }
}
