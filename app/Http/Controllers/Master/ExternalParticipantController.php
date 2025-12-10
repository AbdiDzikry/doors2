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
            'name' => 'required',
            'email' => 'required|email|unique:external_participants',
            'type' => 'required|in:internal,external',
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
            'name' => 'required',
            'email' => 'required|email|unique:external_participants,email,'.$externalParticipant->id,
            'type' => 'required|in:internal,external',
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
        $template = new ExternalParticipantsTemplateExport();
        // FastExcel does not have a direct way to create a header-only file.
        // We can create a collection containing only the headers and export that.
        $headings = $template->headings();
        $data = collect([$headings]);
        return (new FastExcel($data))->download('external_participants_template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            (new FastExcel)->import($request->file('file'), function ($line) {
                ExternalParticipant::create([
                    'name'       => $line['NAME'] ?? $line['name'] ?? null,
                    'email'      => $line['EMAIL'] ?? $line['email'] ?? null,
                    'phone'      => $line['PHONE'] ?? $line['phone'] ?? null,
                    'company'    => $line['COMPANY'] ?? $line['company'] ?? null,
                    'department' => $line['DEPARTMENT'] ?? $line['department'] ?? null,
                    'address'    => $line['ADDRESS'] ?? $line['address'] ?? null,
                    'type'       => 'external',
                ]);
            });
        } catch (\Exception $e) {
            return redirect()->route('master.external-participants.index')->with('error', 'Error importing file: ' . $e->getMessage());
        }

        return redirect()->route('master.external-participants.index')->with('success', 'External participants imported successfully!');
    }

    public function export()
    {
        $export = new ExternalParticipantsExport();
        $collection = $export->collection();
        $headings = $export->headings();

        return (new FastExcel($collection))->download('external_participants.xlsx', function ($participant) use ($headings) {
            $row = [];
            $row[$headings[0]] = $participant->name;
            $row[$headings[1]] = $participant->email;
            $row[$headings[2]] = $participant->phone;
            $row[$headings[3]] = $participant->company;
            $row[$headings[4]] = $participant->department;
            $row[$headings[5]] = $participant->address;
            return $row;
        });
    }
}
