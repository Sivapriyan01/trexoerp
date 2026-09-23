<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\AIChatService;
use Illuminate\Http\Request;

class AIChatController extends Controller
{
    protected $aiService;

    public function __construct(AIChatService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Handle incoming chat message.
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $response = $this->aiService->getResponse($request->message);

        return response()->json($response);
    }
}
