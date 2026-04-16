<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Prediction;

class PrediksiController extends Controller
{
    private function checkAdmin()
    {
        $user = session('user', ['']);
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            return redirect($user ? '/dashboard' : '/login');
        }
        return $user;
    }

    /** GET /admin/prediksi */
    public function index()
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        $predictions = Prediction::with('commodity')
                         ->orderBy('created_at', 'desc')
                         ->paginate(10);
        
        $commodities = \App\Models\Commodity::all();

        return view('admin.prediksi', compact('user', 'predictions', 'commodities'));
    }

    /** POST /admin/prediksi/generate */
    public function generate(Request $request)
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        $request->validate([
            'commodity_id' => 'required',
            'region'       => 'required|string',
            'period'       => 'required|string',
            'model'        => 'required|string',
        ]);

        // TODO: Integrate with Flask ML API / PredictionService
        // $result = PredictionService::generate($request->all());
        // Prediction::create($result);

        return redirect('/admin/prediksi')->with('success', 'Prediksi berhasil digenerate.');
    }

    /** GET /admin/prediksi/{id} */
    public function show(string $id)
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        $prediction = Prediction::with('commodity')->findOrFail($id);

        return view('admin.prediksi-detail', compact('user', 'prediction'));
    }

    /** DELETE /admin/prediksi/{id} */
    public function destroy(string $id)
    {
        $user = $this->checkAdmin();
        if ($user instanceof \Illuminate\Http\RedirectResponse) return $user;

        Prediction::findOrFail($id)->delete();

        return redirect('/admin/prediksi')->with('success', 'Prediksi berhasil dihapus.');
    }
}

