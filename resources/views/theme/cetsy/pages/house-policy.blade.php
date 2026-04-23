@extends('theme.'.theme().'.layouts.app')

@section('title', 'Cetsy House Rules and Community Policy')
@section('meta_description', 'Read Cetsy community rules covering forums, messages, conduct, policy enforcement, and marketplace safety standards.')
@section('canonical_url', localized_route('house-policy'))
@section('meta_image', setting('logo_url') ?: asset('assets/images/cetsylogmain.png'))
@section('meta_robots', 'index, follow')

@section('main')
  <section class="py-10">
    <div class="mx-auto w-full max-w-5xl px-4 sm:px-6">
      <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Behavioral Policy For The Cetsy Community</h1>

        <p class="mt-6 leading-7 text-slate-700">
          Community equality and global trade for all is our goal at Cetsy. We encourage our sellers to use community spaces to connect with others, share inspiration and knowledge, team-build, and further their existing careers or expand into new opportunities.
        </p>
        <p class="mt-4 leading-7 text-slate-700">
          The Cetsy Community includes future Forums and the Cetsy Chat. This policy is part of the Cetsy House Rules. By using our community spaces, you are agreeing to this policy and our House Rules.
        </p>

        <h2 class="mt-8 text-xl font-bold text-slate-900">The Cetsy Community</h2>
        <p class="mt-3 leading-7 text-slate-700">
          Community spaces within Cetsy are public areas where sellers can interact with other sellers. Buyers and sellers must be 18 years of age and above. To ensure our community remains a great and safe place, you agree to comply with all listed policies in all Cetsy community spaces:
        </p>

        <div class="mt-4 space-y-3 text-slate-700">
          <p class="italic"><strong>Respect everyone's privacy, including your own.</strong> Do not share private or personal information in public spaces-this includes, but is not limited to, transaction details, exact copies of private messages, addresses, financial information, bank details, etc.</p>
          <p class="italic">You should not use community spaces to discuss interactions with Cetsy representatives, nor share copies or extracts of conversations <b>(emails, messages/DMs, live chats, etc.)</b>. Share such correspondence only if a Cetsy representative asks you to do so in order to settle a disagreement.</p>
          <p class="font-semibold italic text-rose-700">No fake IDs or documents are to be used by any Buyer or Seller on Cetsy.</p>
          <p class="italic">Be respectful to all individuals. When in doubt, assume the best intentions and act with patience and understanding. Never use Cetsy community spaces to publicly disgrace, humiliate, or slander a specific seller, buyer, Cetsy rep, listing, shop, or category of any item.</p>
          <p class="italic font-semibold">Do not interfere with or cause turmoil for others. Always act with a kind heart.</p>
          <p class="italic"><span class="font-bold uppercase text-rose-700">Don't spam.</span> This includes unsolicited links to your shop, fundraisers, duplicate posts, surveys, social media, or other promotional content.</p>
        </div>

        <p class="mt-4 leading-7 text-slate-700">
          However, there is <strong>one</strong> type of outreach we encourage. Sellers-when a potential buyer favorites your item(s) they are saying, <em>"Hello, I like your item."</em> That's your cue to respond politely. Send a brief message such as, <em>"Welcome to my shop-let me know if you have questions; some prices are flexible."</em> If a buyer favorites multiple items, please refrain from sending the same message repeatedly.
        </p>
        <p class="mt-4 leading-7 text-slate-700">
          Sellers should not use community spaces to coordinate pricing or discuss fee avoidance. Nor should anyone harass other sellers or shame buyers. Content that glorifies hatred, racism, sexism, unethical behavior, or misinformation-along with any threats of violence-has no place here.
        </p>
        <p class="mt-4 leading-7 text-slate-700">
          Never infringe upon someone's intellectual-property rights or encourage others to do so. Follow the law and develop your own ideas. Do not encourage others to break the law or violate our policies.
        </p>

        <h2 class="mt-8 text-xl font-bold uppercase text-slate-900">Forums</h2>
        <p class="mt-3 leading-7 text-slate-700">Forums are public spaces where sellers share knowledge that benefits everyone. Lead with empathy-tone can be hard to read online. When in doubt, say nothing.</p>
        <p class="mt-3 leading-7 text-slate-700">Respect opinions and flag content only when it truly violates House Rules. Cetsy reserves the right to remove content at any time for any reason, including dormant content or anything that disrupts forum operations.</p>

        <h2 class="mt-8 text-xl font-bold uppercase text-slate-900">Direct Messages</h2>
        <p class="mt-3 leading-7 text-slate-700">Members should communicate via Cetsy internal messaging system. Refrain from abusive, disrespectful, obscene, or vulgar language. <b>DO NOT BE RACIST/SEXIST.</b></p>
        <p class="mt-3 leading-7 text-slate-700">No unsolicited advertising or promotions.</p>
        <p class="mt-3 leading-7 text-slate-700">If you receive an inappropriate message, report it to Cetsy via email, phone, or online chat.</p>

        <h2 class="mt-8 text-xl font-bold uppercase text-slate-900">Cetsy Violation Action Policy</h2>
        <p class="mt-3 italic font-semibold leading-7 text-slate-700">If an item or communication appears not to conform to Cetsy policies, please report it. Cetsy will review within 48-72 hours and determine compliance.</p>
        <p class="mt-3 italic font-semibold leading-7 text-slate-700">If compliant, no action is taken. If it violates policies, Cetsy will notify the owner, who has 48 hours to remove it. Failure to comply may result in removal and/or account suspension.</p>

        <h2 class="mt-8 text-xl font-bold uppercase text-slate-900">Service Level Response Times</h2>
        <p class="mt-3 leading-7 text-slate-700">In the event of a service outage or assistance request, the Transaction Processor will acknowledge receipt immediately and strive to resolve the issue as quickly as possible in consultation with stakeholders.</p>

        <h2 id="behavioral-policy" class="mt-8 text-xl font-bold uppercase text-slate-900">Behavioral Policy / Indemnity General</h2>
        <p class="mt-3 leading-7 text-slate-700">All parties agree not to violate U.S. laws (including Ohio law) and to defend, indemnify, and hold Cetsy.co harmless against all claims arising from legal violations. Cetsy.co likewise indemnifies members.</p>

        <h2 class="mt-8 text-xl font-bold text-slate-900">General</h2>
        <h3 class="mt-4 text-base font-bold text-slate-900">Language</h3>
        <p class="mt-2 leading-7 text-slate-700">This Agreement and all notices are in English; where texts exist in multiple languages, the English version governs.</p>

        <h3 class="mt-4 text-base font-bold text-slate-900">Disclaimers</h3>
        <p class="mt-2 leading-7 text-slate-700">Cetsy may change ad-program terms at any time, including fees, platforms, or termination. Participation does not oblige Cetsy to display ads or guarantee clicks or sales. Advertising services may evolve; Cetsy reserves the right to modify, transfer, or end campaigns with notice.</p>
        <p class="mt-2 leading-7 text-slate-700">No ad may infringe third-party rights. Sellers must follow applicable laws. Cetsy may reject or remove any ad at its sole discretion.</p>

        <h2 class="mt-8 text-xl font-bold text-slate-900">Privacy Policy / Listing Fees / Payment Processing Fees</h2>
        <p class="mt-3 leading-7 text-slate-700">Be aware of charges when transferring funds from your Seller account; fees are determined by the payment processor you choose.</p>

        <h2 class="mt-8 text-xl font-bold uppercase text-slate-900">Cetsy IP Infringement Policy</h2>
        <p class="mt-3 leading-7 text-slate-700">This process ensures IP concerns are addressed promptly and fairly.</p>

        <a href="/house-policy#behavioral-policy" class="mt-6 inline-flex items-center rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-400">
          Read More
        </a>
      </article>
    </div>
  </section>
@endsection
