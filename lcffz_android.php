<?php
/* ==========================================================
   LCFFZ ANTI-CHEAT – ANDROID (ADB)
   Scanner Forense para Free Fire / Free Fire MAX
   Baseado em integridade, comportamento e análise temporal
   ========================================================== */

date_default_timezone_set('America/Sao_Paulo');

/* ===================== CORES ===================== */
$R="\e[91m"; $G="\e[92m"; $Y="\e[93m"; $B="\e[94m";
$C="\e[96m"; $W="\e[97m"; $N="\e[0m"; $BOLD="\e[1m";

/* ===================== ESTADO ===================== */
$pontuacao = 0;
$evidencias = [];
$suspeitas  = [];
$infos      = [];

/* ===================== HELPERS ===================== */
function banner(){
  global $C,$W,$N;
  system("clear");
  echo "{$C}=================================================={$N}\n";
  echo "{$W} LCFFZ ANTI-CHEAT – ANDROID (ADB){$N}\n";
  echo "{$C}=================================================={$N}\n\n";
}
function info($m){ global $C,$infos; echo "{$C}[i] {$m}\n"; $infos[]=$m; }
function suspeito($m,$s){ global $Y,$pontuacao,$suspeitas; echo "{$Y}[?] {$m} (+{$s})\n"; $pontuacao+=$s; $suspeitas[]="{$m} (+{$s})"; }
function evidencia($m,$s){ global $R,$pontuacao,$evidencias; echo "{$R}[!] {$m} (+{$s})\n"; $pontuacao+=$s; $evidencias[]="{$m} (+{$s})"; }

/* ===================== CHECKS BASE ===================== */
function checkADB(){
  exec("adb devices 2>&1", $o);
  $t = implode("\n",$o);
  if (strpos($t,"device") === false){
    echo "\n[ERRO] Nenhum dispositivo ADB conectado.\n";
    exit;
  }
  info("ADB conectado");
}

function escolherJogo(){
  echo "[1] Free Fire\n[2] Free Fire MAX\nEscolha: ";
  $op = trim(fgets(STDIN));
  return ($op==="2") ? "com.dts.freefiremax" : "com.dts.freefireth";
}

function checkInstalacao($pkg){
  $r = shell_exec("adb shell pm list packages --user 0 | grep {$pkg}");
  if (!$r) suspeito("Pacote do jogo não listado (pode estar oculto/desinstalado)",10);
  else info("Pacote do jogo encontrado ({$pkg})");
}

/* ===================== 1. INTEGRIDADE DO SHELL ===================== */
function checkShellIntegridade(){
  info("Verificando integridade do shell (aliases/funções)");
  // Não acusa por Termux redefinir comandos; apenas registra se houver evidência explícita
  $files = [
    "~/.bashrc","~/.profile","~/.zshrc",
    "/data/data/com.termux/files/usr/etc/bash.bashrc"
  ];
  foreach($files as $f){
    $cmd = 'adb shell "if [ -f '.$f.' ]; then grep -E \"function (pkg|git|adb|stat|cd)|alias (pkg|git|adb|stat|cd)\" '.$f.'; fi"';
    $r = shell_exec($cmd);
    if ($r && trim($r)!==""){
      suspeito("Funções/aliases detectados em {$f} (possível camuflagem)",5);
    }
  }
}

/* ===================== 2. PROCESSOS SUSPEITOS ===================== */
function checkProcessos(){
  info("Analisando processos suspeitos");
  $r = shell_exec('adb shell "ps | grep -Ei \"(bypass|redirect|fake|inject)\" 2>/dev/null"');
  if ($r && trim($r)!==""){
    evidencia("Processos com padrão de bypass em execução",15);
    echo "     -> {$r}\n";
  }
}

/* ===================== 3. ROOT ===================== */
function checkRoot(){
  $r = shell_exec("adb shell su -c id 2>/dev/null");
  if ($r) evidencia("Root/Magisk confirmado",15);
  else info("Root não detectado");
}

/* ===================== 4. DATA / HORA ===================== */
function checkDataHora(){
  $sysY = trim(shell_exec("adb shell date +%Y 2>/dev/null"));
  $locY = date("Y");
  if ($sysY!==$locY) suspeito("Ano do sistema divergente do local",5);
  else info("Data/hora consistentes");
}

