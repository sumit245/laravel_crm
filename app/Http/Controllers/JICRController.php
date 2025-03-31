<?php

namespace App\Http\Controllers;

use App\Models\Streetlight;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class JICRController extends Controller
{

    public function index()
    {
        $districts = Streetlight::select('district')->distinct()->get();
        return view('jicr.index', compact('districts'));
    }
    public function getBlocks($district)
    {
        // Fetch blocks based on the selected district
        $blocks = Streetlight::where('district', $district)->select('block')->distinct()->get();
        return response()->json($blocks);
    }
    public function getPanchayats($block)
    {
        // Fetch panchayats based on the selected block
        $panchayats = Streetlight::where('block', $block)->select('panchayat')->distinct()->get();
        return response()->json($panchayats);
    }

    public function generatePDF(Request $request)
    {
        $data = [
            'title' => 'Invoice',
            'date' => date('m/d/Y'),
            'customer' => 'John Doe',
            'items' => [
                ['description' => 'Item 1', 'quantity' => 2, 'price' => 10],
                ['description' => 'Item 2', 'quantity' => 1, 'price' => 20],
            ],
            'subtotal' => 50,
            'tax' => 5,
            'total' => 55,
        ];
        $pdf = Pdf::loadView('jicr.index', $data);
        return $pdf->download('invoice.pdf');
    }
}
