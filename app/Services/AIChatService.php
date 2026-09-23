<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIChatService
{
    /**
     * Process user message and return a response.
     */
    public function getResponse(string $message): array
    {
        $originalMessage = $message;
        $message = strtolower($message);
        
        // If it's a conversational question (What, How, Why, Today, etc.), skip direct shortcuts
        $isConversational = Str::contains($message, ['what', 'how', 'why', 'who', 'when', 'today', 'yesterday', 'tell', 'explain', 'show me']);
        
        if (!$isConversational) {
            // --- DIRECT SHORTCUTS / NAVIGATION ---
            // These only trigger for simple, direct commands

        if (Str::contains($message, ['gst', 'tax', 'gstin', 'gstr'])) {
            return [
                'text' => "You can manage **GST** and tax settings within the Billing module. We support **GSTIN validation**, **GSTR-1**, and **GSTR-3B** reports. Would you like to check the tax reports?",
                'action' => [
                    'label' => 'Open GST Reports',
                    'route' => route('tenant.billing.gst.gstr1'),
                ],
                'suggestions' => ['Validate GSTIN', 'GSTR-3B Report', 'Billing Settings']
            ];
        }

        // Production
        if (Str::contains($message, ['production', 'manufacturing', 'job', 'stage'])) {
            return [
                'text' => "The **Production** module tracks your manufacturing stages and job cards. You can manage ongoing jobs and update stages there.",
                'action' => [
                    'label' => 'Open Production',
                    'route' => route('tenant.production.index'),
                ],
                'suggestions' => ['Add Job Card', 'Completed Jobs', 'Stage Config']
            ];
        }

        // Daily Expense
        if (Str::contains($message, ['expense', 'daily', 'petty cash', 'spending'])) {
            return [
                'text' => "Track your office spendings and petty cash in the **Daily Expense** module. It helps in maintaining a transparent cash flow.",
                'action' => [
                    'label' => 'Open Expenses',
                    'route' => route('tenant.daily-expense.index'),
                ],
                'suggestions' => ['Add Expense', 'Petty Cash', 'Expense Report']
            ];
        }

        // WhatsApp & Mail Marketing
        if (Str::contains($message, ['marketing', 'whatsapp', 'email', 'mail', 'broadcast'])) {
            return [
                'text' => "Boost your sales with **Marketing** tools! You can send bulk WhatsApp messages or set up Email broadcasts to reach your customers.",
                'action' => [
                    'label' => 'WhatsApp Marketing',
                    'route' => route('tenant.whatsapp.index'),
                ],
                'suggestions' => ['Mail Broadcast', 'Customer Lists', 'Send Bulk WhatsApp']
            ];
        }

        // Stock Transfer
        if (Str::contains($message, ['transfer', 'warehouse', 'move stock'])) {
            return [
                'text' => "Move inventory between warehouses using **Stock Transfer**. Use **F8** for reports and **F9** to toggle between list and create views.",
                'action' => [
                    'label' => 'Stock Transfer',
                    'route' => route('tenant.stock-transfer.index'),
                ],
                'suggestions' => ['Transfer Report', 'Current Stock', 'New Transfer']
            ];
        }

        // Instalments / EMI
        if (Str::contains($message, ['instalment', 'emi', 'payment schedule', 'due'])) {
            return [
                'text' => "Manage customer payment schedules and track upcoming dues in the **Instalments** module.",
                'action' => [
                    'label' => 'Open Instalments',
                    'route' => route('tenant.instalments.index'),
                ],
                'suggestions' => ['Due Dashboard', 'Payment Collection', 'Schedule EMI']
            ];
        }

        // Summary Dashboard
        if (Str::contains($message, ['summary', 'report', 'analytics', 'overview'])) {
            return [
                'text' => "Get a bird's-eye view of your entire business performance in the **Summary Dashboard**.",
                'action' => [
                    'label' => 'View Summary',
                    'route' => route('tenant.summary.index'),
                ],
                'suggestions' => ['Sales Analysis', 'Stock Summary', 'Financial Growth']
            ];
        }

        // Smart Navigation/Action matching
        if (Str::contains($message, ['billing', 'invoice', 'sale'])) {
            return [
                'text' => "I can help you with that! You can create a new invoice or manage sales in the **Billing** module. Would you like me to take you there?",
                'action' => [
                    'label' => 'Open Billing',
                    'route' => route('tenant.billing.index'),
                    'shortcut' => 'F2'
                ]
            ];
        }

        if (Str::contains($message, ['tally', 'erp', 'xml', 'export'])) {
            return [
                'text' => "The **Tally ERP Integration** module allows you to export your financial data and import masters. You can access it via the shortcut **F7**.",
                'action' => [
                    'label' => 'Open Tally',
                    'route' => route('tenant.tally.index'),
                    'shortcut' => 'F7'
                ]
            ];
        }

        if (Str::contains($message, ['product', 'stock', 'inventory', 'item'])) {
            return [
                'text' => "You can manage your products and monitor stock levels in the **Product Master**. Use **F3** for quick access.",
                'action' => [
                    'label' => 'Open Products',
                    'route' => route('tenant.products.index'),
                    'shortcut' => 'F3'
                ]
            ];
        }

        if (Str::contains($message, ['crm', 'lead', 'customer', 'workflow'])) {
            return [
                'text' => "The **CRM Workflow** manages your leads and customer interactions. Use **F4** to open it instantly.",
                'action' => [
                    'label' => 'Open CRM',
                    'route' => route('tenant.crm.index'),
                    'shortcut' => 'F4'
                ]
            ];
        }

        if (Str::contains($message, ['setup', 'setting', 'config'])) {
            return [
                'text' => "System configurations can be found in the **Setup** section. You can jump there using **F10**.",
                'action' => [
                    'label' => 'Open Setup',
                    'route' => '/setup',
                    'shortcut' => 'F10'
                ]
            ];
        }

        if (Str::contains($message, ['shortcut', 'hotkey', 'keyboard'])) {
            return [
                'text' => "I've set up several shortcuts for you:\n\n- **F1**: Dashboard\n- **F2**: Billing\n- **F3**: Products\n- **F4**: CRM\n- **F7**: Tally\n- **F10**: Setup\n\nWhich one would you like to use?",
            ];
        }

        if (Str::contains($message, ['calendar', 'schedule', 'event', 'date'])) {
            return [
                'text' => "You can manage your appointments and scheduled events in the **Calendar** module.",
                'action' => [
                    'label' => 'Open Calendar',
                    'route' => route('tenant.calendar.index'),
                ],
                'suggestions' => ['View Events', 'Add Schedule', 'Upcoming Tasks']
            ];
        }

        if (Str::contains($message, ['use', 'purpose', 'what is this', 'about', 'application', 'system'])) {
            return [
                'text' => "**TrexoERP** is your all-in-one Business Operating System! 🚀\n\nIt helps you manage:\n- **Billing & POS**: Fast invoices and GST tracking.\n- **Production**: Monitor manufacturing stages and job cards.\n- **CRM**: Track leads and customer workflows.\n- **Inventory**: Real-time stock and warehouse transfers.\n- **Marketing**: WhatsApp and Email broadcasts.\n- **Finance**: Expense tracking and Tally ERP integration.",
                'suggestions' => ['Open Billing', 'Production Status', 'Check CRM']
            ];
        }

        if (Str::contains($message, ['hi', 'hello', 'hey', 'who are you', 'help'])) {
            return [
                'text' => "Hello! I'm your **Trexoerp AI Assistant**. I can help you navigate the system, explain features, and provide quick access to modules.\n\nTry asking me:\n- \"Show me the billing\"\n- \"How do I track production?\"\n- \"I want to see my expenses\"\n- \"Export to Tally\"",
                'suggestions' => ['Open Billing', 'Production Status', 'Daily Expenses', 'CRM Workflow']
            ];
        }

        } // End of !isConversational

        // Default response - Fallback to Gemini AI
        return $this->callGemini($originalMessage);
    }

    /**
     * Call AI API (Supports Gemini via OpenRouter)
     */
    private function callGemini(string $userMessage): array
    {
        // Checks for both possible variable names in .env
        $apiKey = env('GEMINI_API_KEY') ?: env('API_KEY');
        
        if (!$apiKey) {
            return [
                'text' => "I'm currently in **Offline Mode**. To enable my full AI brain, please add your API key to the `.env` file.",
                'suggestions' => ['Go to Billing', 'Check Production', 'Help']
            ];
        }

        try {
            $today = now()->format('Y-m-d');
            $packetsDoneToday = \App\Models\Production::where('status', 'Completed')
                ->whereDate('end_date', $today)
                ->sum('total_qty');

            // Using OpenRouter format as it matches your key type (sk-or-v1-...)
            // .withoutVerifying() bypasses the local SSL certificate issue
            $response = Http::withoutVerifying()->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$apiKey}",
                'HTTP-Referer' => 'http://localhost:8000', // Optional for OpenRouter
                'X-Title' => 'TrexoERP AI', 
            ])->post("https://openrouter.ai/api/v1/chat/completions", [
                'model' => 'openrouter/auto', // Automatically picks an available model for your key
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are 'TrexoERP AI', a premium business assistant for the 'TrexoERP' Business Operating System. 
                        You help users manage Billing (F2), Manufacturing/Production, CRM (F4), Inventory (F3), and Tally ERP (F7).
                        
                        LIVE SYSTEM DATA:
                        - Today's Date: " . now()->format('l, F j, Y') . "
                        - Packets completed today: " . $packetsDoneToday . "
                        
                        CRITICAL: When a user asks for data like 'today's production' or 'packets done', answer them DIRECTLY using the LIVE SYSTEM DATA provided above. 
                        Do not tell them to check a dashboard if you have the data. If they ask something you don't have data for, then suggest the relevant dashboard.
                        Be professional, concise, and helpful. Use markdown for clear formatting."
                    ],
                    [
                        'role' => 'user',
                        'content' => $userMessage
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 1000,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $aiText = $result['choices'][0]['message']['content'] ?? null;

                if ($aiText) {
                    return [
                        'text' => $aiText,
                        'suggestions' => ['Open Billing', 'Production Status', 'Financial Summary']
                    ];
                }
            }
            
            Log::error('AI API Response Error', ['body' => $response->body()]);

        } catch (\Exception $e) {
            Log::error('AI API Connection Error: ' . $e->getMessage());
        }

        return [
            'text' => "I'm having trouble connecting to my AI brain right now. Please check your API key or connection.",
            'suggestions' => ['Try again', 'Go to Billing', 'Help']
        ];
    }
}
