# Final Film — Fotoalbum

Et WordPress-plugin, der giver produktioner et fotoalbum: en lightbox
man kan bladre i, og en valgfri egen side. Afløser Photo Gallery WD.

**Se det køre:** `prototype/index.html` — åbn den i en browser. Det er
den færdige frontend med rigtige billeder. Klik på et billede.

**Det fulde oplæg med adminskærm:**
https://claude.ai/code/artifact/2c3e852a-5491-439b-b1e4-7b403b46bcd0

---

## Hvad problemet er

På `/produktioner/` er der 88 kort. 84 videokort har
`<a href="....mp4" data-lity>` og spiller i en lightbox uden at forlade
siden. De 4 fotoproduktioner har `<a href="/produktion/xxx-fotos/">` og
navigerer væk. Det er den eneste forskel — og den er grunden til, at
foto føles som en anden slags indhold end video.

Albummet vises i dag af Photo Gallery WD, som har sit eget billedbibliotek
i `/uploads/photo-gallery/`, adskilt fra WordPress' mediebibliotek.

## Hvad der skal bygges

Et album bliver en almindelig indholdstype (`final_album`) med billederne
som attachment-ID'er fra mediebiblioteket. Så kan Photo Gallery WD
afskaffes, og fotos ligger samme sted som alt andet indhold.

Albummet vises tre steder — **med samme lightbox alle tre steder**:

1. inde i produktionen, via `[final_album]` i Pods-templaten
2. på sin egen side, `/album/<slug>/`, hvis kontakten er slået til
3. på oversigten, hvor fotokortet åbner lightboxen i stedet for at navigere væk

## Filerne

```
frontend/
  album.css          Gitter + lightbox. Produktionsklar, brug som den er.
  album.js           Lightbox + justeret gitter. Ingen afhængigheder, ingen jQuery.
  markup.html        Den markup PHP skal udsende. Klasserne er kontrakten.

wordpress/
  plugin.php         Bootstrap. Bemærk rækkefølgen af require.
  01-register-cpt.php    Indholdstypen, metafelterne, hjælpefunktioner, 404-logikken
  02-admin.php           De tre metabokse
  03-shortcode.php       [final_album] + render + lightbox i footeren
  04-enqueue.php         Assets. SKAL indlæses før 03.
  05-single-template.php Albummets egen side
  views/single-album.php Selve skabelonen
  06-listekort.php       Fotokortet på oversigten
  07-migrering.php       Engangsimport fra Photo Gallery WD

assets/
  admin.css, admin.js    Adminskærmen: wp.media, jQuery UI Sortable, træk-og-slip

pods/
  produktion-template.txt  Den ene linje, der skal ændres i Pods

prototype/
  index.html         Frontend'en kørende, med rigtige billeder
```

`frontend/album.css` og `frontend/album.js` er færdige. De er testet i
browseren på 1440 og 390 px: gitteret lægger sig i 3 henholdsvis 6
rækker, lightboxen åbner på det rigtige billede, piletaster og Esc
virker, adressen opdateres til `#foto-N`, og der er ingen vandret
overflow. Skriv gerne PHP'en om — men lad klassenavnene stå, for det er
dem, JS'en kobler sig på.

PHP-filerne er et stillads: de er syntakstjekket og hænger sammen, men
de er ikke kørt mod en rigtig WordPress-installation.

## Byggerækkefølge

1. **Indholdstypen** (`01`) — så der er noget at gemme i.
2. **Adminskærmen** (`02` + `assets/`) — så der kan oprettes et album i hånden.
3. **Shortcoden og assets** (`03` + `04`) — nu kan albummet vises.
4. **Pods-templaten** (`pods/`) — én linje. Nu virker produktionssiden.
5. **Egen side** (`05`).
6. **Oversigtskortene** (`06`).
7. **Flytningen** (`07`) — til sidst, når resten er kontrolleret.

## Fem ting der kan gå galt

**`fa_need_assets()` må kun defineres ét sted.** Den ligger i
`04-enqueue.php`. Defineres den også i `03`, giver PHP "Cannot redeclare
function", og hele sitet går ned. Derfor står `require` af `04` før `03`
i `plugin.php`.

**`max-height: 100%` på lightbox-billedet virker ikke.** `.fa-lb-mid`
får sin højde fra grid-rækken, men dens beregnede `height` er `auto`, og
så behandles procenten som `none`. Billedet løber ned under
miniature-striben. Derfor måles der mod vinduet: `calc(100dvh - var(--fa-chrome))`.
Det gælder også, hvis man prøver at lave en absolut placeret kasse
imellem — kæden af `auto`-højder skal brydes med en rigtig enhed.

**`data-w` og `data-h` skal med på hvert felt.** Uden dem kan gitteret
ikke regne rækkerne ud, før billederne er hentet, og layoutet hopper.
Målene kommer gratis fra `wp_get_attachment_image_src()`.

**Temaets `<ol>`/`<button>`-styles.** Kadence sætter baggrund, ramme,
radius og en hover-tilstand på alle `<button>`. `.fa-item` og
`.fa-lb-*` nulstiller det, men tjek det, hvis noget ser forkert ud — og
husk at editoren ikke indlæser `.entry-content`-reglerne, så en fejl kan
se fin ud i backend og gal ud på siden.

**Permalinks skal skylles**, når pluginnet slås til, ellers giver
`/album/xxx/` et 404. `register_activation_hook` gør det.

## Hvad der bevidst IKKE er med

- **Lity røres ikke.** Videoerne bliver ved med at bruge den. Den nye
  lightbox kan overtage dem senere, hvis I vil have det hele i én
  komponent — men det er en selvstændig beslutning.
- **Download af billeder.** Kontakten er tegnet på adminskærmen, men
  ikke bygget. Sig til, hvis den skal med i første omgang.
- **Flere albums pr. produktion.** Datamodellen tillader det (et album
  peger på en produktion), men hverken shortcoden eller skærmen håndterer
  det. Hvis det bliver aktuelt, er det shortcoden, der skal ændres.
