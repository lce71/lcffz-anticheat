# LCFFZ Anti-Cheat – Android (ADB)
Scanner Forense para Free Fire / Free Fire MAX

## 📌 Introdução

O **LCFFZ Anti-Cheat** é uma ferramenta de **verificação forense para dispositivos Android**, desenvolvida para auxiliar em **telagens competitivas**, **campeonatos** e **auditorias pós-partida** no jogo **Free Fire / Free Fire MAX**.

Diferente de anti-cheats tradicionais, o LCFFZ **não executa punições automáticas**.  
Ele **coleta, correlaciona e apresenta evidências técnicas**, permitindo uma análise humana justa e sem falso positivo.

O scanner utiliza **ADB (Android Debug Bridge)** para inspecionar:
- integridade do sistema
- comportamento do ambiente
- possíveis tentativas de bypass ou manipulação

---

## ⚙️ Requisitos

- Android 9 ou superior (recomendado)
- Termux instalado (F-Droid recomendado)
- Depuração USB ou Depuração sem fio ativada
- ADB autorizado no dispositivo
- PHP e android-tools instalados no Termux

---

## 📲 Instalação do Termux

Baixe o Termux via **F-Droid**.

Após abrir o Termux, execute:

```bash
pkg update && pkg upgrade -y
pkg install php android-tools -y
```

---

🔗 Pareamento ADB (Sem Fio)

1. Ative Opções do Desenvolvedor


2. Ative Depuração sem fio


3. Toque em Parear dispositivo com código


4. No Termux, execute:



```bash
adb pair localhost:PORTA CODIGO
```

Exemplo:

```bash
adb pair localhost:37199 123456
```

5. Conecte ao dispositivo:



```bash
adb connect localhost:PORTA_ATUALIZADA
```

6. Verifique a conexão:



```bash
adb devices
```

Saída esperada:

localhost:PORTA    device


---

▶️ Execução do LCFFZ

Coloque o arquivo lcffz_android.php na pasta desejada e execute:

```bash
php lcffz_android.php
```

Selecione:

[1] Free Fire
[2] Free Fire MAX


---

🔍 O que o LCFFZ Analisa

✔ Integridade do ambiente Shell

✔ Scripts e processos suspeitos

✔ Root / Magisk (como fator de risco)

✔ Auditoria de data, hora e fuso horário

✔ Atividades sensíveis (informativo)

✔ Análise forense de replays

✔ Detecção de shaders e mods gráficos

✔ Verificação de OBB (sem falso positivo em Android 13+)



---

📊 Interpretação dos Resultados

Pontuação Final

Pontuação	Status	Interpretação

0 – 19	NORMAL	Nenhuma evidência técnica relevante
20 – 39	SUSPEITO	Indícios leves, revisar manualmente
40+	EVIDÊNCIA FORTE	Múltiplas evidências correlacionadas


Observações Importantes

Root sozinho não confirma trapaça

Android 13+ bloqueia acesso à OBB (não é hack)

O scanner não pune automaticamente



---

🧠 Filosofia do Projeto

O LCFFZ é baseado em:

Integridade do sistema

Análise comportamental

Coerência temporal

Correlação de múltiplos indícios


Não depende apenas de assinaturas fixas, tornando-o mais resistente a:

cheats modernos

scripts de bypass

tentativas de ocultação ativa



---

⚠️ Aviso Legal

Esta ferramenta é destinada a:

fins educacionais

auditorias técnicas

telagens competitivas


O uso indevido é de responsabilidade do usuário.


---

👤 Créditos

LCFFZ Anti-Cheat
Desenvolvimento e conceito: LCFFZ
Ano: 2025


---

📬 Contribuições

Sugestões, melhorias e correções são bem-vindas.
Abra uma issue ou envie um pull request.


