<div class="app-sidebar-menu">
    <div class="h-100" data-simplebar>

        <!--- Sidemenu -->
        <div id="sidebar-menu">

            <div class="logo-box text-center">
                <a href="{{ route('dashboard') }}" class="logo logo-light">
                    <span class="logo-lg">
                        <img src="{{ asset('backend/assets/images/logo.png')}}" alt="" height="54">
                    </span>
                </a>
                <a href="{{ route('dashboard') }}" class="logo logo-dark">
                    <span class="logo-lg">
                        <img src="{{ asset('backend/assets/images/logo.png')}}" alt="" height="54">
                    </span>
                </a>
            </div>

            <ul id="side-menu">

                <li class="menu-title">Menu</li>

                <li>
                    <a href="{{ route('dashboard') }}" class="tp-link">
                        <i data-feather="home"></i>
                        <span> Dashboard </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('all.category') }}" class="tp-link">
                        <i data-feather="home"></i>
                        Kategori Produk
                    </a>
                </li>

                <li>
                    <a href="{{ route('all.product') }}" class="tp-link">
                        <i data-feather="home"></i>
                        Produk
                    </a>
                </li>

                <li>
                    <a href="{{ route('all.customer') }}" class="tp-link">
                        <i data-feather="users"></i>
                        Pelanggan
                    </a>
                </li>


                {{-- <li>
                    <a href="#Purchase" data-bs-toggle="collapse">
                    <i data-feather="aperture"></i>
                        <span> Purchase Manage </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="Purchase">
                        <ul class="nav-second-level">
                            <li>
                                <a href="{{ route('all.purchase') }}" class="tp-link">All Purchase</a>
                            </li>   
                            <li>
                                <a href="{{ route('all.return.purchase') }}" class="tp-link">Purchase Return</a>
                            </li>         
                        </ul>
                    </div>
                </li>

                <li>
                    <a href="#Sale" data-bs-toggle="collapse">
                    <i data-feather="award"></i>
                        <span> Sale Manage </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="Sale">
                        <ul class="nav-second-level">
                            <li>
                                <a href="{{ route('all.sale') }}" class="tp-link">All Sale</a>
                            </li>   
                            <li>
                                <a href="{{ route('all.sale.return') }}" class="tp-link">Sale Return</a>
                            </li>        
                        </ul>
                    </div>
                </li>

                <li>
                    <a href="#Due" data-bs-toggle="collapse">
                    <i data-feather="briefcase"></i>
                        <span> Due Setup </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="Due">
                        <ul class="nav-second-level">
                            <li>
                                <a href="{{ route('due.sale') }}" class="tp-link">Sales Due</a>
                            </li>
                            <li>
                                <a href="{{ route('due.sale.return') }}" class="tp-link">Sales Return Due</a>
                            </li>
                            
                        </ul>
                    </div>
                </li>

                <li>
                    <a href="#Transfers" data-bs-toggle="collapse">
                        <i data-feather="table"></i>
                        <span> Transfers Setup </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="Transfers">
                        <ul class="nav-second-level">
                            <li>
                                <a href="{{ route('all.transfer') }}" class="tp-link">Transfers </a>
                            </li>
                            
                            
                        </ul>
                    </div>
                </li>

                <li>
                    <a href="#Report" data-bs-toggle="collapse">
                    <i data-feather="pie-chart"></i>
                        <span> Report Setup </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="Report">
                        <ul class="nav-second-level">
                            <li>
                                <a href="{{ route('all.report') }}" class="tp-link">All Reports </a>
                            </li>
                            
                            
                        </ul>
                    </div>
                </li>

                <li>
                    <a href="#sidebarBaseui" data-bs-toggle="collapse">
                        <i data-feather="package"></i>
                        <span> Role & Permission </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarBaseui">
                    <ul class="nav-second-level">
                        <li>
                            <a href="{{ route('all.permission') }}" class="tp-link">All Permission</a>
                        </li>
                        <li>
                            <a href="{{ route('all.roles') }}" class="tp-link">All Roles</a>
                        </li>

                        <li>
                            <a href="{{ route('add.roles.permission') }}" class="tp-link">Role In Permission</a>
                        </li>
                        <li>
                            <a href="{{ route('all.roles.permission') }}" class="tp-link">All Role Permission</a>
                        </li>
                        
                    </ul>
                    </div>
                </li>

                <li>
                    <a href="#Admin" data-bs-toggle="collapse">
                    <i data-feather="map"></i>
                        <span> Manage Admin </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="Admin">
                        <ul class="nav-second-level">
                            <li>
                                <a href="{{ route('all.admin') }}" class="tp-link">All Admin</a>
                            </li> 
                            
                        </ul>
                    </div>
                </li> --}}
            </ul>

        </div>
        <!-- End Sidebar -->

        <div class="clearfix"></div>

    </div>
</div>