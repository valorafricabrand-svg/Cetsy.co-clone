                <nav class="navbar navbar-top fixed-top navbar-expand" id="navbarDefault" style="display:none;">
                    <div class="collapse navbar-collapse justify-content-between">
                        <div class="navbar-logo">
                            <button class="btn navbar-toggler navbar-toggler-humburger-icon hover-bg-transparent" type="button"
                            data-bs-toggle="collapse" data-bs-target="#navbarVerticalCollapse"
                            aria-controls="navbarVerticalCollapse" aria-expanded="false" aria-label="Toggle Navigation">
                            <span class="navbar-toggle-icon"><span class="toggle-line"></span></span>
                        </button>
                        <a class="navbar-brand me-1 me-sm-3" href="{{ url('/') }}">
                            <div class="d-flex align-items-center">
                                <!-- <img src="{{ favicon_url() }}" alt="b2b" width="27" /> -->
                                <h5 class="logo-text ms-2 d-none d-sm-block" style="color: #027333;">Cetsy</h5>
                            </div>
                        </a>
                    </div>


                   

                    <ul class="navbar-nav navbar-nav-icons flex-row">



      

                        <li class="nav-item dropdown">
                            <a class="nav-link lh-1 pe-0" id="navbarDropdownUser" href="#!" role="button" data-bs-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <span class="fs-8">{{ shop() }}</span>
                            <i class="fas fa-angle-down"></i>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end navbar-dropdown-caret py-0 dropdown-profile shadow border"
                        aria-labelledby="navbarDropdownUser">
                        <div class="card position-relative border-0">
                            <div class="card-body p-0">
                                <div class="text-center pt-4 pb-3">
                                    <div class="avatar avatar-xl">
                                        <img class="rounded-circle" src="{{ Auth::user()->get_gravatar(150) }}" alt="" />
                                    </div>
                                    <h6 class="mt-2 text-body-emphasis">{{ shop() }} </h6>
                                </div>
                            </div>
                            <div>
                                @if(Auth::user()->isSeller())
                                @if(Auth::user()->package && package(Auth::user()->package)->name == "Basic")
                                <div class="help-box text-center">
                                    <p class="mb-3 mt-2 text-muted">
                                        <strong>{{ package(Auth::user()->package)->name }}</strong><br>
                                        Upgrade your plan and get the most out of Fedhatrac
                                    </p>
                                    <div class="mt-3">
                                        <a href="{{ url('subscribe') }}" class="btn btn-success">Upgrade now</a>
                                    </div>
                                </div>
                                @endif
                                @endif

                                <ul class="nav d-flex flex-column mb-2 pb-1">
                                    @if(Auth::user()->isSeller() && Auth::user()->tenant_id)
                                    <li class="nav-item">
                                        <a class="nav-link px-3 d-block" href="{{ route('settings.index') }}">
                                            <span class="me-2 text-body align-bottom" data-feather="pie-chart"></span>Account settings
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link px-3 d-block" href="{{ route('communications.top_sms') }}">
                                            <span class="me-2 text-body align-bottom" data-feather="pie-chart"></span>SMS Balance
                                            <span class="badge bg-secondary" style="font-size: 0.85rem;">
                                                {{ wallet(Auth::user()->tenant_id) }}
                                            </span>
                                        </a>
                                    </li>
                                
                                    @endif
                                </ul>
                            </div>
                            <div class="card-footer p-0 border-top border-translucent">
                                <ul class="nav d-flex flex-column my-3">
                                    @if(Auth::user()->isSeller())
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="fa fa-user"></i> <span>Profile</span>
                                    </a>
                                    <a class="dropdown-item" href="{{ url('billings') }}">
                                        <i class="fa fa-users"></i> <span>Manage your billings</span>
                                    </a>
                                    <a class="dropdown-item" href="{{ url('subscribe') }}">
                                        <i class="fa fa-users"></i> <span>Manage your subscriptions</span>
                                    </a>


                                  
                                    @endif
                                </ul>
                                <hr />
                                <div class="px-3">
                                    

                                      <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-phoenix-secondary d-flex flex-center w-100"><span class="me-2" data-feather="log-out"></span> Log Out</button>
              </form>


                                </div>
                                <div class="my-2 text-center fw-bold fs-10 text-body-quaternary">
                                     <a class="text-body-quaternary me-1" href="{{ url('intro') }}">Intro</a>&bull;
                                    <a class="text-body-quaternary me-1" href="{{ url('privacy-policy') }}">Privacy policy</a>&bull;
                                    <a class="text-body-quaternary mx-1" href="{{ url('terms-of-service') }}">Terms</a>&bull;
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </nav>