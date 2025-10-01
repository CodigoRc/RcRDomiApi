<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\RadioStreaming;
use App\Models\HostingStation;
use App\Models\VideoStreaming;
use App\Models\Activity;
use App\Mail\StationRadioMail;
use App\Mail\StationHostingMail;
use App\Mail\StationTvMail;
use App\Mail\DomintCompanyMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class StationMailController extends Controller
{
    /**
     * Send radio streaming email to station contacts
     */
    public function sendRadioMail(Request $request)
    {
        try {
            \Log::info('📧 sendRadioMail called', ['request' => $request->all()]);
            
            $stationId = $request->input('station_id');
            \Log::info('📧 Station ID received', ['station_id' => $stationId]);
            
            // Validar y obtener datos
            $station = Station::with(['client'])->find($stationId);
            if (!$station) {
                \Log::error('❌ Station not found', ['station_id' => $stationId]);
                return response()->json(['error' => 'Station not found'], 404);
            }
            \Log::info('✅ Station found', ['station' => $station->toArray()]);

            $radioStreaming = RadioStreaming::where('station_id', $stationId)->first();
            if (!$radioStreaming) {
                \Log::error('❌ No radio streaming config', ['station_id' => $stationId]);
                return response()->json(['error' => 'No radio streaming configuration found'], 400);
            }
            \Log::info('✅ Radio streaming found', ['streaming' => $radioStreaming->toArray()]);

            // Obtener emails de la estación
            $emails = $this->getStationEmails($station);
            \Log::info('📧 Station emails detected', ['emails' => $emails]);
            
            if (empty($emails)) {
                \Log::error('❌ No emails configured', ['station_id' => $stationId]);
                return response()->json(['error' => 'No emails configured for this station'], 400);
            }

            // TEMPORALMENTE: Usar DomintCompanyMail en lugar de StationRadioMail
            $results = [];
            
            foreach ($emails as $emailData) {
                try {
                    \Log::info('📤 Attempting to send email', ['to' => $emailData['address']]);
                    
                    // Usar el Mailable que ya funciona para testing
                    $testDetails = [
                        'subject' => "RDomi - Radio Streaming: {$station->name}",
                        'body' => "Datos de acceso para la estación {$station->name}"
                    ];

                    // Configure mail with SSL options
                    $mailer = Mail::mailer('smtp');
                    $mailer->getSymfonyTransport()->getStream()->setStreamOptions([
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        ]
                    ]);
                    
                    $mailer->to($emailData['address'])
                           ->send(new DomintCompanyMail($testDetails));

                    \Log::info('✅ Email sent successfully', ['to' => $emailData['address']]);

                    $results[] = [
                        'email' => $emailData['address'],
                        'type' => $emailData['type'],
                        'status' => 'sent',
                        'sent_at' => now()->toISOString()
                    ];

                    // Log activity (simplified)
                    try {
                        Activity::create([
                            'station_id' => $station->id,
                            'client_id' => $station->client_id,
                            'action' => 'email_sent',
                            'description' => "Radio streaming email sent to {$emailData['address']}"
                        ]);
                    } catch (\Exception $activityError) {
                        \Log::warning('⚠️ Failed to log activity', ['error' => $activityError->getMessage()]);
                    }

                } catch (\Exception $e) {
                    \Log::error('❌ Email send failed', [
                        'to' => $emailData['address'],
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'mail_config' => [
                            'mailer' => config('mail.default'),
                            'host' => config('mail.mailers.smtp.host'),
                            'port' => config('mail.mailers.smtp.port'),
                            'username' => config('mail.mailers.smtp.username'),
                            'encryption' => config('mail.mailers.smtp.encryption'),
                            'from_address' => config('mail.from.address'),
                            'has_password' => !empty(config('mail.mailers.smtp.password'))
                        ]
                    ]);

                    $results[] = [
                        'email' => $emailData['address'],
                        'type' => $emailData['type'],
                        'status' => 'failed',
                        'error' => $e->getMessage()
                    ];
                }
            }

            $sentCount = collect($results)->where('status', 'sent')->count();
            $failedCount = collect($results)->where('status', 'failed')->count();

            \Log::info('📊 Email sending completed', [
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'results' => $results
            ]);

            return response()->json([
                'success' => true,
                'station_name' => $station->name,
                'results' => $results,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'total_emails' => count($emails)
            ]);

        } catch (\Exception $e) {
            \Log::error('💥 Critical error in sendRadioMail', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to send emails: ' . $e->getMessage(),
                'debug' => [
                    'line' => $e->getLine(),
                    'file' => basename($e->getFile())
                ]
            ], 500);
        }
    }

    /**
     * Send hosting station email to station contacts
     */
    public function sendHostingMail(Request $request)
    {
        try {
            $stationId = $request->input('station_id');
            
            $station = Station::with(['client'])->find($stationId);
            if (!$station) {
                return response()->json(['error' => 'Station not found'], 404);
            }

            $hostingStation = HostingStation::where('station_id', $stationId)->first();
            if (!$hostingStation) {
                return response()->json(['error' => 'No hosting station configuration found'], 400);
            }

            $emails = $this->getStationEmails($station);
            if (empty($emails)) {
                return response()->json(['error' => 'No emails configured for this station'], 400);
            }

            $results = [];
            
            foreach ($emails as $emailData) {
                try {
                    Mail::to($emailData['address'])
                        ->send(new StationHostingMail($station, $hostingStation));

                    $results[] = [
                        'email' => $emailData['address'],
                        'type' => $emailData['type'],
                        'status' => 'sent',
                        'sent_at' => now()->toISOString()
                    ];

                    // Log activity (simplified)
                    try {
                        Activity::create([
                            'station_id' => $station->id,
                            'client_id' => $station->client_id,
                            'action' => 'email_sent',
                            'description' => "Hosting email sent to {$emailData['address']}"
                        ]);
                    } catch (\Exception $activityError) {
                        \Log::warning('⚠️ Failed to log activity', ['error' => $activityError->getMessage()]);
                    }

                } catch (\Exception $e) {
                    $results[] = [
                        'email' => $emailData['address'],
                        'type' => $emailData['type'],
                        'status' => 'failed',
                        'error' => $e->getMessage()
                    ];
                }
            }

            $sentCount = collect($results)->where('status', 'sent')->count();
            $failedCount = collect($results)->where('status', 'failed')->count();

            return response()->json([
                'success' => true,
                'station_name' => $station->name,
                'results' => $results,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'total_emails' => count($emails)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to send emails: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get valid emails from station
     */
    private function getStationEmails($station): array
    {
        $emails = [];
        
        if ($station->email && filter_var($station->email, FILTER_VALIDATE_EMAIL)) {
            $emails[] = [
                'address' => $station->email,
                'type' => 'primary'
            ];
        }
        
        if ($station->email2 && filter_var($station->email2, FILTER_VALIDATE_EMAIL)) {
            $emails[] = [
                'address' => $station->email2,
                'type' => 'secondary'
            ];
        }
        
        return $emails;
    }

    /**
     * Preview radio email content without sending
     */
    public function previewRadioMail(Request $request)
    {
        $stationId = $request->input('station_id');
        
        $station = Station::find($stationId);
        if (!$station) {
            return response()->json(['error' => 'Station not found'], 404);
        }

        $radioStreaming = RadioStreaming::where('station_id', $stationId)->first();
        if (!$radioStreaming) {
            return response()->json(['error' => 'No radio streaming configuration'], 400);
        }

        $emails = $this->getStationEmails($station);
        
        // Generate preview content
        $mailable = new StationRadioMail($station, $radioStreaming);
        $htmlContent = $mailable->render();

        return response()->json([
            'success' => true,
            'station_name' => $station->name,
            'target_emails' => $emails,
            'html_content' => $htmlContent,
            'subject' => "RDomi - Acceso Radio Streaming: {$station->name}"
        ]);
    }

    /**
     * Test email configuration and basic functionality
     */
    public function testEmailConfig()
    {
        try {
            \Log::info('🔧 Testing email configuration...');
            
            $config = [
                'default_mailer' => config('mail.default'),
                'smtp_host' => config('mail.mailers.smtp.host'),
                'smtp_port' => config('mail.mailers.smtp.port'),
                'smtp_username' => config('mail.mailers.smtp.username'),
                'smtp_encryption' => config('mail.mailers.smtp.encryption'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
                'has_password' => !empty(config('mail.mailers.smtp.password'))
            ];
            
            \Log::info('🔧 Mail configuration', $config);
            
            return response()->json([
                'success' => true,
                'mail_config' => $config,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            \Log::error('💥 Error testing email config', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug endpoint to check station data
     */
    public function debugStation(Request $request)
    {
        try {
            $stationId = $request->input('station_id');
            \Log::info('🔍 Debug station called', ['station_id' => $stationId]);
            
            $station = Station::find($stationId);
            if (!$station) {
                return response()->json(['error' => 'Station not found'], 404);
            }

            $radioStreaming = RadioStreaming::where('station_id', $stationId)->first();
            $emails = $this->getStationEmails($station);

            $debug = [
                'station_exists' => !!$station,
                'station_data' => $station ? $station->toArray() : null,
                'radio_streaming_exists' => !!$radioStreaming,
                'radio_streaming_data' => $radioStreaming ? $radioStreaming->toArray() : null,
                'detected_emails' => $emails,
                'mail_config' => [
                    'default' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'from' => config('mail.from.address')
                ]
            ];

            \Log::info('🔍 Debug results', $debug);

            return response()->json([
                'success' => true,
                'debug' => $debug
            ]);

        } catch (\Exception $e) {
            \Log::error('💥 Debug station error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a simple test email
     */
    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'to' => 'required|email'
        ]);

        try {
            $testDetails = [
                'subject' => 'RDomi - Test Email',
                'body' => 'This is a test email from RDomi system.'
            ];

            // Configure mail with SSL bypass for SendGrid connection issues
            $mailer = \Mail::mailer('smtp');
            try {
                $mailer->getSymfonyTransport()->getStream()->setStreamOptions([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ]);
            } catch (\Exception $sslError) {
                \Log::warning('Could not set SSL options', ['error' => $sslError->getMessage()]);
            }
            
            $mailer->to($request->input('to'))
                   ->send(new \App\Mail\DomintCompanyMail($testDetails));

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully',
                'sent_to' => $request->input('to')
            ]);

        } catch (\Exception $e) {
            \Log::error('Test email failed', [
                'error' => $e->getMessage(),
                'to' => $request->input('to'),
                'mail_config' => config('mail')
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'mail_driver' => config('mail.default')
            ], 500);
        }
    }

    /**
     * Send TV streaming email to station contacts
     */
    public function sendTvMail(Request $request)
    {
        try {
            \Log::info('📺 sendTvMail called', ['request' => $request->all()]);
            
            $stationId = $request->input('station_id');
            \Log::info('📺 Station ID received', ['station_id' => $stationId]);
            
            // Validar y obtener datos
            $station = Station::with(['client'])->find($stationId);
            if (!$station) {
                \Log::error('❌ Station not found', ['station_id' => $stationId]);
                return response()->json(['error' => 'Station not found'], 404);
            }
            \Log::info('✅ Station found', ['station' => $station->toArray()]);

            $videoStreaming = VideoStreaming::where('station_id', $stationId)->first();
            if (!$videoStreaming) {
                \Log::error('❌ No video streaming config', ['station_id' => $stationId]);
                return response()->json(['error' => 'No video streaming configuration found'], 400);
            }
            \Log::info('✅ Video streaming found', ['streaming' => $videoStreaming->toArray()]);

            // Obtener emails de la estación
            $emails = $this->getStationEmails($station);
            \Log::info('📺 Station emails detected', ['emails' => $emails]);
            
            if (empty($emails)) {
                \Log::error('❌ No emails configured', ['station_id' => $stationId]);
                return response()->json(['error' => 'No emails configured for this station'], 400);
            }

            $results = [];
            
            foreach ($emails as $emailData) {
                try {
                    \Log::info('📤 Attempting to send TV email', ['to' => $emailData['address']]);
                    
                    // Configure mail with SSL options
                    $mailer = Mail::mailer('smtp');
                    $mailer->getSymfonyTransport()->getStream()->setStreamOptions([
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        ]
                    ]);
                    
                    $mailer->to($emailData['address'])
                           ->send(new StationTvMail($station, $videoStreaming));

                    \Log::info('✅ TV Email sent successfully', ['to' => $emailData['address']]);

                    $results[] = [
                        'email' => $emailData['address'],
                        'type' => $emailData['type'],
                        'status' => 'sent',
                        'sent_at' => now()->toISOString()
                    ];

                    // Log activity (simplified)
                    try {
                        Activity::create([
                            'station_id' => $station->id,
                            'client_id' => $station->client_id,
                            'action' => 'email_sent',
                            'description' => "TV streaming email sent to {$emailData['address']}"
                        ]);
                    } catch (\Exception $activityError) {
                        \Log::warning('⚠️ Failed to log activity', ['error' => $activityError->getMessage()]);
                    }

                } catch (\Exception $e) {
                    \Log::error('❌ TV Email send failed', [
                        'to' => $emailData['address'],
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    $results[] = [
                        'email' => $emailData['address'],
                        'type' => $emailData['type'],
                        'status' => 'failed',
                        'error' => $e->getMessage()
                    ];
                }
            }

            $sentCount = collect($results)->where('status', 'sent')->count();
            $failedCount = collect($results)->where('status', 'failed')->count();

            \Log::info('📊 TV Email sending completed', [
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'results' => $results
            ]);

            return response()->json([
                'success' => true,
                'station_name' => $station->name,
                'results' => $results,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'total_emails' => count($emails)
            ]);

        } catch (\Exception $e) {
            \Log::error('💥 Critical error in sendTvMail', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to send TV emails: ' . $e->getMessage(),
                'debug' => [
                    'line' => $e->getLine(),
                    'file' => basename($e->getFile())
                ]
            ], 500);
        }
    }
}
