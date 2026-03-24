<nav class="sidebar sidebar-offcanvas mt-0" id="sidebar">
    <!-- User Profile Section -->
    <div class="sidebar-profile text-center p-3">
        @if (auth()->user()->profile_pic)
            <img src="{{ 'data:image/jpeg;base64,' . auth()->user()->profile_pic }}" alt="Profile Picture"
                class="rounded-circle shadow" style="width: 80px; height: 80px; object-fit: cover;">
        @else
            <i class="bi bi-person-circle" style="font-size: 3rem; color: #fff;"></i>
        @endif

        <div class="sidebar-profile-info mt-2">
            <span class="sidebar-profile-name truncate-text">{{ auth()->user()->name }}</span>
            <span class="sidebar-profile-email text-light truncate-text">
                <small>{{ auth()->user()->email }}</small>
            </span>
        </div>
        <div class="d-block d-sm-none mt-1">
            <small>Referral Code:</small>
            <p class="badge bg-danger">{{ ucwords(auth()->user()->referral_code) }}</p>
        </div>
    </div>

    <ul class="nav">

        <!-- Dashboard Section -->
        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.dashboard') ? 'active' : '' }}" href="{{ route('user.dashboard') }}">
                <i class="mdi mdi-view-dashboard menu-icon"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.wallet') ? 'active' : '' }}" href="{{ route('user.wallet') }}">
                <i class="mdi mdi-wallet menu-icon"></i>
                <span class="menu-title">Fund Wallet</span>
            </a>
        </li>

        <!-- Verification Section -->

        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.verify-tin') ? 'active' : '' }}"
                href="{{ route('user.verify-tin') }}">
                <i class="mdi mdi-file-certificate menu-icon"></i>
                <span class="menu-title">Generate TIN</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.verify-nin') ? 'active' : '' }}"
                href="{{ route('user.verify-nin') }}">
                <i class="mdi mdi-fingerprint menu-icon"></i>
                <span class="menu-title">Verify NIN</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.verify-nin2') ? 'active' : '' }}"
                href="{{ route('user.verify-nin2') }}">
                <i class="mdi mdi-fingerprint menu-icon"></i>
                <span class="menu-title">Verify NIN V2</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.verify-nin4') ? 'active' : '' }}"
                href="{{ route('user.verify-nin4') }}">
                <i class="mdi mdi-fingerprint menu-icon"></i>
                <span class="menu-title">Verify NIN V3</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.verify-nin5') ? 'active' : '' }}"
                href="{{ route('user.verify-nin5') }}">
                <i class="mdi mdi-fingerprint menu-icon"></i>
                <span class="menu-title">Verify NIN V4</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.verify-nin6') ? 'active' : '' }}"
                href="{{ route('user.verify-nin6') }}">
                <i class="mdi mdi-fingerprint menu-icon"></i>
                <span class="menu-title">Verify NIN V5</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link  {{ Route::is('user.verify-nin-phone') ? 'active' : '' }}"
                href="{{ route('user.verify-nin-phone') }}">
                <i class="mdi mdi-phone menu-icon"></i>
                <span class="menu-title">Verify NIN PHONE</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link  {{ Route::is('user.verify-phone-v5') ? 'active' : '' }}"
                href="{{ route('user.verify-phone-v5') }}">
                <i class="mdi mdi-phone menu-icon"></i>
                <span class="menu-title">NIN PHONE V2</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.verify-demo') ? 'active' : '' }}"
                href="{{ route('user.verify-demo') }}">
                <i class="mdi mdi-account-group menu-icon"></i>
                <span class="menu-title">NIN Demographic</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.verify-demo-v5') ? 'active' : '' }}"
                href="{{ route('user.verify-demo-v5') }}">
                <i class="mdi mdi-account-group menu-icon"></i>
                <span class="menu-title">NIN Demographic V2</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.ipe.v3') ? 'active' : '' }}" href="{{ route('user.ipe.v3') }}">
                <i class="mdi mdi-magnify menu-icon"></i>
                <span class="menu-title">IPE</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.nin.delink') ? 'active' : '' }}"
                href="{{ route('user.nin.delink') }}">
                <i class="mdi mdi-link-off menu-icon"></i>
                <span class="menu-title">NIN Delink</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.verify-bvn') ? 'active' : '' }}"
                href="{{ route('user.verify-bvn') }}">
                <i class="mdi mdi-fingerprint menu-icon"></i>
                <span class="menu-title">Verify BVN</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.personalize-nin') ? 'active' : '' }}"
                href="{{ route('user.personalize-nin') }}">
                <i class="mdi mdi-magnify menu-icon"></i>
                <span class="menu-title">Personalize</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.bvn-enrollment') ? 'active' : '' }}"
                href="{{ route('user.bvn-enrollment') }}">
                <i class="mdi mdi-account-plus menu-icon"></i>
                <span class="menu-title">BVN User Request</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.business.create') ? 'active' : '' }}"
                href="{{ route('user.business.create') }}">
                <i class="mdi  mdi-pencil-outline menu-icon"></i>
                <span class="menu-title">CAC (BUSINESS Name)</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.company.create') ? 'active' : '' }}"
                href="{{ route('user.company.create') }}">
                <i class="mdi  mdi-pencil-outline menu-icon"></i>
                <span class="menu-title">CAC (COMPANY REG)</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.bvn-phone-search') ? 'active' : '' }}"
                href="{{ route('user.bvn-phone-search') }}">
                <i class="mdi mdi-magnify menu-icon"></i>
                <span class="menu-title">BVN Search</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.nin.services') ? 'active' : '' }}"
                href="{{ route('user.nin.services') }}">
                <i class="mdi mdi-autorenew menu-icon"></i>

                <span class="menu-title">NIN Validation</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.bank-services.index') ? 'active' : '' }}"
                href="{{ route('user.bank-services.index') }}">
                <i class="mdi mdi-pencil-outline menu-icon"></i>
                <span class="menu-title">BVN Modification</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.nin.mod') ? 'active' : '' }}" href="{{ route('user.nin.mod') }}">
                <i class="mdi mdi-pencil menu-icon"></i>
                <span class="menu-title">NIN Modification</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Route::is('user.nin.mod.ipe') ? 'active' : '' }}"
                href="{{ route('user.nin.mod.ipe') }}">
                <i class="mdi mdi-pencil menu-icon"></i>
                <span class="menu-title">Mod IPE Clearance</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('user.support') }}">
                <i class="mdi mdi-lifebuoy menu-icon"></i>
                <span class="menu-title">Support</span>
            </a>
        </li>
        <li class="nav-item {{ Route::is('user.profile') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('user.profile') }}">
                <i class="mdi mdi-account-circle menu-icon"></i>
                <span class="menu-title">Profile</span>
            </a>
        </li>
        <li class="nav-item {{ Route::is('user.api.docs') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('user.api.docs') }}">
                <i class="mdi mdi-code-braces menu-icon"></i>
                <span class="menu-title">API Docs </span>
            </a>
        </li>
        <!-- Admin Section -->
        @if (auth()->user()->role == 'admin')
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleSubmenu(event, 'adminSubmenu')">
                    <i class="mdi mdi-cog-outline menu-icon"></i>
                    <span class="menu-title">Manage</span>
                    <i class="mdi mdi-chevron-down ms-auto"></i>
                </a>
                <ul class="sub-menu nav flex-column ps-4" id="adminSubmenu">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.services.index') }}">
                            <i class="mdi mdi-pencil menu-icon"></i> Services
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.users.index') ? 'active' : '' }}"
                            href="{{ route('admin.users.index') }}">
                            <i class="mdi mdi mdi-account-multiple menu-icon"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.enroll.index') ? 'active' : '' }}"
                            href="{{ route('admin.enroll.index') }}">
                            <i class="mdi mdi-pencil menu-icon"></i>BVN User Requests
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.nin.services.list') }}">
                            <i class="mdi mdi-autorenew menu-icon"></i> NIN Validations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.delink.services.list') ? 'active' : '' }}"
                            href="{{ route('admin.delink.services.list') }}">
                            <i class="mdi mdi-link-off menu-icon"></i> NIN Delink
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.bvn.services.list') ? 'active' : '' }}"
                            href="{{ route('admin.bvn.services.list') }}">
                            <i class="mdi mdi-tools menu-icon"></i>BVN Services
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.mod.services.list') ? 'active' : '' }}"
                            href="{{ route('admin.mod.services.list') }}">
                            <i class="mdi mdi-pencil menu-icon"></i>NIN Modifications
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.bvn-modification.index') ? 'active' : '' }}"
                            href="{{ route('admin.bvn-modification.index') }}">
                            <i class="mdi mdi-pencil-outline menu-icon"></i>
                            <span class="menu-title">BVN Modification</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.bank-services.manage') ? 'active' : '' }}"
                            href="{{ route('admin.bank-services.manage') }}">
                            <i class="mdi mdi-cash-multiple menu-icon"></i>
                            <span class="menu-title">Bank Prices</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.ipe.index') ? 'active' : '' }}"
                            href="{{ route('admin.ipe.index') }}">
                            <i class="mdi mdi-magnify menu-icon"></i>
                            IPE Clearance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.modipe.index') ? 'active' : '' }}"
                            href="{{ route('admin.modipe.index') }}">
                            <i class="mdi mdi-pencil menu-icon"></i>Mod IPE Clearance
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.view-request3') ? 'active' : '' }}"
                            href="{{ route('admin.business-reg') }}">
                            <i class="mdi mdi-pencil menu-icon"></i>Biz Name (CAC)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.company.index') ? 'active' : '' }}"
                            href="{{ route('admin.company.index') }}">
                            <i class="mdi mdi-pencil menu-icon"></i>Company Reg (CAC)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.popup.index') ? 'active' : '' }}"
                            href="{{ route('admin.popup.index') }}">
                            <i class="mdi mdi-window-restore menu-icon"></i> Popup
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('admin.transactions') ? 'active' : '' }}"
                            href="{{ route('admin.transactions') }}">
                            <i class="mdi mdi-receipt-text-outline menu-icon"></i>
                            All Transactions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('site-settings.edit') ? 'active' : '' }}"
                            href="{{ route('admin.site-settings.edit') }}">
                            <i class="mdi mdi-cog menu-icon"></i>
                            Site Settings
                        </a>
                    </li>

                </ul>
            </li>
        @endif
        <!-- Logout Section -->
        <li class="nav-item">
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <a class="nav-link d-flex align-items-center" style="margin-left:14px;" href="#"
                    onclick="event.preventDefault(); this.closest('form').submit();">
                    <i class="mdi mdi-logout menu-icon"></i>
                    <span class="menu-title">Logout</span>
                </a>
            </form>
        </li>
    </ul>
</nav>
