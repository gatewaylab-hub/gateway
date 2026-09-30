<?php
    $scheme = config('getfy.panel_color_scheme', \App\Support\PanelColorScheme::defaults());
    $path = request()->path();
    $guestAuthPaths = ['login', 'cadastro', 'criar-admin', 'esqueci-senha', 'login/2fa', 'criar-conta'];
    $ignoreStoredTheme = ! auth()->check() && (
        in_array($path, $guestAuthPaths, true)
        || str_starts_with($path, 'redefinir-senha/')
        || str_starts_with($path, 'plataforma/login')
    );
    // Marketplace Gamkon: sempre claro (sem tema escuro).
    $forceLight = true;
?>
<script>
(function(){try{
    var forceLight=<?php echo json_encode($forceLight, 15, 512) ?>;
    if(forceLight){
        document.documentElement.classList.remove('dark');
        return;
    }
    var policy=<?php echo json_encode($scheme, 15, 512) ?>;
    var ignoreStored=<?php echo json_encode($ignoreStoredTheme, 15, 512) ?>;
    var isDark=false;
    var stored=null;
    if(!policy.locked&&!ignoreStored){stored=localStorage.getItem('theme');}
    if(policy.locked){
        if(policy.mode==='system'){isDark=window.matchMedia('(prefers-color-scheme: dark)').matches;}
        else{isDark=policy.mode==='dark';}
    }else if(stored==='light'||stored==='dark'){isDark=stored==='dark';}
    else if(policy.mode==='system'){isDark=window.matchMedia('(prefers-color-scheme: dark)').matches;}
    else{isDark=policy.mode==='dark';}
    document.documentElement.classList.toggle('dark',isDark);
}catch(_){}})();
</script>
<?php /**PATH C:\laragon\www\ggmax-romildo\resources\views/partials/panel-theme-init.blade.php ENDPATH**/ ?>