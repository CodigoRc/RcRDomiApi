<?php
namespace App\Http\Controllers;

use App\Models\MailLabel;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory as ValidationFactory;

class MailLabelController extends Controller
{
    protected $validationFactory;

    public function __construct(ValidationFactory $validationFactory)
    {
        $this->validationFactory = $validationFactory;
    }

    public function index()
    {
        $mailLabels = MailLabel::all();
        return response()->json($mailLabels);
    }

    public function store(Request $request)
    {
        // Validar los datos recibidos manualmente
        $validator = $this->validationFactory->make($request->all(), [
            'title' => 'required|string|max:255',
            'color' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Obtener manualmente los valores de title, color y slug del request
        $title = $request->input('title');
        $color = $request->input('color');
        $slug = $request->input('slug', strtolower($title)); // Si no se proporciona slug, usar title en minúsculas

        // Crear una nueva etiqueta de correo
        $mailLabel = MailLabel::create([
            'title' => $title,
            'color' => $color,
            'slug' => $slug,
        ]);

        // Devolver la nueva etiqueta de correo en la respuesta
        return response()->json($mailLabel, 201);
    }

    public function show($id)
    {
        $mailLabel = MailLabel::findOrFail($id);
        return response()->json($mailLabel);
    }

    public function update(Request $request, $id)
    {
        $mailLabel = MailLabel::findOrFail($id);
        $mailLabel->update($request->all());
        return response()->json($mailLabel);
    }

    public function destroy($id)
    {
        MailLabel::destroy($id);
        return response()->json(null, 204);
    }
}