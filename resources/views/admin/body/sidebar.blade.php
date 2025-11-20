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

                @if (Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Admin'))
                    <li>
                        <a href="{{ route('all.category') }}" class="tp-link">
                            <i data-feather="package"></i>
                            Kategori Produk
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('all.product') }}" class="tp-link">
                            <i data-feather="package"></i>
                            Produk
                        </a>
                    </li>
                @endif

                @if (Auth::user()->hasRole('Super Admin'))
                    <li>
                        <a href="{{ route('all.supplier') }}" class="tp-link">
                            <i data-feather="users"></i>
                            Supplier
                        </a>
                    </li>
                @endif

                @if (Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Admin'))
                    <li>
                        <a href="{{ route('all.customer') }}" class="tp-link">
                            <i data-feather="users"></i>
                            Pelanggan
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('all.sale') }}" class="tp-link">
                            <i data-feather="shopping-bag"></i>
                            Penjualan
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('all.service') }}" class="tp-link">
                            <i data-feather="pen-tool"></i>
                            Jasa Jahit
                        </a>
                    </li>
                @endif

                <li>
                    <a href="{{ route('all.tailor') }}" class="tp-link">
                        <i data-feather="pen-tool"></i>
                        Transaksi Jahit
                    </a>
                </li>

                @if (Auth::user()->hasRole('Super Admin'))
                    <li>
                        <a href="{{ route('profit.distribution.report') }}" class="tp-link">
                            <i data-feather="dollar-sign"></i>
                            Laporan Profit
                        </a>
                    </li>
                @endif

                @if (Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Admin'))
                    <li>
                        <a href="{{ route('all.expense') }}" class="tp-link">
                            <i data-feather="dollar-sign"></i>
                            Pengeluaran
                        </a>
                    </li>
                @endif

                @if (Auth::user()->hasRole('Super Admin'))
                    <li>
                    <a href="{{ route('all.purchase') }}" class="tp-link">
                        <i data-feather="aperture"></i>
                            Pembelian Barang
                        </a>
                    </li>
                @endif

                @if (Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Admin'))
                <li>
                    <a href="{{ route('all.production') }}" class="tp-link">
                        <i data-feather="pen-tool"></i>
                        Produksi
                    </a>
                </li>
                @endif

                <li>
                    <a href="{{ route('attendances.index') }}" class="tp-link">
                        <i data-feather="check-square"></i>
                        Presensi
                    </a>
                </li>

                <li>
                    <a href="{{ route('payroll.history') }}" class="tp-link">
                        <i data-feather="briefcase"></i>
                        Gaji/Komisi
                    </a>
                </li>

                @if (Auth::user()->hasRole('Super Admin'))
                    <li>
                        <a href="{{ route('all.acceptance') }}" class="tp-link">
                            <i data-feather="briefcase"></i>
                            Penerimaan Eksternal
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('financial.index') }}" class="tp-link">
                            <i data-feather="briefcase"></i>
                            Arus Kas
                        </a>
                    </li>
                @endif

                {{-- 
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
                </li> --}}

                @if (Auth::user()->hasRole('Super Admin'))
                    <li>
                        <a href="{{ route('all.admin') }}" class="tp-link">
                            <i data-feather="map"></i>
                            Kelola Pengguna
                        </a>
                    </li>
                @endif
            </ul>

        </div>
        <!-- End Sidebar -->

        <div class="clearfix"></div>

    </div>
</div>