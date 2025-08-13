<div class="topbar-custom">
    <div class="container-xxl">
        <div class="d-flex justify-content-between">
            <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">
                <li>
                    <button class="button-toggle-menu nav-link ps-0">
                        <i data-feather="menu" class="noti-icon"></i>
                    </button>
                </li>
            </ul>

<ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">

    <li class="d-none d-sm-flex">
        <button type="button" class="btn nav-link" data-toggle="fullscreen">
            <i data-feather="maximize" class="align-middle fullscreen noti-icon"></i>
        </button>
    </li>

    @php
        $id = Auth::user()->id;
        $profileData = App\Models\User::find($id);
    @endphp            

                <li class="dropdown notification-list topbar-dropdown">
                    <a class="nav-link dropdown-toggle nav-user me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <img src="{{ (!empty($profileData->photo)) ? url('upload/user_images/'.$profileData->photo) : url('upload/no_image.jpg') }}" alt="user-image" class="rounded-circle">
                        <span class="pro-user-name ms-1">
                            {{ $profileData->name }} <i class="mdi mdi-chevron-down"></i> 
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end profile-dropdown ">
                        <!-- item-->
                        <div class="dropdown-header noti-title">
                            <h6 class="text-overflow m-0 text-uppercase">{{ Auth::user()->role }}</h6>
                        </div>

                        <!-- item-->
                        <a href="{{ route('admin.profile') }}" class="dropdown-item notify-item">
                            <i class="mdi mdi-account-circle-outline fs-16 align-middle"></i>
                            <span>Pengaturan Akun</span>
                        </a>

                        <!-- item-->
                        <a href="{{ route('admin.logout') }}" class="dropdown-item notify-item">
                            <i class="mdi mdi-location-exit fs-16 align-middle"></i>
                            <span>Keluar</span>
                        </a>

                    </div>
                </li>

            </ul>
        </div>

    </div>
    
</div>