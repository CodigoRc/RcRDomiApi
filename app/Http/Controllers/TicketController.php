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
            // Log para debug
            Log::info('Ticket store request:', [
                'has_id' => $request->has('id'),
                'id_value' => $request->input('id'),
                'request_data' => $request->all()
            ]);
            
            // Si hay un ID, es una actualización - validación más flexible
            if ($request->has('id') && $request->input('id')) {
                $validatedData = $request->validate([
                    'id' => 'required|integer',
                    'user_id' => 'nullable|integer',
                    'client_id' => 'nullable|integer',
                    'station_id' => 'nullable|integer',
                    'contact_method' => 'nullable|string',
                    'title' => 'nullable|string|max:255',
                    'priority' => 'nullable|string',
                    'notes' => 'nullable|string',
                    'status' => 'nullable|string',
                    'phone' => 'nullable|string|max:20',
                    'internal_use' => 'nullable|boolean',
                    'department' => 'nullable|string|max:255',
                    'email' => 'nullable|string|email|max:255',
                ]);

                $ticket = Ticket::find($validatedData['id']);
                if (!$ticket) {
                    return response()->json(['message' => 'Ticket not found'], 404);
                }
                
                unset($validatedData['id']); // Remover el ID de los datos a actualizar
                
                // Solo actualizar campos que no estén vacíos
                $updateData = array_filter($validatedData, function($value) {
                    return $value !== null && $value !== '';
                });
                
                $ticket->update($updateData);
                $description = 'Ticket updated: ' . ($ticket->title ?? 'Ticket');
                
                Log::info('Ticket updated successfully:', [
                    'ticket_id' => $ticket->id,
                    'updated_data' => $updateData,
                    'final_ticket' => $ticket->toArray()
                ]);
            } else {
                // Crear nuevo ticket - validación completa
                $validatedData = $request->validate([
                    'user_id' => 'required|integer',
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
                $description = 'Ticket created: ' . $validatedData['title'];
            }

            // Solo registrar nueva actividad si es un ticket nuevo
            if (!$request->has('id') || !$request->input('id')) {
                // Registrar la actividad solo para tickets nuevos
                $userId = $validatedData['user_id'] ?? $ticket->user_id ?? 1;
                $importantChange = $validatedData['priority'] ?? $ticket->priority ?? 'medium';
                $status = $validatedData['status'] ?? $ticket->status ?? 'open';
                $ticketId = $ticket->id;

                $stationId = $validatedData['station_id'] ?? $ticket->station_id;
                $clientId = $validatedData['client_id'] ?? $ticket->client_id;

                if (!empty($stationId)) {
                    $station = Station::find($stationId);
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
                } elseif (!empty($clientId)) {
                    $client = Client::find($clientId);
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

    // Eliminar un ticket (soft delete - move to trash)
    public function destroy($id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        // Soft delete - mark as deleted instead of removing
        $ticket->status = 'deleted';
        $ticket->save();

        return response()->json(['message' => 'Ticket moved to trash successfully']);
    }

    // Eliminar múltiples tickets (soft delete - move to trash)
    public function bulkDestroy(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'ticket_ids' => 'required|array',
                'ticket_ids.*' => 'required|integer'
            ]);

            $ticketIds = $validatedData['ticket_ids'];
            $deletedCount = 0;
            $notFoundIds = [];

            foreach ($ticketIds as $ticketId) {
                $ticket = Ticket::find($ticketId);
                if ($ticket) {
                    // Soft delete - mark as deleted instead of removing
                    $ticket->status = 'deleted';
                    $ticket->save();
                    $deletedCount++;
                } else {
                    $notFoundIds[] = $ticketId;
                }
            }

            $response = [
                'message' => "Successfully moved {$deletedCount} ticket(s) to trash",
                'deleted_count' => $deletedCount,
                'total_requested' => count($ticketIds)
            ];

            if (!empty($notFoundIds)) {
                $response['not_found_ids'] = $notFoundIds;
                $response['warning'] = 'Some tickets were not found';
            }

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Bulk delete tickets error: ' . $e->getMessage());
            return response()->json(['message' => 'Error moving tickets to trash'], 500);
        }
    }

    // Actualizar estado de múltiples tickets
    public function bulkUpdateStatus(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'ticket_ids' => 'required|array',
                'ticket_ids.*' => 'required|integer',
                'status' => 'required|string|in:open,in_progress,closed,report'
            ]);

            $ticketIds = $validatedData['ticket_ids'];
            $status = $validatedData['status'];
            $updatedCount = 0;
            $notFoundIds = [];

            foreach ($ticketIds as $ticketId) {
                $ticket = Ticket::find($ticketId);
                if ($ticket) {
                    $ticket->status = $status;
                    $ticket->save();
                    $updatedCount++;
                } else {
                    $notFoundIds[] = $ticketId;
                }
            }

            $response = [
                'message' => "Successfully updated {$updatedCount} ticket(s) to {$status}",
                'updated_count' => $updatedCount,
                'total_requested' => count($ticketIds),
                'new_status' => $status
            ];

            if (!empty($notFoundIds)) {
                $response['not_found_ids'] = $notFoundIds;
                $response['warning'] = 'Some tickets were not found';
            }

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Bulk update ticket status error: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating ticket status'], 500);
        }
    }

    // Restaurar ticket desde la papelera
    public function restore($id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        if ($ticket->status !== 'deleted') {
            return response()->json(['message' => 'Ticket is not in trash'], 400);
        }

        // Restore ticket - set status back to open
        $ticket->status = 'open';
        $ticket->save();

        return response()->json(['message' => 'Ticket restored successfully']);
    }

    // Eliminar permanentemente múltiples tickets
    public function bulkPermanentDelete(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'ticket_ids' => 'required|array',
                'ticket_ids.*' => 'required|integer'
            ]);

            $ticketIds = $validatedData['ticket_ids'];
            $deletedCount = 0;
            $notFoundIds = [];

            foreach ($ticketIds as $ticketId) {
                $ticket = Ticket::find($ticketId);
                if ($ticket) {
                    // Permanent delete - actually remove from database
                    $ticket->delete();
                    $deletedCount++;
                } else {
                    $notFoundIds[] = $ticketId;
                }
            }

            $response = [
                'message' => "Successfully permanently deleted {$deletedCount} ticket(s)",
                'deleted_count' => $deletedCount,
                'total_requested' => count($ticketIds)
            ];

            if (!empty($notFoundIds)) {
                $response['not_found_ids'] = $notFoundIds;
                $response['warning'] = 'Some tickets were not found';
            }

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Bulk permanent delete tickets error: ' . $e->getMessage());
            return response()->json(['message' => 'Error permanently deleting tickets'], 500);
        }
    }

    // Migrar tickets legacy (status='report') a tickets con status='deleted'
    public function migrateLegacyDeleted(Request $request)
    {
        try {
            // Buscar todos los tickets con status='report' que deberían estar eliminados
            $legacyTickets = Ticket::where('status', 'report')->get();
            
            $migratedCount = 0;
            $migrationLog = [];
            
            foreach ($legacyTickets as $ticket) {
                // Cambiar status de 'report' a 'deleted'
                $ticket->status = 'deleted';
                $ticket->save();
                
                $migratedCount++;
                $migrationLog[] = [
                    'ticket_id' => $ticket->id,
                    'title' => $ticket->title,
                    'old_status' => 'report',
                    'new_status' => 'deleted'
                ];
                
                Log::info('Legacy ticket migrated:', [
                    'ticket_id' => $ticket->id,
                    'title' => $ticket->title,
                    'old_status' => 'report',
                    'new_status' => 'deleted'
                ]);
            }
            
            $response = [
                'message' => "Successfully migrated {$migratedCount} legacy deleted ticket(s)",
                'migrated_count' => $migratedCount,
                'migration_log' => $migrationLog
            ];
            
            Log::info('Legacy ticket migration completed:', $response);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Legacy ticket migration error: ' . $e->getMessage());
            return response()->json(['message' => 'Error migrating legacy tickets', 'error' => $e->getMessage()], 500);
        }
    }

    // Eliminar permanentemente todos los tickets legacy (status='report' o status=NULL con action='report')
    public function deleteLegacyTickets(Request $request)
    {
        try {
            // Buscar todos los tickets legacy - pueden tener status='report' o status=NULL
            $legacyTickets = Ticket::where(function($query) {
                $query->where('status', 'report')
                      ->orWhereNull('status');
            })->get();
            
            $deletedCount = 0;
            $deletedTickets = [];
            
            foreach ($legacyTickets as $ticket) {
                // Eliminar permanentemente del database
                $deletedTickets[] = [
                    'ticket_id' => $ticket->id,
                    'title' => $ticket->title
                ];
                
                $ticket->delete(); // Eliminación física
                $deletedCount++;
                
                Log::info('Legacy ticket permanently deleted:', [
                    'ticket_id' => $ticket->id,
                    'title' => $ticket->title
                ]);
            }
            
            $response = [
                'message' => "Successfully permanently deleted {$deletedCount} legacy ticket(s)",
                'deleted_count' => $deletedCount,
                'deleted_tickets' => $deletedTickets
            ];
            
            Log::info('Legacy tickets permanently deleted:', $response);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Legacy tickets deletion error: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting legacy tickets', 'error' => $e->getMessage()], 500);
        }
    }
}