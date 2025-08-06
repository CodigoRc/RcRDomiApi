<?php

namespace App\Http\Controllers;

use App\Models\Mail;
use App\Models\MailMessage;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MailMessageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'mailId' => 'required|integer|exists:mails,id',
            'message' => 'required|string',
            'recipient_id' => 'required|integer|exists:users,id',
            'sender_type' => 'required|string|in:client,admin,admin_notes', // Validar el tipo de remitente
        ]);

        $mail = Mail::findOrFail($request->input('mailId'));

        $data = $request->only(['message', 'recipient_id', 'sender_type']);
        $data['mail_id'] = $mail->id;
        $data['sender_id'] = $request->input('sender_type') === 'admin' ? Auth::id() : null;

        $mailMessage = MailMessage::create($data);

        // Manejar adjuntos
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments');
                Attachment::create([
                    'attachable_id' => $mailMessage->id,
                    'attachable_type' => MailMessage::class,
                    'file_path' => $path,
                ]);
            }
        }

        // Cargar y ordenar los mensajes relacionados por 'created_at' en orden descendente
        $messages = $mail->messages()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $mailMessage,
            'messages' => $messages
        ], 201);
    }

    public function index($mailId)
    {
        $mail = Mail::findOrFail($mailId);
        $messages = $mail->messages()->with(['sender', 'recipient', 'attachments'])->get();

        return response()->json(['messages' => $messages]);
    }
}