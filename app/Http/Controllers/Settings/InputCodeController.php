<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InputCodeController extends Controller
{
    public function index()
    {
        if (!session('input_code_unlocked')) {
            return view('settings.input-code.lock');
        }
        return view('settings.input-code.index');
    }

    public function unlock(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if ($request->password === 'abdidzikry31') {
            session(['input_code_unlocked' => true]);
            return redirect()->route('settings.input-code.index');
        }

        return back()->with('error', 'Incorrect access code.');
    }

    public function execute(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
        ]);

        $query = $request->input('query');

        // Split by semicolon, filter empty
        $queries = array_filter(array_map('trim', explode(';', $query)), function ($val) {
            return strlen($val) > 0;
        });

        DB::beginTransaction();
        try {
            $results = [];

            // Use PDO directly to preserve @variables session state
            $pdo = DB::connection()->getPdo();

            foreach ($queries as $q) {
                // Use prepare + execute for ALL statements to handle potential result sets safely
                $stmt = $pdo->prepare($q);
                $stmt->execute();

                // Determine identifying keyword to guess if we should try fetching
                $uQ = strtoupper($q);
                $isSelect = (strpos($uQ, 'SELECT') === 0 || strpos($uQ, 'SHOW') === 0 || strpos($uQ, 'DESCRIBE') === 0);

                if ($isSelect) {
                    try {
                        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                        $results[] = [
                            'query' => $q,
                            'type' => 'SELECT',
                            'data' => $data
                        ];
                    } catch (\Exception $e) {
                        // Fallback if fetch fails (e.g. it wasn't actually a result set)
                        $results[] = ['query' => $q, 'type' => 'STATEMENT', 'affected' => $stmt->rowCount()];
                    }
                } else {
                    $results[] = [
                        'query' => $q,
                        'type' => 'STATEMENT',
                        'affected' => $stmt->rowCount()
                    ];
                }

                // CRITICAL: Close cursor to prevent "Cannot execute queries while other unbuffered queries are active"
                $stmt->closeCursor();
            }

            DB::commit();

            return back()->with('success', 'Code executed successfully.')->with('results', $results);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SQL Input Execution Error: ' . $e->getMessage());
            return back()->with('error', 'Execution Error: ' . $e->getMessage())->withInput();
        }
    }
}
