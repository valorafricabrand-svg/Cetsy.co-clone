{{-- resources/views/theme/{{ theme() }}/partials/_tab_description.blade.php --}}
@php
  $rawDescription = trim((string) ($product->localized_description ?? $product->description ?? ''));
  $hasHtml = $rawDescription !== strip_tags($rawDescription);

  $plainText = html_entity_decode(strip_tags($rawDescription), ENT_QUOTES | ENT_HTML5, 'UTF-8');
  $plainText = preg_replace('/\r\n?/', "\n", $plainText);
  $plainText = preg_replace('/[ \t]+/', ' ', (string) $plainText);
  $lines = array_values(array_filter(array_map('trim', preg_split('/\n+/', (string) $plainText)), fn ($v) => $v !== ''));

  $intro = null;
  $featureRows = [];
  $paragraphs = [];

  foreach ($lines as $index => $line) {
      if (preg_match('/^\[(.+?)\]\s*(.+)$/u', $line, $m)) {
          $featureRows[] = [
              'title' => trim($m[1]),
              'body' => trim($m[2]),
          ];
          continue;
      }

      if ($intro === null && $index === 0) {
          $intro = $line;
          continue;
      }

      $paragraphs[] = $line;
  }

  if ($intro === null && !empty($paragraphs)) {
      $intro = array_shift($paragraphs);
  }
@endphp

<div class="listing-tab-pane" id="desc-pane" role="tabpanel">
  @if($rawDescription === '')
    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
      No description has been provided for this listing yet.
    </div>
  @elseif($hasHtml)
    <div class="prose prose-slate max-w-none text-[15px] leading-7">
      {!! $rawDescription !!}
    </div>
  @else
    <div class="space-y-4">
      @if(!empty($intro))
        <p class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 text-[15px] leading-7 text-slate-800">
          {{ $intro }}
        </p>
      @endif

      @if(!empty($featureRows))
        <div class="grid gap-3 md:grid-cols-2">
          @foreach($featureRows as $row)
            <article class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
              <h4 class="text-sm font-semibold tracking-wide text-slate-900">{{ $row['title'] }}</h4>
              <p class="mt-1 text-sm leading-6 text-slate-700">{{ $row['body'] }}</p>
            </article>
          @endforeach
        </div>
      @endif

      @if(!empty($paragraphs))
        <div class="space-y-3 text-[15px] leading-7 text-slate-700">
          @foreach($paragraphs as $line)
            <p>{{ $line }}</p>
          @endforeach
        </div>
      @endif
    </div>
  @endif
</div>
