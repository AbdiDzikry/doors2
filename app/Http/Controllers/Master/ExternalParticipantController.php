<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExternalParticipant;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Exports\ExternalParticipantsExport;
use App\Exports\ExternalParticipantsTemplateExport;

class ExternalParticipantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('master.participants.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master.participants.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|email|unique:external_participants',
            'phone' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'type' => 'required|in:internal,external',
        ], [
            'name.regex' => 'Name format is invalid. Only letters and spaces are allowed.',
            'phone.regex' => 'Phone format is invalid.',
        ]);

        ExternalParticipant::create($request->all());

        return redirect()->route('master.external-participants.index')
                        ->with('success','Participant created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExternalParticipant $externalParticipant)
    {
        return view('master.participants.edit',['participant' => $externalParticipant]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExternalParticipant $externalParticipant)
    {
        $request->validate([
            'name' => 'required|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|email|unique:external_participants,email,'.$externalParticipant->id,
            'phone' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'type' => 'required|in:internal,external',
        ], [
            'name.regex' => 'Name format is invalid. Only letters and spaces are allowed.',
            'phone.regex' => 'Phone format is invalid.',
        ]);

        $externalParticipant->update($request->all());

        return redirect()->route('master.external-participants.index')
                        ->with('success','Participant updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExternalParticipant $externalParticipant)
    {
        $externalParticipant->delete();

        return redirect()->route('master.external-participants.index')
                        ->with('success','Participant deleted successfully');
    }

    public function downloadTemplate()
    {
        if (ob_get_length()) ob_end_clean();
        ob_start();
        // Create an empty row with keys as headers to generating the template structure
        $data = collect([
            [
                'NAME' => 'John Doe',
                'EMAIL' => 'john@example.com',
                'PHONE' => '08123456789',
                'COMPANY' => 'Example Corp',
                'ADDRESS' => 'Jl. Example No. 123',
            ]
        ]);
        
        return (new FastExcel($data))->download('external_participants_template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            (new FastExcel)->import($request->file('file'), function ($line) {
                // Determine keys based on case insensitivity or varations
                $name = $line['NAME'] ?? $line['name'] ?? null;
                $email = $line['EMAIL'] ?? $line['email'] ?? null;
                
                // Skip if essential data is missing
                if (!$name || !$email) {
                    return;
                }

                ExternalParticipant::updateOrCreate(
                    ['email' => $email], // Avoid duplicates by email
                    [
                        'name'       => $name,
                        'phone'      => $line['PHONE'] ?? $line['phone'] ?? null,
                        'company'    => $line['COMPANY'] ?? $line['company'] ?? null,
                        'address'    => $line['ADDRESS'] ?? $line['address'] ?? null,
                        'type'       => 'external',
                    ]
                );
            });
        } catch (\Exception $e) {
            return redirect()->route('master.external-participants.index')->with('error', 'Error importing file: ' . $e->getMessage());
        }

        return redirect()->route('master.external-participants.index')->with('success', 'External participants imported successfully!');
    }

    public function export()
    {
        if (ob_get_length()) ob_end_clean();
        ob_start();
        $participants = ExternalParticipant::all();

        // Helper function to sanitize string for XML (copied from MeetingListController)
        $sanitize = function ($value) {
            if ($value === null) {
                return '';
            }
            if (!is_string($value)) {
                $value = (string) $value;
            }
            // Ensure valid UTF-8
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
            // Remove control characters (including newlines for safer CSV/Excel cells)
            $value = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
            // Normalize whitespace
            $value = preg_replace('/\s+/', ' ', $value);
            return trim($value);
        };

        return (new FastExcel($participants))->download('external_participants.xlsx', function ($participant) use ($sanitize) {
            return [
                'NAME' => $sanitize($participant->name),
                'EMAIL' => $sanitize($participant->email),
                'PHONE' => $sanitize($participant->phone),
                'COMPANY' => $sanitize($participant->company),
                'ADDRESS' => $sanitize($participant->address),
            ];
        });
    }
}
