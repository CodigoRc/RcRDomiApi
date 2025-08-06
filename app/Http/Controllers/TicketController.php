<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Client;
use App\Models\Station;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    protected $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    // Mostrar una lista de tickets
    public function index()
    {
        $tickets = Ticket::orderBy('created_at', 'desc')->get();
        return response()->json($tickets);
    }

    // Mostrar un ticket específico
    public function show($id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        return response()->json($ticket);
    }

    // Crear un nuevo ticket
   
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'user_id' => 'nullable|integer',
                'client_id' => 'nullable|integer',
                'station_id' => 'nullable|integer',
                'contact_method' => 'required|string',
                'title' => 'required|string|max:255',
                'priority' => 'required|string',
                'notes' => 'nullable|string',
                'status' => 'required|string',
                'phone' => 'nullable|string|max:20',
                'internal_use' => 'nullable|boolean',
                'department' => 'nullable|string|max:255',
                'email' => 'nullable|string|email|max:255',
            ]);

            $ticket = Ticket::create($validatedData);

            // Registrar la actividad
            $userId = $validatedData['user_id'];
            $description = 'Ticket created: ' . $validatedData['title'];
            $importantChange = $validatedData['priority'];
            $status = $validatedData['status'];
            $ticketId = $ticket->id;

            if (!empty($validatedData['station_id'])) {
                $station = Station::find($validatedData['station_id']);
                if ($station) {
                    $this->activityService->logStationReport(
                        $station,
                        $userId,
                        $description,
                        $importantChange,
                        $status,
                        $ticketId,
                        $request
                    );
                }
            } elseif (!empty($validatedData['client_id'])) {
                $client = Client::find($validatedData['client_id']);
                if ($client) {
                    $this->activityService->logClientReport(
                        $client,
                        $userId,
                        $description,
                        $importantChange,
                        $status,
                        $ticketId,
                        $request
                    );
                }
            }

            return response()->json(["data" => $ticket, "code" => 200]);
        } catch (\Exception $e) {
            Log::error('Error creating ticket: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating ticket', 'error' => $e->getMessage()], 500);
        }
    }

    // Actualizar un ticket existente
    public function update(Request $request, $id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $validatedData = $request->validate([
            'user_id' => 'nullable|integer',
            'client_id' => 'nullable|integer',
            'station_id' => 'nullable|integer',
            'contact_method' => 'required|string',
            'title' => 'required|string|max:255',
            'priority' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'internal_use' => 'nullable|boolean',
            'department' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
        ]);

        $ticket->fill($validatedData);
        $ticket->save();

        return response()->json(["data" => $ticket, "code" => 200]);
    }

    // Eliminar un ticket
    public function destroy($id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $ticket->delete();

        return response()->json(['message' => 'Ticket deleted successfully']);
    }
}