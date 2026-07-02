<?php

namespace App\Http\Controllers;

use App\Services\AgingpiutRepository;
use App\Services\Dashboard2Repository;
use App\Services\DashboardRepository;
use App\Support\FormatHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private Dashboard2Repository $repository,
        private DashboardRepository $dashboardRepository,
        private AgingpiutRepository $agingpiutRepository,
    ) {}

    public function index(Request $request): View
    {
        $username = $request->session()->get('username');
        $nik = $request->session()->get('nik');

        $tahunSekarang = (int) now()->format('Y');
        $bulanSekarang = (int) now()->format('n');

        $tahunOptions = range($tahunSekarang, $tahunSekarang - 6);
        $tahun = (int) $request->query('tahun', $tahunSekarang);
        if (! in_array($tahun, $tahunOptions, true)) {
            $tahun = $tahunSekarang;
        }

        // Can't browse past the month that's actually running for the current
        // year — there's no posted data yet for future months. Defaults to the
        // real running month (not FormatHelper::periodeTrans()'s accounting-close
        // lag) so "Kinerja Bulan Berjalan" always reflects the calendar month.
        $maxBulan = $tahun === $tahunSekarang ? $bulanSekarang : 12;
        $bulan = max(1, min((int) $request->query('bulan', $bulanSekarang), $maxBulan));
        $periode = sprintf('%04d%02d', $tahun, $bulan);

        $piutangJatuhTempo = $this->dashboardRepository->piutangJatuhTempo();
        $kpi = $this->dashboardRepository->kpiBulanIni($periode);
        $sektorAchievement = $this->dashboardRepository->sektorAchievement($periode);
        $kpiTahunan = $this->repository->kpiTahunan($periode);
        $penjualanTahunan = $this->repository->seriesPenjualanTahunan((string) $tahun);
        $bebanPemasaranTahunan = $this->repository->seriesBebanPemasaranTahunan((string) $tahun);
        $bebanUmumTahunan = $this->repository->seriesBebanUmumTahunan((string) $tahun);
        $wilayahCards = $this->repository->wilayahCards($periode);
        $totalPiutangAktif = $this->agingpiutRepository->totalOpenAmount();

        return view('dashboard.index', array_merge([
            'lastLogin' => $this->dashboardRepository->lastLogin($username),
            'pegawai' => $this->dashboardRepository->pegawai($nik),
            'piutangJatuhTempo' => $piutangJatuhTempo,
            'totalPiutangAktif' => $totalPiutangAktif,
            'penjualanHariIniTotal' => $this->dashboardRepository->penjualanHariIniTotal(),
            'kpi' => $kpi,
            'sektorAchievement' => $sektorAchievement,
            'kpiTahunan' => $kpiTahunan,
            'penjualanTahunan' => $penjualanTahunan,
            'labaKotorTahunan' => $this->repository->seriesLabaTahunan((string) $tahun),
            'labaOperasiTahunan' => $this->repository->seriesLabaOperasiTahunan((string) $tahun),
            'labaBersihTahunan' => $this->repository->seriesLabaBersihTahunan((string) $tahun),
            'bebanPemasaranTahunan' => $bebanPemasaranTahunan,
            'bebanUmumTahunan' => $bebanUmumTahunan,
            'wilayahCards' => $wilayahCards,
            'periode' => $periode,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'tahunOptions' => $tahunOptions,
            'maxBulanForTahun' => $maxBulan,
        ], $this->buildInsights(
            $kpiTahunan,
            $penjualanTahunan,
            $bebanPemasaranTahunan,
            $bebanUmumTahunan,
            $sektorAchievement,
            $wilayahCards,
            $totalPiutangAktif,
            $piutangJatuhTempo,
            $tahun,
        )));
    }

    /**
     * Turns the already-fetched chart/KPI data into short Indonesian insight
     * sentences for each chart section. No new queries — purely derived from
     * data the page already loads.
     *
     * @return array<string, array<int, string>>
     */
    private function buildInsights(
        array $kpiTahunan,
        array $penjualanTahunan,
        array $bebanPemasaranTahunan,
        array $bebanUmumTahunan,
        array $sektorAchievement,
        array $wilayahCards,
        float $totalPiutangAktif,
        array $piutangJatuhTempo,
        int $tahun,
    ): array {
        $penjualan = $kpiTahunan['penjualan'];
        $sumRealPenjualan = array_sum(array_map('floatval', $penjualanTahunan['real']));
        $sumSblPenjualan = array_sum(array_map('floatval', $penjualanTahunan['sbl']));

        $insightPenjualan = [
            'Penjualan tahun ini Rp '.FormatHelper::maskRp($sumRealPenjualan).' ('.FormatHelper::trendLabel($sumRealPenjualan, $sumSblPenjualan).' dibanding '.($tahun - 1).').',
            'Capaian terhadap RKAP '.$penjualan['capaian'].'% ('.FormatHelper::achievementLabel($penjualan['capaian']).').',
        ];

        $insightLaba = [
            'Margin laba kotor (GPM) '.$kpiTahunan['laba_kotor']['gpm'].'%, laba operasi (OPM) '.$kpiTahunan['laba_operasi']['opm'].'%, laba bersih (NPM) '.$kpiTahunan['laba_bersih']['npm'].'%.',
            'Laba bersih tahun berjalan '.FormatHelper::achievementLabel($kpiTahunan['laba_bersih']['capaian']).' terhadap RKAP ('.$kpiTahunan['laba_bersih']['capaian'].'%).',
        ];

        $bebanInsight = function (array $beban, string $label): array {
            $sumReal = array_sum(array_map('floatval', $beban['real']));
            $sumRkap = array_sum(array_map('floatval', $beban['rkap']));
            $sumSbl = array_sum(array_map('floatval', $beban['sbl']));
            $capaian = FormatHelper::targetPersen($sumRkap, $sumReal);

            return [
                $label.' tahun ini Rp '.FormatHelper::maskRp($sumReal).' ('.FormatHelper::trendLabel($sumReal, $sumSbl).' dibanding tahun lalu).',
                'Realisasi '.$capaian.'% dari anggaran RKAP ('.($capaian > 100 ? 'melebihi anggaran' : 'masih dalam anggaran').').',
            ];
        };

        $insightBebanPemasaran = $bebanInsight($bebanPemasaranTahunan, 'Beban Pemasaran');
        $insightBebanUmum = $bebanInsight($bebanUmumTahunan, 'Beban Umum & Administrasi');

        $sektorBerRkap = array_filter($sektorAchievement, fn ($row) => $row['rkap'] > 0);
        $insightSektor = [];
        if ($sektorBerRkap !== []) {
            $tertinggi = collect($sektorBerRkap)->sortByDesc('capaian')->first();
            $terendah = collect($sektorBerRkap)->sortBy('capaian')->first();
            $insightSektor[] = 'Sektor '.$tertinggi['kode_sektor'].' capaian tertinggi ('.$tertinggi['capaian'].'%); sektor '.$terendah['kode_sektor'].' capaian terendah ('.$terendah['capaian'].'%).';
        }

        $insightWilayah = [];
        if ($wilayahCards !== []) {
            $topReal = collect($wilayahCards)->sortByDesc('real')->first();
            $topRasio = collect($wilayahCards)->sortByDesc(fn ($row) => $row['real'] > 0 ? ($row['beban_pemasaran'] + $row['beban_umum']) / $row['real'] : 0)->first();
            $rasio = $topRasio['real'] > 0 ? round((($topRasio['beban_pemasaran'] + $topRasio['beban_umum']) / $topRasio['real']) * 100, 1) : 0;
            $insightWilayah[] = 'Wilayah '.$topReal['wilayah'].' realisasi penjualan tertinggi (Rp '.FormatHelper::maskRp($topReal['real']).'); wilayah '.$topRasio['wilayah'].' rasio beban terhadap penjualan tertinggi ('.$rasio.'%).';
        }

        $totalJatuhTempo = array_sum(array_map(fn ($row) => (float) $row->NILAI, $piutangJatuhTempo));
        $insightPiutang = [];
        if ($totalPiutangAktif > 0) {
            $pctJatuhTempo = round(($totalJatuhTempo / $totalPiutangAktif) * 100, 1);
            $insightPiutang[] = 'Rp '.FormatHelper::maskRp($totalJatuhTempo).' ('.$pctJatuhTempo.'%) dari piutang aktif sudah jatuh tempo.';
            if ($piutangJatuhTempo !== []) {
                $topCustomer = $piutangJatuhTempo[0];
                $insightPiutang[] = 'Terbesar dari '.trim((string) $topCustomer->NAMA).' sebesar Rp '.FormatHelper::maskRp((float) $topCustomer->NILAI).'.';
            }
        }

        return [
            'insightPenjualan' => $insightPenjualan,
            'insightLaba' => $insightLaba,
            'insightBebanPemasaran' => $insightBebanPemasaran,
            'insightBebanUmum' => $insightBebanUmum,
            'insightSektor' => $insightSektor,
            'insightWilayah' => $insightWilayah,
            'insightPiutang' => $insightPiutang,
        ];
    }

    public function sektorDetail(Request $request): JsonResponse
    {
        $idSektor = (string) $request->input('idSektor1');
        $periode = (string) $request->input('periode') ?: FormatHelper::periodeTrans();

        return response()->json($this->dashboardRepository->sektorDetail($idSektor, $periode));
    }

    public function penjualanHariIni(): JsonResponse
    {
        return response()->json($this->dashboardRepository->penjualanHariIni());
    }

    public function agingDetail(Request $request): JsonResponse
    {
        $korek = (string) $request->input('korek');

        return response()->json($this->dashboardRepository->agingDetailByRekanan($korek));
    }

    public function fakturDetail(Request $request): JsonResponse
    {
        $nofaktur = (string) $request->input('nofaktur');

        return response()->json($this->dashboardRepository->fakturDetail($nofaktur));
    }

    public function chartDetail(Request $request): JsonResponse
    {
        $opt = (string) $request->input('option');

        return response()->json($this->repository->chartTahunanDetail($opt));
    }

    public function penjualanBreakdown(Request $request): JsonResponse
    {
        $tahun = (int) $request->input('tahun', now()->year);

        return response()->json([
            'sektor' => $this->repository->sektorTahunan($tahun),
            'wilayah' => $this->repository->wilayahTahunan($tahun),
        ]);
    }
}
