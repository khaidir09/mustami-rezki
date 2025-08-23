<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\TailorCommission;
use App\Models\TailorTransaction;
use App\Models\ProfitDistribution;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function AdminLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
    // End Method 

    public function AdminProfile()
    {
        $id = Auth::user()->id;
        $profileData = User::find($id);
        return view('admin.admin_profile', compact('profileData'));
    }
    // End Method 

    public function ProfileStore(Request $request)
    {
        $id = Auth::user()->id;
        $data = User::find($id);

        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone = $request->phone;
        $data->address = $request->address;

        $oldPhotoPath = $data->photo;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/user_images'), $filename);
            $data->photo = $filename;

            if ($oldPhotoPath && $oldPhotoPath !== $filename) {
                $this->deleteOldImage($oldPhotoPath);
            }
        }

        $data->save();

        $notification = array(
            'message' => 'Profile Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
    // End Method

    private function deleteOldImage(string $oldPhotoPath): void
    {
        $fullPath = public_path('upload/user_images/' . $oldPhotoPath);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
    // End private Method

    public function AdminPasswordUpdate(Request $request)
    {

        $user = Auth::user();
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed'
        ]);

        if (!Hash::check($request->old_password, $user->password)) {

            $notification = array(
                'message' => 'Old Password Does not Match!',
                'alert-type' => 'error'
            );
            return back()->with($notification);
        }

        User::whereId($user->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        Auth::logout();

        $notification = array(
            'message' => 'Password Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('login')->with($notification);
    }
    // End Method

    public function AdminDashboard()
    {
        $user = Auth::user();
        $data = [];

        $data['productCount'] = Product::count();
        $data['lowStockCount'] = Product::where('product_qty', '<=', 3)->count();
        $data['stockValue'] = Product::select(DB::raw('SUM(modal * product_qty) as total_value'))
            ->value('total_value');
        $data['ongoingJobs'] = TailorTransaction::whereIn('status', ['Antrian', 'Dikerjakan'])->count();

        $data['totalModal'] = ProfitDistribution::where('distribution_type', 'pengembangan_modal')
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->sum('amount');
        $data['totalPribadi'] = ProfitDistribution::where('distribution_type', 'pribadi')
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->sum('amount');
        $data['totalSedekah'] = ProfitDistribution::where('distribution_type', 'sedekah')
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->sum('amount');
        $data['completedJobsThisMonth'] = TailorTransaction::whereIn('status', ['Selesai', 'Diambil'])
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->count();
        $data['tailorOwnerProfit'] = ProfitDistribution::where('transaction_type', 'App\Models\TailorTransaction')
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->sum('amount');

        // Cek jika user adalah Penjahit, ambil data personal
        if ($user->hasRole('Tailor')) {
            $data['assignedJobs'] = TailorTransaction::where('tailor_id', $user->id)
                ->whereIn('status', ['Antrian', 'Dikerjakan'])
                ->count();

            $data['completedJobsThisMonth'] = TailorTransaction::where('tailor_id', $user->id)
                ->whereIn('status', ['Selesai', 'Diambil'])
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->count();

            $data['pendapatanPenjahit'] = TailorCommission::where('user_id', $user->id)
                ->sum('amount');
        }


        return view('admin.index', $data);
    }
}
