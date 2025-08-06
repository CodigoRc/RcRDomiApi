<?php
namespace App\Http\Controllers;

use App\Models\Mail;
use App\Models\MailFolder;
use App\Models\MailLabel;
use App\Models\MailFilter;
use Illuminate\Http\Request;
use App\Http\Resources\MailResource;
use Illuminate\Support\Facades\Log;
use App\Models\Attachment;
use Illuminate\Support\Facades\Mail as MailFacade;
use App\Mail\DomintCompanyMail;

class MailController extends Controller
{
    public function index(Request $request)
    {
        $labelSlug = $request->input('label');
        $filterSlug = $request->input('filter');
        $folderSlug = $request->input('folder');
        $page = $request->input('page', 1);

        $query = Mail::query();

        if ($labelSlug) {
            // Buscar el ID del label por su slug
            $label = MailLabel::where('slug', $labelSlug)->first();
            if ($label) {
                $query->whereJsonContains('labels', $label->id); // Usar la columna correcta
            } else {
                return response()->json([
                    'error' => 'Label not found',
                    'query_params' => $request->all(),
                ], 404);
            }
        }

        if ($folderSlug) {
            // Buscar el ID del folder por su slug
            $folder = MailFolder::where('slug', $folderSlug)->first();
            if ($folder) {
                $query->where('folder', $folder->slug); // Usar la columna correcta
            } else {
                return response()->json([
                    'error' => 'Folder not found',
                    'query_params' => $request->all(),
                ], 404);
            }
        }

        if ($filterSlug) {
            if ($filterSlug === 'in_progress') {
                // Filtrar por correos electrónicos que tengan in_progress = 1
                $query->where('in_progress', 1);
            } elseif ($filterSlug === 'open') {
                // Filtrar por correos electrónicos que tengan open = 1
                $query->where('open', 1);
            } elseif ($filterSlug === 'closed') {
                // Filtrar por correos electrónicos que tengan closed = 1
                $query->where('closed', 1);
            } else {
                // Buscar el ID del filter por su slug
                $filter = MailFilter::where('slug', $filterSlug)->first();
                if ($filter) {
                    $query->where('filter', $filter->id); // Usar la columna correcta
                } else {
                    return response()->json([
                        'error' => 'Filter not found',
                        'query_params' => $request->all(),
                    ], 404);
                }
            }

        }

        $mails = $query->paginate(10, ['*'], 'page', $page);

        return response()->json([
            'mails' => MailResource::collection($mails),
            'pagination' => [
                'totalResults' => $mails->total(),
                'resultsPerPage' => $mails->perPage(),
                'currentPage' => $mails->currentPage(),
                'lastPage' => $mails->lastPage(),
                'startIndex' => $mails->firstItem(),
                'endIndex' => $mails->lastItem() - 1,
            ],
            'query_params' => $request->all(), // Mostrar solo los parámetros de consulta
        ]);
    }


    public function getMailCounts(Request $request)
    {
        try {
            // Contar correos por etiquetas
            $labelCounts = MailLabel::all()->mapWithKeys(function ($label) {
                return [$label->slug => Mail::whereJsonContains('labels', $label->id)->count()];
            });

            // Contar correos por carpetas
            $folderCounts = MailFolder::all()->mapWithKeys(function ($folder) {
                return [$folder->slug => Mail::where('folder', $folder->slug)->count()];
            });

            // Contar correos por filtros
            $filterCounts = [
                'in_progress' => Mail::where('in_progress', 1)->count(), // in_progress es ahora important
                'open' => Mail::where('open', 1)->count(), // open es ahora starred
                'closed' => Mail::where('closed', 1)->count(), // closed se mantiene igual
            ];

            // Devolver los resultados en una respuesta JSON
            return response()->json([
                'labelCounts' => $labelCounts,
                'folderCounts' => $folderCounts,
                'filterCounts' => $filterCounts,
            ]);
        } catch (\Exception $e) {
            // Registrar el error en el log
            Log::error('Error retrieving mail counts', ['error' => $e->getMessage()]);

            // Devolver una respuesta de error
            return response()->json(['message' => 'Error retrieving mail counts', 'error' => $e->getMessage()], 500);
        }
    }

    


    public function rcSend(Request $request)
    {
        $email = $request->input('email');
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Email is valid', 'email' => $email]);
        } else {
            return response()->json(['message' => 'Invalid email address', 'email' => $email], 400);
        }
    }



    public function store(Request $request)
    {
        // Obtener los datos de la solicitud sin validar
        $data = $request->input('mail');

        try {
            // Establecer el campo 'date' igual a 'created_at'
            $data['date'] = now();

            // Crear el correo
            $mail = Mail::create($data);

                  // Manejar adjuntos
                  if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $path = $file->store('attachments');
                        Attachment::create([
                            'attachable_id' => $mail->id,
                            'attachable_type' => Mail::class,
                            'file_path' => $path,
                        ]);
                    }
                }

            // Devolver el recurso de correo
            return response()->json([
                'message' => 'Mail created successfully',
                'data' => new MailResource($mail),
            ], 201);
        } catch (\Exception $e) {
            // Devolver una respuesta de error más entendible
            return response()->json([
                'message' => 'Failed to create mail',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $mail = Mail::findOrFail($id);
        $mail->update($request->all());
        return new MailResource($mail);
    }




    public function show($id)
    {
        $mail = Mail::findOrFail($id);
        return new MailResource($mail);
    }

  

    public function destroy($id)
    {
        Mail::destroy($id);
        return response()->json(null, 204);
    }

    public function sendTestMail(Request $request)
    {
        // Validar que el campo 'to' sea un correo electrónico válido
        $request->validate([
            'to' => 'required|email',
        ]);

        // Dirección de correo a la que se enviará el correo de prueba
        $rcmail = 'codigorc@gmail.com';

        try {
            // Detalles del correo electrónico
            $details = [
                'subject' => 'Test Mail from DomintCompany',
                'body' => 'This is a test email sent from DomintCompany.'
            ];

            // Enviar el correo electrónico a la dirección especificada
            MailFacade::to($rcmail)->send(new DomintCompanyMail($details));

            // Devolver una respuesta JSON indicando el éxito del envío
            return response()->json([
                'message' => 'Test mail sent successfully',
            ], 200);
        } catch (\Exception $e) {
            // Devolver una respuesta JSON indicando el fallo del envío
            return response()->json([
                'message' => 'Failed to send test mail',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}