<?php

namespace App\Http\Controllers;

use App\Exports\StudentSurveyUnfilledExport;
use App\Imports\StudentImport;
use App\Models\ProgramStudy;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Exception;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $prodi = $request->input('prodi');
        $tahun = $request->input('tahun');

        $query = Student::with('programStudy');

        if ($prodi) {
            $query->where('program_study_id', $prodi);
        }

        if ($tahun) {
            $query->whereYear('graduation_date', $tahun);
        }

        $students = $query->get();

        $program_studies = ProgramStudy::all();

        return view('admin.student.index', compact('students', 'program_studies', 'prodi', 'tahun'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nim' => 'required|unique:students,nim',
            'name' => 'required|string|max:255',
            'graduation_date' => 'required|date',
            'program_study_id' => 'required|exists:program_studies,id',
        ]);

        $validated['graduation_date'] = Carbon::parse($request->graduation_date);

        try {
            Student::create($validated);
            return redirect()->back()->with('success', 'Mahasiswa berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $student = Student::where('nim', $id)->firstOrFail();

        $validated = $request->validate([
            'nim' => 'required|unique:students,nim,' . $student->nim . ',nim',
            'name' => 'required|string|max:255',
            'graduation_date' => 'required|date',
            'program_study_id' => 'required|exists:program_studies,id',
        ]);

        try {
            $student->update($validated);
            return redirect()->back()->with('success', 'Data mahasiswa berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengupdate data');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $student = Student::where('nim', $id)->firstOrFail();
            $student->delete();

            return redirect()->back()->with('success', 'Mahasiswa berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data');
        }
    }

    public function import(Request $request)
    {
        try {
            $validated = $request->validate([
                'file' => 'required|file|mimes:xls,xlsx'
            ]);

            Excel::import(new StudentImport, $validated['file']);

            return back()->with('success', 'Data mahasiswa berhasil diimport');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return back()
                ->with('error', 'Terjadi kesalahan saat mengimport data: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportStudentUnfilled(Request $request)
    {
        return Excel::download(new StudentSurveyUnfilledExport, 'mahasiswa-belum-isi-survey.xlsx');
    }
}
