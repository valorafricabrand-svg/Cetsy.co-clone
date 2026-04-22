@extends('theme.'.theme().'.layouts.app')

@php
    use Illuminate\Support\Str;

    $meta = $post->meta ?? [];
    $description = $meta['description'] ?? strip_tags($post->excerpt ?: Str::limit($post->body, 180));
    $heroImage = $post->featured_image_url ?? asset('assets/img/blog/blog-3.png');
    $postUrl = route('blog.show', $post->slug);
    $publishedAt = $post->published_at ?? $post->created_at;

    $articleStructuredData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'BlogPosting',
                '@id' => $postUrl . '#article',
                'headline' => $post->title,
                'description' => Str::limit(strip_tags($description), 200),
                'image' => [$heroImage],
                'mainEntityOfPage' => [
                    '@type' => 'WebPage',
                    '@id' => $postUrl,
                ],
                'author' => [
                    '@type' => $post->author ? 'Person' : 'Organization',
                    'name' => optional($post->author)->name ?: config('app.name', 'Cetsy'),
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => config('app.name', 'Cetsy'),
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => asset('assets/images/cetsylogmain.png'),
                    ],
                ],
                'datePublished' => optional($publishedAt)->toAtomString(),
                'dateModified' => optional($post->updated_at ?? $publishedAt)->toAtomString(),
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Home',
                        'item' => url('/'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => 'Blog',
                        'item' => route('blog.index'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => $post->title,
                        'item' => $postUrl,
                    ],
                ],
            ],
        ],
    ];
@endphp

@section('title', $meta['title'] ?? $post->title.' | Cetsy.co Blog')
@section('meta_description', $description)
@section('meta_image', $heroImage)
@section('canonical_url', route('blog.show', $post->slug))
@section('meta_robots', 'index, follow')

