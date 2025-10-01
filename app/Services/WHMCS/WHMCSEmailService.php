<?php

namespace App\Services\WHMCS;

use App\Exceptions\WHMCSException;
use Illuminate\Support\Facades\Log;

class WHMCSEmailService
{
    protected $api;

    public function __construct(WHMCSApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Log email sent in WHMCS client notes (does NOT send email via WHMCS)
     * 
     * Note: This method only logs the activity in WHMCS.
     * The actual email sending should be done with Laravel Mail for proper HTML rendering.
     * 
     * @param int $clientId WHMCS client ID
     * @param string $recipientEmail
     * @param string $subject
     * @param string $emailType Type of email (Radio, TV, Hosting)
     * @param array $details Additional details to log
     * @return array
     */
    public function logEmailInWHMCS(int $clientId, string $recipientEmail, string $subject, string $emailType, array $details = []): array
    {
        try {
            // Create note text
            $note = "📧 Email {$emailType} enviado\n";
            $note .= "══════════════════════════\n";
            $note .= "Para: {$recipientEmail}\n";
            $note .= "Asunto: {$subject}\n";
            
            foreach ($details as $key => $value) {
                $note .= ucfirst($key) . ": {$value}\n";
            }
            
            $note .= "Enviado: " . date('Y-m-d H:i:s') . "\n";
            $note .= "══════════════════════════";

            Log::info('📝 Logging email in WHMCS client notes', [
                'client_id' => $clientId,
                'recipient' => $recipientEmail,
                'type' => $emailType
            ]);

            $response = $this->api->request('AddClientNote', [
                'userid' => $clientId,
                'notes' => $note
            ], true);

            Log::info('✅ Email logged in WHMCS client notes', [
                'client_id' => $clientId,
                'recipient' => $recipientEmail
            ]);

            return [
                'success' => true,
                'message' => 'Email activity logged in WHMCS',
                'whmcs_response' => $response,
                'logged_for_client' => $clientId
            ];

        } catch (WHMCSException $e) {
            Log::error('❌ Failed to log email in WHMCS', [
                'client_id' => $clientId,
                'error' => $e->getMessage()
            ]);

            throw new WHMCSException('Failed to log email in WHMCS: ' . $e->getMessage());
        }
    }

    /**
     * Send email using WHMCS custom template with Smarty variables
     *
     * This uses the correct WHMCS approach: call template by name with variables
     *
     * @param string $templateName Name of WHMCS template (e.g., 'rdomi_radio_streaming')
     * @param int $clientId WHMCS client ID
     * @param string $recipientEmail Recipient email address
     * @param array $variables Smarty variables for the template
     * @return array
     */
    public function sendEmailViaTemplate(string $templateName, int $clientId, ?string $recipientEmail, array $variables): array
    {
        try {
            // WHMCS SendEmail API - using custom template approach
            $params = [
                'messagename' => $templateName,  // Our custom template name
                'id' => $clientId,  // Client ID
                'customvars' => base64_encode(serialize($variables)),  // Smarty variables
            ];

            // If recipient is different from client's email, specify it
            // If NULL, WHMCS will send to the client's registered email automatically
            if ($recipientEmail) {
                $params['customto'] = $recipientEmail;
            }

            Log::info('📧 Sending email via WHMCS template', [
                'template' => $templateName,
                'client_id' => $clientId,
                'custom_to' => $recipientEmail ?? 'client_default_email',
                'variables' => array_keys($variables)
            ]);

            $response = $this->api->request('SendEmail', $params, true);

            Log::info('✅ Email sent via WHMCS template', [
                'template' => $templateName,
                'client_id' => $clientId,
                'custom_to' => $recipientEmail ?? 'client_default_email',
                'response' => $response
            ]);

            return [
                'success' => true,
                'message' => 'Email sent successfully via WHMCS template',
                'whmcs_response' => $response,
                'sent_to' => $recipientEmail ?? 'client_default_email',
                'template' => $templateName,
                'method' => 'whmcs_template'
            ];

        } catch (WHMCSException $e) {
            Log::error('❌ WHMCS template email send failed', [
                'template' => $templateName,
                'client_id' => $clientId,
                'custom_to' => $recipientEmail ?? 'client_default_email',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // This will allow the caller to fallback to Laravel Mail
            throw new WHMCSException('WHMCS template email failed: ' . $e->getMessage());
        }
    }

    /**
     * Send Radio Streaming email via WHMCS template
     *
     * @param int $clientId WHMCS client ID
     * @param string $recipientEmail
     * @param array $stationData Station information
     * @param array $streamingData Streaming configuration
     * @return array
     */
    public function sendRadioStreamingViaTemplate(int $clientId, ?string $recipientEmail, array $stationData, array $streamingData): array
    {
        // Get client name from WHMCS
        $clientName = 'Cliente';
        try {
            $clientDetails = $this->api->request('GetClientsDetails', [
                'clientid' => $clientId,
                'stats' => false
            ], true);
            
            if (isset($clientDetails['client'])) {
                $client = $clientDetails['client'];
                // Construir nombre completo
                $firstName = $client['firstname'] ?? '';
                $lastName = $client['lastname'] ?? '';
                $companyName = $client['companyname'] ?? '';
                
                // Usar nombre de empresa si existe, sino nombre completo
                if (!empty($companyName)) {
                    $clientName = $companyName;
                } else {
                    $clientName = trim($firstName . ' ' . $lastName);
                }
                
                // Si aún está vacío, usar email
                if (empty($clientName)) {
                    $clientName = $client['email'] ?? 'Cliente';
                }
            }
            
            Log::info('✅ Client name retrieved from WHMCS', [
                'client_id' => $clientId,
                'client_name' => $clientName
            ]);
            
        } catch (\Exception $e) {
            Log::warning('⚠️ Could not retrieve client name from WHMCS', [
                'client_id' => $clientId,
                'error' => $e->getMessage()
            ]);
        }
        
        // Prepare Smarty variables for the template
        $variables = [
            'client_name' => $clientName,  // ← NUEVO: Nombre del cliente
            'station_name' => $stationData['name'] ?? 'Estación',
            'username' => $streamingData['username'] ?? 'N/A',
            'password' => $streamingData['password'] ?? 'N/A',
            'server_host' => $streamingData['host'] ?? 'N/A',
            'server_port' => $streamingData['port'] ?? 'N/A',
            'stream_password' => $streamingData['stream_password'] ?? 'N/A',
            'panel_url' => "https://{$streamingData['host']}",
            'stream_url' => "https://{$streamingData['host']}/{$streamingData['port']}/stream",
            'embed_code' => '&lt;iframe src=&quot;https://rdomiplayer.com/embed/radio/' . $stationData['id'] . '/2&quot; style=&quot;border: none; border-radius: 12px; overflow: hidden;&quot; width=&quot;100%&quot; height=&quot;111&quot; allow=&quot;autoplay&quot;&gt;&lt;/iframe&gt;',
        ];

        Log::info('📧 Sending email with client name', [
            'client_id' => $clientId,
            'client_name' => $clientName,
            'station_name' => $stationData['name'] ?? 'N/A'
        ]);

        return $this->sendEmailViaTemplate(
            'rdomi_radio_streaming',  // Template name in WHMCS
            $clientId,
            $recipientEmail,
            $variables
        );
    }

    /**
     * Send TV Streaming email via WHMCS template
     *
     * @param int $clientId WHMCS client ID
     * @param string|null $recipientEmail
     * @param array $stationData Station information
     * @param array $streamingData Video streaming configuration
     * @return array
     */
    public function sendTvStreamingViaTemplate(int $clientId, ?string $recipientEmail, array $stationData, array $streamingData): array
    {
        // Get client name from WHMCS
        $clientName = 'Cliente';
        try {
            $clientDetails = $this->api->request('GetClientsDetails', [
                'clientid' => $clientId,
                'stats' => false
            ], true);
            
            if (isset($clientDetails['client'])) {
                $client = $clientDetails['client'];
                // Construir nombre completo
                $firstName = $client['firstname'] ?? '';
                $lastName = $client['lastname'] ?? '';
                $companyName = $client['companyname'] ?? '';
                
                // Usar nombre de empresa si existe, sino nombre completo
                if (!empty($companyName)) {
                    $clientName = $companyName;
                } else {
                    $clientName = trim($firstName . ' ' . $lastName);
                }
                
                // Si aún está vacío, usar email
                if (empty($clientName)) {
                    $clientName = $client['email'] ?? 'Cliente';
                }
            }
            
            Log::info('✅ Client name retrieved from WHMCS for TV email', [
                'client_id' => $clientId,
                'client_name' => $clientName
            ]);
            
        } catch (\Exception $e) {
            Log::warning('⚠️ Could not retrieve client name from WHMCS for TV email', [
                'client_id' => $clientId,
                'error' => $e->getMessage()
            ]);
        }
        
        // Prepare Smarty variables for the template
        // Note: VideoStreaming model has: host, port, username, password, stream_password, stream_ssl_url, ip
        
        // Clean host (remove any protocol prefix)
        $host = $streamingData['host'] ?? 'N/A';
        $host = str_replace(['https://', 'http://'], '', $host);
        
        $port = $streamingData['port'] ?? '1935';
        $username = $streamingData['username'] ?? 'N/A';
        $password = $streamingData['password'] ?? 'N/A';
        $streamPassword = $streamingData['stream_password'] ?? 'N/A';
        
        // Fix stream_ssl_url if it has double https://
        $streamSslUrl = $streamingData['stream_ssl_url'] ?? '';
        $streamSslUrl = str_replace('https://https://', 'https://', $streamSslUrl);
        $streamSslUrl = str_replace('http://https://', 'https://', $streamSslUrl);
        if (empty($streamSslUrl) || $streamSslUrl === 'https://') {
            $streamSslUrl = "https://{$host}/live/stream.m3u8";
        }
        
        // Build server URL (MediaCP format: rtmp://host:port/username)
        $serverUrl = "rtmp://{$host}:{$port}/{$username}";
        
        $variables = [
            'client_name' => $clientName,
            'station_name' => $stationData['name'] ?? 'Estación',
            'server_url' => $serverUrl,
            'stream_key' => $username,  // Stream key = username en MediaCP
            'stream_username' => $username,  // Para OBS/MediaCP
            'stream_password' => $streamPassword,  // Stream password separado
            'username' => $username,  // Panel username
            'password' => $password,  // Panel password
            'server_host' => $host,
            'server_port' => $port,
            'server_ip' => $streamingData['ip'] ?? 'N/A',
            'panel_url' => "https://{$host}",
            'stream_ssl_url' => $streamSslUrl,
            'embed_code' => '&lt;iframe src=&quot;https://rdomiplayer.com/embed/tv/' . $stationData['id'] . '/2&quot; style=&quot;border: none; border-radius: 12px; overflow: hidden;&quot; width=&quot;100%&quot; height=&quot;480&quot; allow=&quot;autoplay&quot;&gt;&lt;/iframe&gt;',
        ];

        Log::info('📺 Sending TV email with all variables', [
            'client_id' => $clientId,
            'client_name' => $clientName,
            'station_name' => $stationData['name'] ?? 'N/A',
            'server_url' => $variables['server_url'],
            'stream_key' => $variables['stream_key'],
            'stream_username' => $variables['stream_username'],
            'stream_password' => $variables['stream_password'],
            'username' => $variables['username'],
            'password' => $variables['password'],
            'stream_ssl_url' => $variables['stream_ssl_url'],
            'panel_url' => $variables['panel_url']
        ]);

        return $this->sendEmailViaTemplate(
            'rdomi_tv_streaming',  // Template name in WHMCS
            $clientId,
            $recipientEmail,
            $variables
        );
    }

    /**
     * Send Hosting Web email via WHMCS template
     *
     * @param int $clientId WHMCS client ID
     * @param string|null $recipientEmail
     * @param array $stationData Station information
     * @param array $hostingData Hosting configuration
     * @return array
     */
    public function sendHostingViaTemplate(int $clientId, ?string $recipientEmail, array $stationData, array $hostingData): array
    {
        // Get client name from WHMCS
        $clientName = 'Cliente';
        try {
            $clientDetails = $this->api->request('GetClientsDetails', [
                'clientid' => $clientId,
                'stats' => false
            ], true);
            
            if (isset($clientDetails['client'])) {
                $client = $clientDetails['client'];
                $firstName = $client['firstname'] ?? '';
                $lastName = $client['lastname'] ?? '';
                $companyName = $client['companyname'] ?? '';
                
                if (!empty($companyName)) {
                    $clientName = $companyName;
                } else {
                    $clientName = trim($firstName . ' ' . $lastName);
                }
                
                if (empty($clientName)) {
                    $clientName = $client['email'] ?? 'Cliente';
                }
            }
            
            Log::info('✅ Client name retrieved from WHMCS for Hosting email', [
                'client_id' => $clientId,
                'client_name' => $clientName
            ]);
            
        } catch (\Exception $e) {
            Log::warning('⚠️ Could not retrieve client name from WHMCS for Hosting email', [
                'client_id' => $clientId,
                'error' => $e->getMessage()
            ]);
        }
        
        // Clean cpanel URL (remove any protocol prefix)
        $cpanelUrl = $hostingData['cpanel'] ?? '';
        $cpanelUrl = str_replace('https://https://', 'https://', $cpanelUrl);
        $cpanelUrl = str_replace('http://https://', 'https://', $cpanelUrl);
        
        // Extract host from cpanel URL for FTP
        $ftpHost = $cpanelUrl;
        if (strpos($ftpHost, '://') !== false) {
            $ftpHost = parse_url($ftpHost, PHP_URL_HOST) ?: $ftpHost;
        }
        $ftpHost = str_replace(['https://', 'http://'], '', $ftpHost);
        
        // Clean site URL
        $siteUrl = $hostingData['url'] ?? '';
        $siteUrl = str_replace('https://https://', 'https://', $siteUrl);
        $siteUrl = str_replace('http://https://', 'https://', $siteUrl);
        if (empty($siteUrl)) {
            $siteUrl = "https://{$ftpHost}";
        }
        
        $variables = [
            'client_name' => $clientName,
            'station_name' => $stationData['name'] ?? 'Estación',
            'cpanel_url' => $cpanelUrl,
            'cpanel_username' => $hostingData['user_name'] ?? 'N/A',
            'cpanel_password' => $hostingData['pass'] ?? 'N/A',
            'ftp_host' => $ftpHost,
            'ftp_username' => $hostingData['ftp_user'] ?? $hostingData['user_name'] ?? 'N/A',
            'ftp_password' => $hostingData['ftp_pass'] ?? $hostingData['pass'] ?? 'N/A',
            'site_url' => $siteUrl,
        ];

        Log::info('🏠 Sending Hosting email with all variables', [
            'client_id' => $clientId,
            'client_name' => $clientName,
            'station_name' => $stationData['name'] ?? 'N/A',
            'cpanel_url' => $variables['cpanel_url'],
            'ftp_host' => $variables['ftp_host'],
            'site_url' => $variables['site_url']
        ]);

        return $this->sendEmailViaTemplate(
            'rdomi_hosting_web',  // Template name in WHMCS
            $clientId,
            $recipientEmail,
            $variables
        );
    }

    /**
     * Alternative approach: Use WHMCS API to send email to client directly
     * This bypasses the template system and sends HTML directly
     */
    public function sendEmailToClient(int $clientId, string $subject, string $htmlMessage): array
    {
        try {
            // Alternative: Send directly to client using their ID
            $params = [
                'messagename' => 'Client Notification', // Try different template
                'id' => $clientId,
                'customsubject' => $subject,
                'custommessage' => $htmlMessage,
                'customtype' => 'notification',
            ];

            Log::info('📧 Sending HTML email to client via WHMCS', [
                'client_id' => $clientId,
                'subject' => $subject,
                'method' => 'client_direct'
            ]);

            $response = $this->api->request('SendEmail', $params, true);

            return [
                'success' => true,
                'message' => 'HTML Email sent to client via WHMCS',
                'whmcs_response' => $response,
                'client_id' => $clientId,
                'method' => 'client_direct'
            ];

        } catch (WHMCSException $e) {
            Log::error('❌ WHMCS client email failed', [
                'client_id' => $clientId,
                'error' => $e->getMessage()
            ]);

            throw new WHMCSException('WHMCS client email failed: ' . $e->getMessage());
        }
    }

    /**
     * Log radio streaming email in WHMCS (does NOT send email)
     * 
     * Note: This only logs in WHMCS. The actual email is sent by Laravel Mail.
     * 
     * @param int $clientId WHMCS client ID
     * @param string $recipientEmail
     * @param array $stationData Station information
     * @param array $streamingData Streaming configuration
     * @return array
     */
    public function logRadioStreamingEmail(int $clientId, string $recipientEmail, array $stationData, array $streamingData): array
    {
        $subject = "RDomi - Acceso Radio Streaming: {$stationData['name']}";
        
        $details = [
            'estación' => "{$stationData['name']} (ID: {$stationData['id']})",
            'servidor' => "{$streamingData['host']}:{$streamingData['port']}",
            'usuario' => $streamingData['username']
        ];
        
        try {
            return $this->logEmailInWHMCS($clientId, $recipientEmail, $subject, 'Radio Streaming', $details);
        } catch (\Exception $e) {
            Log::warning('⚠️ Could not log radio email in WHMCS', [
                'client_id' => $clientId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'logged_in_whmcs' => false,
                'warning' => $e->getMessage()
            ];
        }
    }


    /**
     * Log TV streaming email in WHMCS (does NOT send email)
     * 
     * Note: This only logs in WHMCS. The actual email is sent by Laravel Mail.
     * 
     * @param int $clientId WHMCS client ID
     * @param string $recipientEmail
     * @param array $stationData Station information
     * @param array $streamingData Video streaming configuration
     * @return array
     */
    public function logTvStreamingEmail(int $clientId, string $recipientEmail, array $stationData, array $streamingData): array
    {
        $subject = "RDomi - Acceso TV Streaming: {$stationData['name']}";
        
        $details = [
            'estación' => "{$stationData['name']} (ID: {$stationData['id']})",
            'servidor' => "{$streamingData['host']}:{$streamingData['port']}",
            'usuario' => $streamingData['username']
        ];
        
        try {
            return $this->logEmailInWHMCS($clientId, $recipientEmail, $subject, 'TV Streaming', $details);
        } catch (\Exception $e) {
            Log::warning('⚠️ Could not log TV email in WHMCS', [
                'client_id' => $clientId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'logged_in_whmcs' => false,
                'warning' => $e->getMessage()
            ];
        }
    }

}

