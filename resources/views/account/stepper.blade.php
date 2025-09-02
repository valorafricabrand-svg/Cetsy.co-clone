{{-- ===== ORDER JOURNEY & ACTIONS — Font Awesome icons ===== --}}

@php
    /** 1) Status + label & FA icon */
    $journeySteps = [
        \App\Models\Order::STATUS_PENDING    => ['label' => 'Pending Payment', 'icon' => 'fas fa-wallet'],
        \App\Models\Order::STATUS_PROCESSING => ['label' => 'Processing',      'icon' => 'fas fa-cogs'],
        \App\Models\Order::STATUS_SHIPPED    => ['label' => 'Shipped',         'icon' => 'fas fa-truck'],
        \App\Models\Order::STATUS_COMPLETED  => ['label' => 'Completed',       'icon' => 'fas fa-check-circle'],
        \App\Models\Order::STATUS_CANCELLED  => ['label' => 'Cancelled',       'icon' => 'fas fa-ban'],
    ];

    $currentIndex = array_search($order->status, array_keys($journeySteps));
@endphp

<style>
:root{
    --step-complete:#20c997;--step-active:#0d6efd;--step-upcoming:#adb5bd
}
.stepper{display:flex;justify-content:space-between;flex-wrap:wrap;gap:1rem}
.stepper .step{flex:1 1 110px;text-align:center;position:relative;min-width:110px}
.stepper .circle{width:48px;height:48px;line-height:48px;border-radius:50%;margin:0 auto;
                display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem}
.stepper .label{font-size:.875rem;margin-top:.5rem;min-height:2rem}
.stepper .line{position:absolute;top:24px;left:50%;height:4px;width:100%;
               transform:translateX(-50%);z-index:-1}
.stepper .step:first-child .line{display:none}
.stepper .complete .circle{background:var(--step-complete)}
.stepper .active   .circle{background:var(--step-active)}
.stepper .upcoming .circle{background:var(--step-upcoming)}
.stepper .complete .line,
.stepper .active   .line {background:var(--step-complete)}
.stepper .upcoming .line {background:var(--step-upcoming)}
</style>

<div class="card shadow-sm mb-5 border-0">
  <div class="card-body">

    {{-- Heading --}}
    <h5 class="fw-semibold text-muted d-flex align-items-center gap-2 mb-4">
      <i class="fas fa-route fs-4 text-info"></i>
      Order&nbsp;Progress
    </h5>

    {{-- 2) Visual stepper --}}
    <div class="stepper mb-4">
      @foreach($journeySteps as $code => $meta)
        @php
            $idx   = $loop->index;
            $state = $idx <  $currentIndex ? 'complete'
                   : ($idx === $currentIndex ? 'active' : 'upcoming');
        @endphp
        <div class="step {{ $state }}">
          <div class="circle">
            <i class="{{ $meta['icon'] }}"></i>
          </div>
          <div class="line"></div>
          <div class="label {{ $state === 'active' ? 'fw-semibold text-dark' : 'text-muted' }}">
            {{ $meta['label'] }}
            @if($state === 'active')
              <span class="badge bg-primary ms-1">Now</span>
            @endif
          </div>
        </div>
      @endforeach
    </div>

    {{-- 3) Info text --}}
    <p class="small text-muted mb-4">
      Email notifications are sent at every step. Refresh this page anytime for real-time status.
    </p>

    {{-- 4) Action buttons --}}
    @if($order->status === \App\Models\Order::STATUS_PENDING)
    <div class="d-flex flex-wrap gap-3">
        <a href="{{ route('pay_now', $order->id) }}"
                class="btn btn-success btn-lg d-flex align-items-center gap-2 px-4 py-2">
            <i class="fas fa-credit-card fs-5"></i>
            <span>Pay&nbsp;Now</span>
        </a>
    </div>
    @endif

  </div>
</div>