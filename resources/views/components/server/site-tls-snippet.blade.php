(tls-{!! $site->id !!}) {
    @if($tlsSetting === \App\Models\TlsSetting::Custom && $certificate)
        tls {!! $certificate->certificatePath() !!} {!! $certificate->privateKeyPath() !!}
    @elseif($tlsSetting === \App\Models\TlsSetting::Internal)
        tls internal
    @else
        #
    @endif
}
