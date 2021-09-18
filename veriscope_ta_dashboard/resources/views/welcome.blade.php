@extends('layouts.app')

@section('body-styles') layout--welcome @endsection('body-styles')

@section('content')
  <div id="globe"></div>
  <div class="w-full bg-home-gradient relative pt-32 md:pt-0">
    <div class="container p-2 md:p-12 md:pl-4 xxl:pl-0 xxl:pr-16">
      <div class="md:flex md:h-full items-center p-4 py-16 md:py-48">
        <div class="md:w-2/3 text-white">
          <h1 class="text-md lg:text-xxl">VERISCOPE</h1>
          <h2>Verified Shyft Compliant Optimized Participant Exchanges</h2>
          <h4>The complete solution to the FATF Travel Rule and global regulatory Compliance.</h4>
          <h4>Veriscope is a blockchain-based compliance framework and smart contract platform for Virtual Asset Service Providers (VASPs).</h4>
        </div>
      </div>
    </div>
  </div>

  <div class="bg-white">
    <div class="container items-center p-4 xxl:px-0 pb-16 pt-16 md:py-16 lg:py-32">
      <h1 class="h2 mb-12 md:mb-24 text-center xl:w-2/3 m-auto">VERISCOPE, One Solution.</h1>
      <div class="md:flex  items-center mb-12">
        <div class="md:w-1/2 text-center md:text-left">
          <img src="/images/home/credibility.svg" width="406" height="286" class="mb-4 md:mb-12 lg:mb-20" alt="Shyft Secure">
        </div>
        <div class="md:w-1/2">
          <h2 class="font-headline text-base font-bold py-1 m-0">Shyft Coalition</h2>
          <p>The Verified Shyft Compliant Optimized Participant Exchange (Veriscope) system was designed as a smart contract mediated data coordination infrastructure, intended to provide a global discovery and validation ecosystem to solve regulatory guidance mandated through the Financial Action Task Force.</p>
        </div>
      </div>
      <h1 class="h2 mb-12 md:mb-24 text-center xl:w-2/3 m-auto">The Veriscope Discovery and Coordination Layer</h1>
      <div class="md:flex md:flex-row-reverse items-center mb-12">
        <div class="md">
          <h2 class="font-headline text-base font-bold py-1 m-0">How it works:</h2>
          <p>The goal of Veriscope is to enable VASP discovery by making cryptographic associations between three different objects:</p>
          <ul>
            <li>The outgoing cryptocurrency address.</li>
            <li>The sending VASP's public Shyft Network address.</li>
            <li>The Shyft Network public address of the VASP's user.</li>
          </ul>
          <p>A VASP writes an attestation claim to the network, which acts as a Request for Response to other VASPs on the network that can respond to this claim.</p>
          <ul>
            <li>If a VASP on the Veriscope Network sees that an attestation of a cryptocurrency address, associated with one of their users has been made, they can use Shyft Network's smart contract system to respond to that attestation.</li>
            <li>This response, and set of simultaneous handshakes, kick off an off-chain communication channel which then allows them to share sensitive travel rule information across both counterparties.</li>
          </ul>
        </div>
      </div>
      <h1 class="h2 mb-12 md:mb-24 text-center xl:w-2/3 m-auto">What is the FATF Travel Rule?</h1>
      <div class="md:flex md:flex-row-reverse items-center mb-12">
        <div class="md:w-1/2 text-center md:text-right">
          <img src="/images/home/shyft-reporting.svg" width="406" height="286" class="mb-4 md:mb-12 lg:mb-20" alt="Shyft Reporting">
        </div>
        <div class="md:w-1/2">
          <h2 class="font-headline text-base font-bold py-1 m-0">FATF Travel Rule</h2>
          <p>The Financial Action Task Force(FATF) issued a <a href="https://www.fatf-gafi.org/media/fatf/documents/recommendations/RBA-VA-VASPs.pdf">guidance</a> requiring Virtual Asset Service Providers (VASPs) to share Personal Identifiable Information (PII) and Know-Your-Customer (KYC) data between transacting senders and receivers before executing transactions.</p>
          <p>This guidance, called the Travel Rule, is enforced in the traditional finance space between counterparties such as banks who use SWIFT for both transaction settlement and identity data sharing.</p>
        </div>
      </div>
      <h1 class="h2 mb-12 md:mb-24 text-center xl:w-2/3 m-auto">Key Stakeholders</h1>
      <div class="md:flex items-center mb-12">
        <div class="md:w-1/2 text-center md:text-left">
          <img src="/images/home/shyft-minimize-risk.svg" width="406" height="286" class="mb-4 md:mb-12 lg:mb-20" alt="Shyft Minimize Risk">
        </div>
        <div class="md:w-1/2">
            <h2 class="font-headline text-base font-bold py-1 m-0">Financial Action Task Force</h2>
            <p>Intergovernmental organization, <a href="https://www.fatf-gafi.org/">(FATF)</a> that focuses on the development of policies to combat <a href="https://en.wikipedia.org/wiki/Money_laundering">money laundering</a> and <a href="https://en.wikipedia.org/wiki/Terrorism_financing">terrorism financing</a>.  It monitors progress in implementing the FATF Recommendations through "peer reviews" ("mutual evaluations") of member countries; it also maintains two lists of nations depending on their level of compliance or adherence to AML regulation and controls: the FATF Blacklist and FATF Greylist.</p>
        </div>
      </div>
      <div class="md:flex md:flex-row-reverse items-center mb-12">
        <div class="md:w-1/2 text-center md:text-right">
          <img src="/images/home/vasps.svg" width="406" height="286" class="mb-4 md:mb-12 lg:mb-20" alt="Shyft Reporting">
        </div>
        <div class="md:w-1/2">
          <h2 class="font-headline text-base font-bold py-1 m-0">Virtual Asset Service Providers</h2>
          <p>VASPs: any entity engaged in digital asset custody, such as:</p>
          <ul>
            <li>Cryptocurrency exchanges</li>
            <li>Non-custodial wallets</li>
            <li>OTC desks</li>
            <li>Brokerage firms</li>
          </ul>
        </div>
      </div>
      <h1 class="h2 mb-12 md:mb-24 text-center xl:w-2/3 m-auto">Trust, Coordination, and Discoverability</h1>
      <div class="md:flex items-center mb-12">
        <div class="md:w-1/2 text-center md:text-left">
          <img src="/images/home/ecosystem-1.svg" width="406" height="286" class="mb-4 md:mb-12 lg:mb-20" alt="Shyft Minimize Risk">
        </div>
        <div class="md:w-1/2">
            <h2 class="font-headline text-base font-bold py-1 m-0">Trust</h2>
            <p>Veriscope enables the evolution of online trust by applying consent frameworks and codified rules of engagement to digital ecosystems.</p>
            <p>Shyft Network infrastructure does not hold or facilitate the sending/receiving of any private or regulated data.</p>
        </div>
      </div>
      <div class="md:flex md:flex-row-reverse items-center mb-12">
        <div class="md:w-1/2 text-center md:text-right">
          <img src="/images/home/ecosystem-2.svg" width="406" height="286" class="mb-4 md:mb-12 lg:mb-20" alt="Shyft Reporting">
        </div>
        <div class="md:w-1/2">
          <h2 class="font-headline text-base font-bold py-1 m-0">Coordination</h2>
          <p>VASPs can form coalitions, or trust channels, between known peers and industry participants, and they can pre-validate compliance and custody procedures.</p>
        </div>
      </div>
      <div class="md:flex items-center mb-12">
        <div class="md:w-1/2 text-center md:text-left">
          <img src="/images/home/ecosystem-3.svg" width="406" height="286" class="mb-4 md:mb-12 lg:mb-20" alt="Shyft Minimize Risk">
        </div>
        <div class="md:w-1/2">
            <h2 class="font-headline text-base font-bold py-1 m-0">Discoverability</h2>
            <p>Participants can whitelist exchange addresses and privacy-preserving individual PII data attestations on a shared registry internal to the coalition.</p>
        </div>
      </div>
      <div class="md:flex md:flex-row-reverse items-center mb-12">
        <div class="md:w-1/2 text-center md:text-right">
          <img src="/images/home/ecosystem-4.svg" width="406" height="286" class="mb-4 md:mb-12 lg:mb-20" alt="Shyft Reporting">
        </div>
        <div class="md:w-1/2">
          <h2 class="font-headline text-base font-bold py-1 m-0">Key Principles</h2>
          <p><b>Data protection:</b> Individual KYC data must be protected, and the individual needs to be able to consent to any KYC data sharing.</p>
          <p><b>Collaborative self-regulatory system for competitors:</b> VASPs shouldn’t have to trust each other to do business, but must be able to communicate, set business rules, and exchange information with each other.</p>
          <p><b>Auditability:</b> Data sharing processes and trails and data custody standards must be auditable by all applicable VASPs, end users, and regulatory bodies.</p>
          <p><b>Flexibility:</b> Any technology stack used for adherence to current regulations must be upgradable to enforce future regulations.</p>
          <p><b>Business use:</b> The framework must present acceptable risk and cost models for VASPs.</p>
          <p><b>Interoperability:</b> The framework must be usable for today’s intermediaries (VASP types, protocols, data transfer rails, transaction analysis companies, identity verifiers, etc.)</p>
        </div>
      </div>
      <h1 class="h2 mb-12 md:mb-24 text-center xl:w-2/3 m-auto">VASPScan: the VASP Transaction explorer</h1>
      <div class="md:flex md:flex-row-reverse items-center mb-12">
        <div class="md:w-1/2 text-center md:text-right">
          <img src="/images/home/shyft-blockexplorer.svg" width="406" height="286" class="mb-4 md:mb-12 lg:mb-20" alt="Shyft Block Explorer">
        </div>
        <div class="md:w-1/2">
          <p>VASPscan is the world’s first VASP explorer, providing a detailed view of real-time transactions from VASP claimed addresses, and enabling a permanent link between VASPs and their cryptocurrency transactions, while still maintaining crypto asset fungibility and anonymizing Personally Identifiable Information (PII).</p>
          <p>A Block Explorer is a tool that provides detailed information about blocks, addresses, and transactions. We have created our own explorer that gives VASP’s the ability to publicly view and lookup VASP-enabled addresses, transactions and various details that take place on the network, while protecting anonymity.</p>
          <p>VASPscan identifies attestations in transactions, allowing VASPs to discover counterparties and complete associated cryptocurrency transactions that require FATF compliance.</p>
        </div>
      </div>
    </div>
  </div>

  @include('partials.footer')

@endsection('content')
