    <style>
        #toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1050;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .toast {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: fadeInUp 0.5s ease-out;
        }

        .toast-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .toast-body {
            font-size: 0.9rem;
        }

        .toast .badge {
            margin-right: 0.5rem;
            font-size: 0.75rem;
        }

        @keyframes fadeInUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }


    </style>
    <div id="toast-container">
        @if (\Session::has('success'))
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
            <div class="toast-header">
                <span class="avatar avatar-xs me-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon text-success" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 5a2 2 0 0 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /><path d="M21 6.727a11.05 11.05 0 0 0 -2.794 -3.727" /><path d="M3 6.727a11.05 11.05 0 0 1 2.792 -3.727" /></svg>
                </span>
                <strong class="me-auto">System</strong>
                <small>Just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <span class="badge bg-success">Success</span>
                {!! \Session::get('success') !!}
            </div>
        </div>
        @endif 

        @if (\Session::has('warning'))
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
            <div class="toast-header">
                <span class="avatar avatar-xs me-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon text-warning" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 5a2 2 0 0 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /><path d="M21 6.727a11.05 11.05 0 0 0 -2.794 -3.727" /><path d="M3 6.727a11.05 11.05 0 0 1 2.792 -3.727" /></svg>
                </span>
                <strong class="me-auto">System</strong>
                <small>Just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <span class="badge bg-warning">Warning</span>
                {!! \Session::get('warning') !!}
            </div>
        </div>
        @endif 

        @if (\Session::has('danger'))
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
            <div class="toast-header">
                <span class="avatar avatar-xs me-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 5a2 2 0 0 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /><path d="M21 6.727a11.05 11.05 0 0 0 -2.794 -3.727" /><path d="M3 6.727a11.05 11.05 0 0 1 2.792 -3.727" /></svg>
                </span>
                <strong class="me-auto">System</strong>
                <small>Just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <span class="badge bg-danger">Error</span>
                {!! \Session::get('danger') !!}
            </div>
        </div>
        @endif 
    </div>
