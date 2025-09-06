<?php

namespace App\Http\Controllers\Backend;

use Carbon\Carbon;
use App\Models\Salary;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $attendance = Attendance::latest()->get();
        $today = Carbon::today();
        $user = Auth::user();
        $attendanceToday = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $query = Attendance::latest();

        if ($user->hasRole('Tailor') || $user->hasRole('Admin')) {
            // Jika ya, tambahkan kondisi where untuk memfilter berdasarkan ID penjahit
            $query->where('user_id', $user->id);
        }

        $attendance = $query->get();

        return view('admin.backend.attendance.all_attendance', compact('attendance', 'attendanceToday'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();

        // Cek apakah sudah absen hari ini
        $attendanceToday = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendanceToday && $attendanceToday->check_out) {
            return redirect()->back()->with('error', 'Anda sudah melakukan absen pulang hari ini.');
        }

        // Proses Check-in
        if (!$attendanceToday) {
            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'check_in' => Carbon::now(),
                'status' => 'Hadir',
            ]);

            // Jika yang absen adalah Kasir, catat gaji hariannya
            if ($user->hasRole('Admin')) {
                Salary::create([
                    'user_id'       => $user->id,
                    'type'          => 'Gaji Harian', // Jenis pembayaran
                    'amount'        => 30000,   // Jumlah gaji
                    'payment_date'  => $today,         // Tanggal pembayaran = tanggal absen
                    'description'   => 'Gaji harian otomatis dari presensi tanggal ' . $today->format('d-m-Y'),
                ]);
            }
            return redirect()->back()->with('success', 'Berhasil absen masuk!');
        }

        // Proses Check-out
        if ($attendanceToday && !$attendanceToday->check_out) {
            $attendanceToday->update([
                'check_out' => Carbon::now(),
            ]);
            return redirect()->back()->with('success', 'Berhasil absen pulang!');
        }

        return redirect()->back()->with('info', 'Tidak ada aksi presensi yang dapat dilakukan.');
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
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
