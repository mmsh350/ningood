<nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    <div class="navbar-menu-wrapper d-flex align-items-center justify-content-between px-2 px-md-4">
        <!-- Left Side: Toggle (Desktop) & Brand -->
        <div class="d-flex align-items-center">
            <button class="navbar-toggler align-self-center border-0 bg-transparent p-0 d-none d-lg-block me-4"
                type="button" data-bs-toggle="minimize">
                <i class="mdi mdi-menu text-slate-600 fs-4"></i>
            </button>

            <a class="navbar-brand brand-logo d-flex align-items-center gap-2" href="{{ route('user.dashboard') }}"
                style="width: auto !important;">
                <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center text-primary">
                    <i class="bi bi-shield-check"></i>
                </div>
                <span class="text-xl font-bold text-slate-900 tracking-tight">Ningood</span>
            </a>
        </div>

        <!-- Center Side: Search (Desktop only) -->
        <div class="search-field d-none d-xl-block">
            <div class="input-group bg-slate-50 rounded-xl px-3 border-0">
                <span class="input-group-text bg-transparent border-0 p-0 me-2 text-slate-400">
                    <i class="mdi mdi-magnify fs-5"></i>
                </span>
                <input type="text" class="form-control bg-transparent border-0 ps-0 text-sm py-2"
                    placeholder="Search services..." aria-label="search">
            </div>
        </div>

        <!-- Right Side: Profile & Mobile Toggle -->
        <div class="navbar-nav-right d-flex align-items-center gap-2">
            <div class="dropdown">
                <a class="nav-link d-flex align-items-center gap-2 gap-md-3 bg-slate-50 px-2 px-md-4 py-2 rounded-xl transition hover:bg-slate-100"
                    href="#" data-bs-toggle="dropdown" id="profileDropdown">
                    <div class="user-info text-end d-none d-sm-block">
                        <p class="mb-0 text-slate-900 font-bold text-xs">{{ auth()->user()->name }}</p>
                        <p class="mb-0 text-slate-400 text-[10px] uppercase font-bold tracking-wider">Ref:
                            {{ auth()->user()->referral_code }}</p>
                    </div>
                    @if (auth()->user()->profile_pic)
                        <img src="{{ 'data:image/;base64,' . auth()->user()->profile_pic }}"
                            class="rounded-lg shadow-sm" alt="Profile"
                            style="width: 32px; height: 32px; object-fit: cover;" />
                    @else
                        <div
                            class="w-8 h-8 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center font-bold shadow-sm">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    @endif
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-2xl p-2 mt-2" aria-labelledby="profileDropdown">
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item rounded-xl py-2 d-flex align-items-center gap-2 text-danger hover:bg-red-50 transition border-0 w-100 text-start">
                                <i class="bi bi-box-arrow-right fs-6"></i>
                                <span class="font-bold text-sm">Logout</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

            <button class="navbar-toggler align-self-center border-0 bg-transparent p-0 d-lg-none" type="button"
                data-bs-toggle="offcanvas">
                <i class="mdi mdi-menu text-slate-600 fs-4"></i>
            </button>
        </div>
    </div>
</nav>
