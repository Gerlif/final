# Kategorivælger til /produktioner/

Filter-UI til produktionsoversigten på final.dk. Pakken indeholder færdig CSS,
JavaScript og WordPress-eksempler. Produktionernes eget markup og styling skal
**ikke** ændres — filtreringen skjuler og viser de elementer, der allerede findes.

---

## Hvad der er i pakken

| Fil | Hvad det er |
|---|---|
| `filter-ui.css` | Al styling til filterbjælken. Rører intet andet på siden. |
| `filter-ui.js` | Filtreringen. Læser `data-`attributter fra DOM'en — ingen API-kald, ingen data duplikeret. |
| `filter-ui.html` | Markup til filterbjælken + kontrakten for et produktionselement. |
| `demo.html` | **Åbn denne først.** Filtrene kørende på markup kopieret direkte fra final.dk/produktioner/. |
| `wordpress/01-register-taxonomy.php` | Taksonomien `produktionstype`. |
| `wordpress/02-render-loop.php` | Ét WP_Query-loop, der erstatter de fire håndbyggede faner. |
| `wordpress/03-enqueue.php` | Indlæsning af CSS/JS på de rigtige sider. |
| `prototype/produktioner-prototype.html` | Designreference med alle 115 produktioner. Ikke produktionskode. |
| `screenshots/` | Desktop og mobil. |

---

## Opgavens omfang

**Skal laves:** kategorivælgeren — hovedkategorier, typefilter, søgning, indlæsning ved scroll.

**Skal ikke laves om:** thumbnails, hover-video, lity-lightbox, titel- og klientlinje,
grid-layout, farver på produktionerne. Det bliver som det er i dag.

---

## Sådan hænger det sammen

JavaScriptet kender ikke til WordPress, ACF eller taksonomier. Det læser tre
attributter på hvert produktionselement:

```html
<div class="produktioner-grid-item"
     data-main="video"                                 <!-- én hovedkategori (slug) -->
     data-types="brandingfilm,kampagne"                <!-- 0..n typer (slugs, kommasepareret) -->
     data-search="Titel på filmen Kundenavn">          <!-- valgfri; ellers bruges elementets tekst -->
```

Din opgave er derfor primært at få de rigtige slugs ud i de attributter.
Resten virker af sig selv.

Filterbjælken kender sine egne elementer på `data-ff="..."`. De må ikke omdøbes:

| Attribut | Element |
|---|---|
| `data-ff="seg"` | Beholder til hovedkategori-knapper (fyldes af JS) |
| `data-ff="toggle"` | Knappen der folder typerne ud |
| `data-ff="panel"` / `data-ff="chips"` | Det sammenfoldelige panel og chip-beholderen |
| `data-ff="search"` / `input` / `clear` | Søgefeltet |
| `data-ff="empty"` | Tom tilstand (skjules/vises automatisk) |
| `data-ff="loader"` | Indlæsningszonen. **Skal ligge efter grid'et.** |
| `data-ff="reset"` | Knap der rydder alle filtre (kan sidde hvor som helst) |

---

## Trin for trin

### 1. Opret taksonomien

`wordpress/01-register-taxonomy.php`. Hierarkisk taksonomi `produktionstype`:
forældre = de tre hovedkategorier, børn = de 20+ typer.

Skyl permalinks bagefter (Indstillinger → Permalinks → Gem). Konflikter
`/produktioner/brandingfilm/` med siden `/produktioner/`, så skift rewrite-slug
til `produktioner/type`.

### 2. Tag produktionerne

115 produktioner skal have én forælder-term og mindst én barn-term.
De tre hovedkategorier kan tages fra de nuværende faner:

| Fane i dag | Antal |
|---|---|
| Video | 75 |
| Animation og VFX | 18 |
| Foto | 4 |

Bemærk: fanen "Alle" viser i dag 88 af de 115 produktioner. De faner er
håndbyggede og ude af sync — brug dem som udgangspunkt, ikke som facit.

Bulk-redigering i wp-admin (vælg flere → Redigér) kan sætte termer på mange
poster ad gangen.

### 3. Erstat de fire faner med ét loop

`wordpress/02-render-loop.php`. De 185 håndindsatte blokke fjernes, og ét loop
udskriver alle produktioner én gang. Det er det, der gør, at en ny produktion
kun skal oprettes ét sted fremover.

Markup inde i `.produktioner-grid-item` er kopieret fra den nuværende side og
skal blive som det er.

#### Felter der skal mappes

Loopet bruger pladsholdere, som skal peges mod jeres faktiske ACF-felter:

