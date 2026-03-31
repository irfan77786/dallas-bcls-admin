<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRController extends Controller
{
    // Generate QR code for the review form
    public function generateReviewQR()
    {
        $url = route('review.create'); // Route for the review form
        $qrCode = QrCode::size(300)->generate($url);

        return view('pages.reviews.qr', compact('qrCode', 'url'));
    }

    // Generate QR code for the unified feedback form (complaint or suggestion)
    public function generateFeedbackQR()
    {
        $url = route('feedback.create'); // Unified route for the feedback form
        $qrCode = QrCode::size(300)->generate($url);

        return view('pages.feedback.qr', compact('qrCode', 'url'));
    }
}
