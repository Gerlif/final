# Bloggen — sådan kommer det op på siden

Fire filer. `blog.css` skal ind ét sted; de tre HTML-filer indsættes som
**Custom HTML**-blokke i editoren. Der ændres ikke i temaet, og der
installeres ikke noget.

F-mærket ligger som baggrundsbillede inde i CSS'en, så der er ingen
billeder at uploade. Vil I hellere have det fra mediebiblioteket, ligger
`final-f-maerke.png` her — upload den og skift `url(data:image/png;base64,…)`
ud med filens adresse to steder i `blog.css`.

---

## 1. CSS'en

Indsæt hele `blog.css` samme sted som `.svc-*`, `.fp-*` og `.proc-*` ligger
i dag (Tilpas ▸ Ekstra CSS, eller den snippet I bruger). Rækkefølgen er
ligegyldig — alt er prefikset `.fb-`, og de steder, hvor temaet skal
overtrumfes, står klassen to gange.

---

## 2. Oversigtssiden

`/tips-og-inspiration-til-videoproduktion/` er en almindelig side med en
**Kadence Posts-blok**. Der skal ikke laves om på markup'en:

1. Klik på Posts-blokken.
2. **Avanceret ▸ Ekstra CSS-klasse(r)** → skriv `fb-posts`.
3. Sæt blokken til **2 kolonner** (CSS'en tvinger det alligevel, men så
   passer det, I ser i editoren, med det, der kommer ud).

Så bliver kortene til det, mockup'et viser: 16:9-billede med
"Inspiration"-chip, titel i Libre Baskerville, resumé på tre linjer og en
fod med F-mærket og "Skrevet af Final Film".

Afsenderen i foden er skrevet i CSS'en (`::before` og `::after`), fordi den
er ens på alle kort. Skal teksten laves om, står den ét sted:
`.fb-posts .entry-footer::after { content: "…" }`.

---

## 3. Indlægssiden

Indholdet ligger i Kadence-rækker i selve indlægget, så det er dér, det
bygges. Tre indgreb:

### 3a. Afsenderen under overskriften

Indsæt `1-afsender.html` som en **Custom HTML**-blok lige under `<h1>`.
Ret læsetiden i teksten — den skrives i hånden pr. indlæg.

### 3b. To-kolonne-rækken

1. Læg en **Række-blok** med **2 kolonner** omkring brødteksten.
   Fordelingen er ligegyldig; CSS'en sætter den til `1fr / 380px`.
2. Rækken: **Avanceret ▸ Ekstra CSS-klasse(r)** → `fb-row`.
3. Højre kolonne: **Avanceret ▸ Ekstra CSS-klasse(r)** → `fb-rail`.
4. Flyt de eksisterende indholdsrækker ind i **venstre** kolonne — også
   rækken med det udvalgte billede. Bliver den stående udenfor, står
   billedet centreret midt på siden, mens artiklen står til venstre, og
   de to kanter passer ikke sammen.
5. Indsæt `2-sidepanel.html` i **højre** kolonne, og sæt jeres
   Instagram-blok ind, hvor kommentaren siger det. Bruger I en shortcode,
   kan den stå direkte i HTML-blokken. Er det en rigtig blok, så del
   HTML'en i to blokke og læg Instagram-blokken imellem — CSS'en er den
   samme.
6. Ret adresse, billede og titel i "Læs også".

Panelet klæber 96 px under toppen, altså lige under den faste menu, og
falder ned under artiklen på tablet og mobil.

**Den store "Har du brug for hjælp?"-række skal blive udenfor** rækken med
de to kolonner, så den bliver ved med at gå i fuld bredde.

### 3c. Del-række og afsenderkort

Indsæt `3-under-artiklen.html` som en **Custom HTML**-blok nederst i
venstre kolonne — efter brødteksten, før "Har du brug for hjælp?".

Blokken har et lille script i bunden, der bygger delelinkene ud fra sidens
egen adresse. Så kan den samme blok bruges uændret på alle indlæg. Kører
scriptet ikke, peger LinkedIn- og Facebook-ikonet på jeres profiler — ikke
i stykker, bare mindre nyttigt.

### 3d. Valgfrit: manchetten

Vil I have første afsnit sat op i grad som i mockup'et, så giv afsnittet
klassen `fb-lede` under **Avanceret ▸ Ekstra CSS-klasse(r)**.

---

## Tre ting mere, som ikke er CSS

1. **"Mere inspiration"-rækken skal ud af indlægget.** Den står i dag med
   overskriften og teksten "Ingen indlæg" under, fordi det andet indlæg
   ikke er offentligt. Panelet overtager den rolle.
2. **Mobilfilm-indlægget er ikke offentligt.** Adressen svarer 404, og
   API'et giver 401. Så længe det er sådan, har "Læs også" ikke noget at
   vise — lad blokken være ude, indtil indlægget er publiceret igen.
3. **Datoen står stadig i de strukturerede data.** Yoast skriver
   `datePublished: 2023-09-11` ud, selvom siden ikke viser den. Det er
   dét, Google kan finde på at vise i søgeresultatet.

---

## Instagram-feedet i panelet

Feedet leverer lige nu fire opslag, og forsiden viser tre af dem på
desktop. I panelet vises alle fire i to kolonner — derfor 2 × 2 og ikke
3 i bredden, som ville efterlade ét opslag alene på anden række. Sætter I
feedet op til seks opslag, bliver panelet 2 × 3 af sig selv.

Billedteksterne under opslagene skjules i panelet; der er ikke plads til
dem i 380 px.

---

## Bonus: produktionssiden

`.fp-mark` på produktionssiden viser det brede FINAL-logo skaleret ned til
26 px bredde inde i en 44 px cirkel — det bliver kun 6 px højt. Vil I have
samme F-mærke som på bloggen, er det to linjer:

```css
.fp-mark{
	background-image: url("…samme url som .fb-av…");
	background-repeat: no-repeat;
	background-position: 48% 50%;
	background-size: 25px auto;
}
.fp-mark img{ display: none; }
```
