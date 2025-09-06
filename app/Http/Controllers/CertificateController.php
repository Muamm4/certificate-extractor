<?php

namespace App\Http\Controllers;

use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Exception;

class CertificateController extends Controller
{
    protected $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    /**
     * Show the certificate upload form.
     */
    public function index()
    {
        return Inertia::render('Extractor');
    }

    /**
     * Handle the certificate upload and processing.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'certificate' => ['required', 'file'],
            'password' => ['required', 'string'],
        ]);

        try {
            $result = $this->certificateService->process(
                $request->file('certificate'),
                $request->input('password')
            );

            return Inertia::render('Extractor', [
                'result' => $result,
            ]);

        } catch (Exception $e) {
            return Redirect::back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download the generated PEM files.
     */
    public function download($id, $type)
    {
        $allowedTypes = ['certificate', 'public', 'private'];
        if (!in_array($type, $allowedTypes)) {
            abort(404, 'Invalid file type.');
        }

        $path = "pem/{$id}/{$type}.pem";

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('local')->download($path);
    }
}