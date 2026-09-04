# Funktionel spec

Alt herunder er, hvad mockuppet gør. Afvig gerne, men sig til — det er
alt sammen valgt bevidst.

## Gitteret

Et **justeret gitter**: rækker af billeder, der skaleres så hver række
fylder bredden præcis ud. Ikke masonry, ikke firkantede beskæringer.
Grunden er, at albummet indeholder både stående og liggende billeder
(i AAH-albummet: 5 stående, 9 liggende ud af 14), og begge dele skal
kunne ses i deres eget format.

- Målhøjde pr. række: 260 px over 1000 px bredde, 210 px mellem 620 og
  1000, 150 px derunder.
- Mellemrum: 8 px.
- Sidste række skaleres **ikke** op. Ellers bliver den urimeligt høj,
  når der kun er ét eller to billeder tilbage.
- Rækkerne regnes ud fra `data-w` og `data-h` på hvert felt, **før**
  billederne er hentet. Uden dem hopper gitteret, mens billederne loader.
- Uden JavaScript falder gitteret tilbage til et simpelt CSS-gitter i
  3:2. Siden er stadig brugbar.

## Lightboxen

| Handling | Virkning |
|---|---|
| Klik på et billede | Åbner på præcis det billede |
| ← → | Forrige / næste, med ombrydning |
| Esc | Luk |
| Klik ved siden af billedet | Luk |
| Swipe til siden | Forrige / næste (over 45 px) |
| Swipe nedad | Luk (over 70 px) |
| Klik på miniature | Hop til det billede |
| Tab | Fokus holdes inde i lightboxen |

- Tælleren viser `3 / 14`.
- Adressen opdateres til `#foto-3`, så et enkelt billede kan deles.
  Åbner man en URL med `#foto-3`, åbner lightboxen på billede 3.
- Naboerne hentes på forhånd, så bladring føles øjeblikkelig.
- Ved ét billede skjules pile og miniature-stribe (`[data-single]`).
- Fokus vender tilbage til det felt, man klikkede på, når man lukker.
- `body` låses mod scroll, mens lightboxen er åben.

## Højden på billedet

`max-height: calc(100dvh - var(--fa-chrome))` — **ikke** `max-height: 100%`.

Det er ikke en smagssag. `.fa-lb-mid` får sin højde fra grid-rækken, men
dens *beregnede* `height` er `auto`, og så behandles en procent-baseret
`max-height` som `none`. Billedet bliver aldrig begrænset og løber ned
under miniature-striben. Det samme gælder et niveau nede, hvis man
prøver at lave en absolut placeret mellemliggende kasse.

`--fa-chrome` er 172 px på desktop og 148 px på mobil — top- og bundlinje
tilsammen. Ændrer I højden på dem, skal tallet med.

## Breakpoints

- **over 1000 px** — rækkehøjde 260, pile synlige, miniaturer 74×52
- **600–1000 px** — rækkehøjde 210
- **under 600 px** — rækkehøjde 150, pile skjult (der er ingen mus),
  miniaturer 56×40, `--fa-chrome` 148

## De tre steder albummet vises

1. **I produktionen** — `[final_album]` i Pods-templaten. Respekterer
   kontakten "Vis i produktionen".
2. **Albummets egen side** — `/album/<slug>/`. Findes kun når
   "Vis som egen side" er slået til; ellers svarer URL'en 404.
3. **På oversigten** — fotokortet åbner lightboxen i stedet for at
   navigere væk. Hele albummet ligger i DOM'en (resten skjult), så man
   kan bladre uden at hente noget.

## Tilgængelighed

- Felterne er `<button>`, ikke `<div>` — de kan nås med Tab og aktiveres
  med mellemrum og Enter uden ekstra kode.
- Lightboxen er `role="dialog" aria-modal="true"`.
- Alt-tekst kommer fra mediebibliotekets alt-felt.
- `prefers-reduced-motion` slår zoom og overgange fra.