| I filen | Skal være | Bemærkning |
|---|---|---|
| `get_field('video_fil')` | URL til mp4 | Bruges tre steder: lity-link, `data-video-src`, `<source>` |
| `get_field('klient')` | Kundenavn | Vises i klientlinjen og indgår i søgningen |
| `get_the_post_thumbnail_url()` | Thumbnail | Skift størrelse hvis I bruger en anden |

### 4. Indsæt filterbjælken

Kopiér blokken fra `filter-ui.html` ind mellem sidens overskrift og grid'et.
Husk `data-ff="empty"` og `data-ff="loader"` **efter** grid'et.

### 5. Indlæs CSS og JS

`wordpress/03-enqueue.php`. Filerne lægges i child theme under `/css/` og `/js/`.

### 6. Initialisér

```js
FinalFilter.init({
  batch: 24,
  mains: [
    { slug: '',                 label: 'Alle' },
    { slug: 'video',            label: 'Video' },
    { slug: 'animation-og-vfx', label: 'Animation og VFX' },
    { slug: 'foto',             label: 'Foto' }
  ],
  labels: { 'employer-branding': 'Employer branding' },  // kun hvis slug ikke giver et pænt navn
  onFilter: function () { if (window.AOS) AOS.refreshHard(); }
});
```

Udelades `mains`, udleder scriptet dem fra `data-main` i DOM'en. Eksplicit liste
giver kontrol over rækkefølge og navne.

---

## Fælder, der er værd at kende

**AOS.** Produktionerne har `data-aos="fade"`. Når et element skjules og vises
igen, skal AOS have besked — derfor `onFilter`-callbacket. Alternativt kan
`data-aos` fjernes fra grid-items.

**Lazy-loaded videoer.** Kun ~24 elementer er synlige ad gangen, hvilket i sig
selv er en gevinst i forhold til i dag. Starter jeres hover-video-script på alle
elementer ved page load, så overvej at koble det på `onFilter` i stedet.

**Rækkefølge i stylesheet.** Media-blokken `@media (max-width:600px)` i
`filter-ui.css` skal blive stående **til sidst**. Flyttes den op over de regler,
den overskriver, taber den kaskaden, og mobilvisningen holder op med at virke.

**`.ff-hidden` bruger `display:none !important`.** Det er med vilje: temaet sætter
selv `display` på grid-items, og uden `!important` vinder temaet.

**URL'en.** Scriptet skriver filtertilstanden i query-strengen
(`?kategori=video&type=brandingfilm`) og læser den ved load, så et filtreret
udvalg kan deles. Det erstatter ikke rigtige term-arkiver — se nedenfor.

---

## SEO: de rigtige URL'er

Filtrering i JavaScript er til brugeren. Google indekserer kun det, der ligger i
HTML'en ved første load.

Derfor: lad taksonomien have sine egne arkivsider (`/produktioner/brandingfilm/`)
med et almindeligt WordPress-arkivtemplate, der udskriver netop den kategoris
produktioner. Filterbjælken kan genbruges der — sæt bare `data-main`/`data-types`
på elementerne som normalt.

Chips kan så linke til de rigtige URL'er i stedet for kun at filtrere i JS. Det
er en lille ændring i `renderChips()` (skift `<button>` til `<a href>`), og det
kan gøres senere uden at røre resten.

---

## Tjekliste før aflevering

- [ ] Alle 115 produktioner har både en hovedkategori og mindst én type
- [ ] Tallene i knapperne stemmer med, hvad der faktisk vises
- [ ] Typepanelet folder ud og i, og lukker efter valg på telefon
- [ ] Valgt type vises på knappen, når panelet er foldet sammen, og kan ryddes med ×
- [ ] Søgning finder både titler og kundenavne, også med æøå
- [ ] Nye produktioner hentes ved scroll, og spinneren forsvinder til sidst
- [ ] Tom tilstand vises, når intet matcher
- [ ] `?kategori=…&type=…` genskaber tilstanden ved reload
- [ ] Luften over og under filterbjælken er ens — foldet såvel som udfoldet
- [ ] Testet i 1440 / 1024 / 768 / 390 px uden vandret scroll
- [ ] Hover-video og lity-lightbox virker stadig efter filtrering
- [ ] Tastaturnavigation: alle knapper kan nås, fokus er synligt

---

## Testet i denne pakke

`demo.html` er kørt med produktionsmarkup kopieret fra final.dk:
48 elementer, filtrering på hovedkategori og type, søgning, tom tilstand,
nulstilling og indlæsning ved scroll — uden JavaScript-fejl.
PHP-filerne er syntakstjekket.