@push('structured-data')
<script type="application/ld+json">
{!! json_encode($articleStructuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@push('styles')
<style>
  .blog-article {
    min-width: 0;
    color: #334155;
    line-height: 1.85;
    overflow-wrap: anywhere;
    word-break: break-word;
  }

  .blog-article,
  .blog-article * {
    box-sizing: border-box;
  }

  .blog-article > * {
    max-width: 100%;
  }

  .blog-article > :first-child {
    margin-top: 0;
  }

  .blog-article > :last-child {
    margin-bottom: 0;
  }

  .blog-article h1,
  .blog-article h2,
  .blog-article h3,
  .blog-article h4,
  .blog-article h5,
  .blog-article h6 {
    color: #0f172a;
    font-weight: 800;
    line-height: 1.25;
    margin: 1.35rem 0 0.75rem;
    overflow-wrap: anywhere;
    word-break: break-word;
    white-space: normal !important;
  }

  .blog-article h1 { font-size: clamp(1.7rem, 5.5vw, 2.5rem); }
  .blog-article h2 { font-size: clamp(1.45rem, 4.8vw, 2rem); }
  .blog-article h3 { font-size: clamp(1.2rem, 4vw, 1.6rem); }

  .blog-article p,
  .blog-article ul,
  .blog-article ol,
  .blog-article blockquote,
  .blog-article pre,
  .blog-article table,
  .blog-article figure {
    margin: 0 0 1rem;
  }

  .blog-article p,
  .blog-article li,
  .blog-article a,
  .blog-article span,
  .blog-article strong,
  .blog-article em {
    overflow-wrap: anywhere;
    word-break: break-word;
    white-space: normal;
  }

  .blog-article ul,
  .blog-article ol {
    padding-left: 1.25rem;
  }

  .blog-article blockquote {
    margin-left: 0;
    padding-left: 1rem;
    border-left: 4px solid #cbd5e1;
    color: #475569;
  }

  .blog-article img,
  .blog-article video,
  .blog-article iframe,
  .blog-article embed,
  .blog-article object {
    display: block;
    height: auto !important;
    max-width: 100% !important;
  }

  .blog-article img,
  .blog-article video {
    border-radius: 0.9rem;
  }

  .blog-article iframe {
    width: 100% !important;
    min-height: min(56vw, 360px);
    border: 0;
    border-radius: 0.9rem;
  }

  .blog-article figure {
    width: 100% !important;
    margin-left: 0;
    margin-right: 0;
  }

  .blog-article table {
    display: block;
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-collapse: collapse;
    border-spacing: 0;
  }

  .blog-article th,
  .blog-article td {
    border: 1px solid #e2e8f0;
    padding: 0.65rem 0.85rem;
    vertical-align: top;
    white-space: normal;
  }

  .blog-article pre,
  .blog-article code {
    max-width: 100%;
    overflow-wrap: anywhere;
    word-break: break-word;
  }

  .blog-article pre {
    overflow-x: auto;
    white-space: pre-wrap;
    border-radius: 1rem;
    background: #0f172a;
    color: #e2e8f0;
    padding: 1rem;
  }

  @media (max-width: 640px) {
    .blog-article {
      font-size: 1rem;
      line-height: 1.75;
    }

    .blog-article table {
      font-size: 0.875rem;
    }
  }
</style>
@endpush

@section('main')
<div class="relative overflow-x-clip pb-10">
  <div class="pointer-events-none absolute -right-24 -top-20 h-80 w-80 rounded-full bg-emerald-200/40 blur-3xl"></div>
  <div class="pointer-events-none absolute -left-24 top-[24rem] h-72 w-72 rounded-full bg-cyan-200/30 blur-3xl"></div>

  <section class="relative bg-gradient-to-b from-emerald-900 to-emerald-700 py-12 text-white">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
      <nav aria-label="Breadcrumb" class="mb-4 text-sm">
        <ol class="flex flex-wrap items-center gap-2 text-emerald-100/80">
          <li><a href="{{ url('/') }}" class="hover:text-white">Home</a></li>
          <li>/</li>
          <li><a href="{{ route('blog.index') }}" class="hover:text-white">Blog</a></li>
          <li>/</li>
          <li class="truncate text-white">{{ $post->title }}</li>
        </ol>
      </nav>

      <div class="grid items-center gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <div>
          <span class="inline-flex rounded-full border border-white/30 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.1em] text-emerald-50">
            {{ optional($post->category)->name ?? 'Cetsy.co Updates' }}
          </span>
          <h1 class="mt-3 break-words text-4xl font-extrabold leading-tight md:text-5xl">{{ $post->title }}</h1>
          <p class="mt-3 max-w-3xl break-words text-sm text-emerald-50/95 md:text-base">
            {{ $post->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($post->body), 160) }}
          </p>

          <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-emerald-100/85 sm:text-sm">
            @if($post->author)
              <div><i class="fas fa-user mr-1"></i> {{ $post->author->name }}</div>
            @endif
            <div><i class="fas fa-calendar mr-1"></i> {{ optional($post->published_at)->format('M j, Y') ?? $post->created_at->format('M j, Y') }}</div>
          </div>
        </div>

        <div class="justify-self-start lg:justify-self-end">
          <img src="{{ $heroImage }}" alt="{{ $post->title }}" class="h-56 w-full max-w-md rounded-2xl border border-white/20 object-cover shadow-xl md:h-64">
        </div>
      </div>
    </div>
  </section>

  <section class="bg-slate-50 py-6">
    <div class="mx-auto grid w-full max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[1fr_320px] lg:px-8">
      <div class="min-w-0">
        <article class="blog-article content-body min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          {!! $post->body !!}
        </article>

        @php
          $shareUrl = urlencode(route('blog.show', $post->slug));
          $shareText = urlencode($post->title.' on Cetsy.co');
        @endphp

        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
          <h2 class="text-xs font-semibold uppercase tracking-[0.12em] text-emerald-700">Share</h2>
          <div class="mt-3 flex flex-wrap gap-2">
            <a class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}"><i class="fab fa-x-twitter mr-2"></i>Tweet</a>
            <a class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}"><i class="fab fa-facebook-f mr-2"></i>Share</a>
            <a class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-emerald-300 hover:text-emerald-700" target="_blank" rel="noopener" href="https://www.linkedin.com/shareArticle?mini=true&url={{ $shareUrl }}&title={{ $shareText }}"><i class="fab fa-linkedin-in mr-2"></i>Post</a>
          </div>
        </div>
      </div>

      <aside class="space-y-5">
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
          <h3 class="text-xs font-semibold uppercase tracking-[0.12em] text-emerald-700">Stay in the loop</h3>
          <p class="mt-2 text-sm text-emerald-800">Subscribe to seller tips and platform updates straight to your inbox.</p>
        </div>

        @if($relatedPosts->isNotEmpty())
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <h3 class="text-xs font-semibold uppercase tracking-[0.12em] text-emerald-700">More for you</h3>
            <div class="mt-3 space-y-3">
              @foreach($relatedPosts as $related)
                @php
                  $relatedImage = $related->featured_image_url ?? asset('assets/img/blog/blog-4.png');
                @endphp
                <a href="{{ route('blog.show', $related->slug) }}" class="group flex items-center gap-3 rounded-xl border border-slate-200 p-2 transition hover:border-emerald-300">
                  <img src="{{ $relatedImage }}" alt="{{ $related->title }}" class="h-16 w-16 rounded-lg object-cover">
                  <div class="min-w-0">
                    <p class="line-clamp-2 text-sm font-semibold text-slate-800 group-hover:text-emerald-700">{{ $related->title }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ optional($related->published_at)->format('M j, Y') ?? $related->created_at->format('M j, Y') }}</p>
                  </div>
                </a>
              @endforeach
            </div>
          </div>
        @endif
      </aside>
    </div>
  </section>
</div>
@endsection
