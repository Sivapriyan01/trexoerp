<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SettingsController extends Controller
{
    public function index()
    {
        $rapidApiKey = env('RAPIDAPI_KEY');
        $gstReturnTargetMonths = env('GST_RETURN_TARGET_MONTHS', 8);
        $sessionDriver = env('SESSION_DRIVER', 'database');
        $sessionLifetime = env('SESSION_LIFETIME', 120);
        return view('superadmin.settings', compact('rapidApiKey', 'gstReturnTargetMonths', 'sessionDriver', 'sessionLifetime'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'rapidapi_key' => 'required|string',
            'gst_return_target_months' => 'nullable|integer|min:1|max:24',
            'session_driver' => 'required|string|in:file,cookie,database,apc,memcached,redis,dynamodb,array',
            'session_lifetime' => 'required|integer|min:1',
        ]);

        $inputKeys = str_replace("\r", "", $request->input('rapidapi_key'));
        $keysArray = array_filter(array_map('trim', preg_split('/[\n,]+/', $inputKeys)));
        $keysString = implode(',', $keysArray);

        $this->setEnv('RAPIDAPI_KEY', $keysString);
        
        if ($request->has('gst_return_target_months')) {
            $this->setEnv('GST_RETURN_TARGET_MONTHS', $request->input('gst_return_target_months'));
        }
        
        $this->setEnv('SESSION_DRIVER', $request->input('session_driver'));
        $this->setEnv('SESSION_LIFETIME', $request->input('session_lifetime'));

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    private function setEnv($key, $value)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            $env = file_get_contents($path);
            $env = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $env);
            if (strpos($env, "{$key}=") === false) {
                $env .= "\n{$key}={$value}";
            }
            file_put_contents($path, $env);
        }
    }
}
