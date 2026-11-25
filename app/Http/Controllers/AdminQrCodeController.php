<?php

namespace App\Http\Controllers;

use App\Models\LivestockTag;
use App\Support\CowPublicUrl;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Response;

class AdminQrCodeController extends Controller
{
    public function svg(LivestockTag $tag): Response
    {
        $result = (new SvgWriter)->write($this->makeQrCode($tag));

        return response($result->getString())
            ->header('Content-Type', 'image/svg+xml; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="'.$tag->code.'.svg"');
    }

    public function png(LivestockTag $tag): Response
    {
        $result = (new PngWriter)->write($this->makeQrCode($tag));

        return response($result->getString())
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="'.$tag->code.'.png"');
    }

    private function makeQrCode(LivestockTag $tag): QrCode
    {
        return QrCode::create(CowPublicUrl::forTag($tag))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
            ->setSize(1000)
            ->setMargin(24)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin);
    }
}