/* ===================== 5. ATIVIDADES SENSÍVEIS ===================== */
function checkAtividades(){
  info("Auditoria leve de atividades sensíveis");
  // Informativo (sem acusar)
  $r = shell_exec('adb shell "dumpsys package com.android.vending | head -5" 2>/dev/null');
  if ($r) info("Play Store acessível (informativo)");
}

/* ===================== 6. REPLAYS ===================== */
function checkReplays($pkg){
  $path = "/sdcard/Android/data/{$pkg}/files/MReplays";
  $r = shell_exec("adb shell ls {$path} 2>&1");
  if (strpos($r,"Permission denied")!==false){
    info("Replays inacessíveis (bloqueio padrão do Android)");
    return;
  }
  if (!$r){
    info("Pasta de replays não encontrada");
    return;
  }
  info("Analisando replays");
  $stat = shell_exec("adb shell stat {$path} 2>/dev/null");
  if ($stat && preg_match('/Modify:\s+(\d{4}-\d{2}-\d{2})/',$stat,$m)){
    if (strtotime($m[1]) < strtotime('2021-01-01')){
      suspeito("Timestamp suspeito em replays (data muito antiga)",10);
    }
  }
}

/* ===================== 7. SHADERS / MODS VISUAIS ===================== */
function checkShaders($pkg){
  $paths = [
    "/sdcard/Android/data/{$pkg}/files",
    "/sdcard/Android/obb/{$pkg}"
  ];
  foreach($paths as $p){
    $r = shell_exec("adb shell ls {$p} 2>&1");
    if (strpos($r,"Permission denied")!==false){
      info("Assets inacessíveis em {$p} (bloqueio padrão)");
      continue;
    }
    if ($r){
      $f = shell_exec("adb shell find {$p} -type f -iname \"*shader*\" -o -iname \"*wall*\" 2>/dev/null | head -5");
      if ($f && trim($f)!==""){
        evidencia("Arquivos gráficos suspeitos encontrados",20);
        echo "     -> {$f}\n";
      }
    }
  }
}

/* ===================== OBB (SEM FALSO POSITIVO) ===================== */
function checkOBB($pkg){
  $p = "/sdcard/Android/obb/{$pkg}";
  $r = shell_exec("adb shell ls {$p} 2>&1");
  if (strpos($r,"Permission denied")!==false){
    info("OBB inacessível (Android 13+ bloqueia)");
    return;
  }
  if (!$r){
    suspeito("Pasta OBB não localizada",10);
    return;
  }
  info("OBB acessível");
}

/* ===================== RELATÓRIO ===================== */
function relatorio(){
  global $pontuacao,$evidencias,$suspeitas,$infos,$G,$Y,$R,$W,$N;
  echo "\n{$W}=================================================={$N}\n";
  echo "{$W} RESULTADO FINAL – LCFFZ ANDROID{$N}\n";
  echo "{$W}=================================================={$N}\n";
  echo "Pontuação: {$pontuacao}\n";
  if ($pontuacao < 20) echo "{$G}Status: NORMAL{$N}\n";
  elseif ($pontuacao < 40) echo "{$Y}Status: SUSPEITO (revisar manualmente){$N}\n";
  else echo "{$R}Status: EVIDÊNCIA FORTE (análise detalhada){$N}\n";

  echo "\n--- Evidências ---\n";
  if (!$evidencias) echo "Nenhuma\n"; else foreach($evidencias as $e) echo "- {$e}\n";

  echo "\n--- Suspeitas ---\n";
  if (!$suspeitas) echo "Nenhuma\n"; else foreach($suspeitas as $s) echo "- {$s}\n";

  echo "\n--- Informações ---\n";
  foreach($infos as $i) echo "- {$i}\n";

  echo "\nHash LCFFZ: ".substr(hash("sha256",json_encode([$evidencias,$suspeitas,$infos])),0,16)."\n";
  echo "Data: ".date("Y-m-d H:i:s")."\n";
  echo "{$W}=================================================={$N}\n";
}

/* ===================== EXECUÇÃO ===================== */
banner();
checkADB();
$pkg = escolherJogo();
checkInstalacao($pkg);
checkShellIntegridade();
checkProcessos();
checkRoot();
checkDataHora();
checkAtividades();
checkReplays($pkg);
checkShaders($pkg);
checkOBB($pkg);
relatorio();
