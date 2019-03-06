<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\SuratDisposisi;
use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SuratMasukController extends Controller
{
    public function showSuratMasuk(Request $request)
    {
        $masuks = SuratMasuk::orderByDesc('id')->get();
        $types = JenisSurat::all();
        $no_urut = str_pad(SuratMasuk::count() + 1, 3, '0', STR_PAD_LEFT);
        $findSurat = $request->q;

        return view('surat.masuk', compact('masuks', 'types', 'no_urut', 'findSurat'));
    }

    public function createSuratMasuk(Request $request)
    {
        $no_urut = str_pad(SuratMasuk::count() + 1, 3, '0', STR_PAD_LEFT);

        $this->validate($request, [
            'files' => 'required|array',
            'files.*' => 'mimes:jpg,jpeg,gif,png|max:5120'
        ]);

        if ($request->hasfile('files')) {
            $c = 0;
            $files = [];
            foreach ($request->file('files') as $file) {
                $file->storeAs('public/surat-masuk/' . $no_urut, $file->getClientOriginalName());

                $files[$c] = $file->getClientOriginalName();
                $c = $c + 1;
            }

            SuratMasuk::create([
                'user_id' => Auth::id(),
                'jenis_id' => $request->jenis_id,
                'tgl_surat' => $request->tgl_surat,
                'no_surat' => $request->no_surat,
                'sifat_surat' => $request->sifat_surat,
                'perihal' => $request->perihal,
                'lampiran' => $request->lampiran . ' lembar',
                'nama_instansi' => $request->nama_instansi,
                'asal_instansi' => $request->asal_instansi,
                'nama_pengirim' => $request->nama_pengirim,
                'jabatan_pengirim' => $request->jabatan_pengirim,
                'nip_pengirim' => $request->nip_pengirim,
                'tembusan' => $request->tembusan != null ? $request->tembusan : '-',
                'files' => $files
            ]);
        }

        return back()->with('success', 'Surat Masuk #' . $request->no_surat . ' berhasil dibuat!');
    }

    public function editSuratMasuk($id)
    {
        return SuratMasuk::find($id);
    }

    public function updateSuratMasuk(Request $request)
    {
        $surat = SuratMasuk::find($request->id);
        $surat->update([
            'user_id' => Auth::id(),
            'jenis_id' => $request->jenis_id,
            'tgl_surat' => $request->tgl_surat,
            'no_surat' => $request->no_surat,
            'sifat_surat' => $request->sifat_surat,
            'perihal' => $request->perihal,
            'lampiran' => $request->lampiran . ' lembar',
            'nama_instansi' => $request->nama_instansi,
            'asal_instansi' => $request->asal_instansi,
            'nama_pengirim' => $request->nama_pengirim,
            'jabatan_pengirim' => $request->jabatan_pengirim,
            'nip_pengirim' => $request->nip_pengirim,
            'tembusan' => $request->tembusan != null ? $request->tembusan : '-',
        ]);

        if ($request->hasfile('files')) {
            $c = 0;
            $files = [];
            $no_urut = substr($surat->no_surat, 4, 3);
            foreach ($request->file('files') as $file) {
                $file->storeAs('public/surat-masuk/' . $no_urut, $file->getClientOriginalName());

                $files[$c] = $file->getClientOriginalName();
                $c = $c + 1;
            }

            $surat->update(['files' => $surat->files != "" ? array_merge($surat->files, $files) : $files]);
        }

        return back()->with('success', 'Surat Masuk #' . $surat->no_surat . ' berhasil diperbarui!');
    }

    public function deleteSuratMasuk($id)
    {
        $surat = SuratMasuk::find(decrypt($id));
        $no_urut = substr($surat->no_surat, 4, 3);

        Storage::deleteDirectory('public/surat-masuk/' . $no_urut);

        $surat->delete();

        return back()->with('success', 'Surat Masuk #' . $surat->no_surat . ' berhasil dihapus!');
    }

    public function massDeleteFileSuratMasuk(Request $request)
    {
        $surat = SuratMasuk::find($request->id);
        $no_urut = substr($surat->no_surat, 4, 3);
        $data = implode(', ', array_diff($surat->files, $request->fileSuratMasuks));
        foreach ($request->fileSuratMasuks as $file) {
            if (count($surat->files) == count($request->fileSuratMasuks)) {
                Storage::deleteDirectory('public/surat-masuk/' . $no_urut);

            } else {
                Storage::delete('public/surat-masuk/' . $no_urut . '/' . $file);
            }
        }

        $surat->update(['files' => explode(", ", $data)]);

        return back()->with('success', '' . count($request->fileSuratMasuks) . ' file surat masuk #' .
            $surat->no_surat . ' berhasil dihapus!');
    }

    public function createSuratDisposisi(Request $request)
    {
        $sd = SuratDisposisi::create([
            'suratmasuk_id' => $request->sm_id,
            'diteruskan_kepada' => $request->diteruskan_kepada,
            'harapan' => $request->harapan,
            'catatan' => $request->catatan,
        ]);
        $sd->getSuratMasuk->update(['isDisposisi' => true]);

        return back()->with('success', 'Surat Masuk #' . $sd->getSuratMasuk->no_surat . ' berhasil didisposisi!');
    }

    public function editSuratDisposisi($id)
    {
        return SuratDisposisi::find($id);
    }

    public function updateSuratDisposisi(Request $request)
    {
        $sd = SuratDisposisi::find($request->id);
        $sd->update([
            'suratmasuk_id' => $request->sm_id,
            'diteruskan_kepada' => $request->diteruskan_kepada,
            'harapan' => $request->harapan,
            'catatan' => $request->catatan,
        ]);

        return back()->with('success', 'Disposisi Surat Masuk #' . $sd->getSuratMasuk->no_surat . ' berhasil diperbarui!');
    }

    public function deleteSuratDisposisi($id)
    {
        $sd = SuratDisposisi::find(decrypt($id));
        $sd->delete();
        $sd->getSuratMasuk->update(['isDisposisi' => false]);

        return back()->with('success', 'Disposisi Surat Masuk #' . $sd->getSuratMasuk->no_surat . ' berhasil dihapus!');
    }
}
