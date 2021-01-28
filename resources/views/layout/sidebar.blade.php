<div class="bg-blue-darkest shadow text-white md:w-64 flex flex-col z-20">
    <div class="flex md:hidden py-4 px-6">
        <div>{{ config('app.name') }}</div>
        <div class="ml-auto"><i class="fas fa-bars"></i></div>
    </div>
    <div class="hidden md:flex flex-col flex-1 md:overflow-hidden md:overflow-y-auto">
        @auth
            <div class="flex mt-4 px-4 flex-row items-center text-sm text-blue-lighter opacity-50 flex-no-shrink">
                <i class="fas fa-user-circle text-4xl mr-4 ml-1"></i>
                <div class="truncate">
                    @if(Auth::user()->name)
                        <div class="font-bold mb-1 truncate">{{ Auth::user()->name }}</div>
                    @endif
                    <div class="truncate">{{ Auth::user()->address() }}</div>
                </div>
            </div>
        @endauth

        <div class="text-sm py-2 flex-no-shrink">
            <div class="sidebar-heading">Main</div>
            <a href="{{ route('home') }}"
               class="sidebar-link{{ strpos(Route::currentRouteName(), 'home') === 0 ? ' active' : '' }}"><i
                        class="fas fa-home mr-4"></i>Home</a>
            <a href="{{ route('domains.index') }}"
               class="sidebar-link{{ strpos(Route::currentRouteName(), 'domains') === 0 ? ' active' : '' }}"><i
                        class="fas fa-fw fa-globe mr-4"></i>Domains</a>
            <a href="{{ route('mailboxes.index') }}"
               class="sidebar-link{{ strpos(Route::currentRouteName(), 'mailboxes') === 0 ? ' active' : '' }}"><i
                        class="fas fa-fw fa-inbox mr-4"></i>Mailboxes</a>
            <a href="{{ route('aliases.index') }}"
               class="sidebar-link{{ strpos(Route::currentRouteName(), 'aliases') === 0 ? ' active' : '' }}"><i
                        class="fas fa-fw fa-paper-plane mr-4"></i>Aliases</a>
            @if (isUserSuperAdmin())
                <a href="{{ route('tls-policies.index') }}"
                   class="sidebar-link{{ strpos(Route::currentRouteName(), 'tls-policies') === 0 ? ' active' : '' }}"><i
                            class="fas fa-fw fa-shield-alt  mr-4"></i>TLS Policies</a>
            @endif
        </div>

        <div class="text-sm py-2 flex-no-shrink">
            <div class="sidebar-heading">User</div>
            <a href="{{ route('password.change') }}"
               class="sidebar-link{{ strpos(Route::currentRouteName(), 'password.change') === 0 ? ' active' : '' }}"><i
                        class="fas fa-fw fa-key mr-4"></i>Change Password</a>
            <a href="{{ route('logout') }}" class="sidebar-link"><i
                        class="fas fa-fw fa-sign-out-alt mr-4"></i>Logout</a>
        </div>
    </div>
</div>