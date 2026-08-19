# Forside-refresh — hvad der er ændret

`mockup-refresh.html` er bygget oven på den nuværende final.dk-forside.
Samme sektioner i samme rækkefølge, samme tekst, samme fonts, samme farver,
samme billeder. Alt er hentet fra den live side.

## Hentet direkte fra final.dk

| | Nuværende side | Brugt i mockup |
|---|---|---|
| Overskrifter | Libre Baskerville 700 | Samme (Google Fonts) |
| Brødtekst | Nunito Sans, #D9D9D9 | Samme |
| Knapper/UI | REM | Samme |
| Baggrunde | #1D2028 / #252C39 | Samme |
| Accent | #8D8DFF / #4E4EFF | Samme |
| Knap-gradient | linear-gradient(143deg,#4141DF,#BC34FF) | Samme |
| Gradient-bånd | FinalGradient1 / FinalGradient7 | Samme billedfiler |
| Cases | 6 produktioner | Samme 6, samme klient/bureau-tekst |

Viktors Farmor og Trekantområdet ligger som video uden still på siden, så de
to felter viser brandgradienten med logo-watermark i stedet for et billede.

## Ændringer

1. **Hierarki.** Sektionsoverskrifter er venstrestillede med en over-overskrift
   (kicker) og ét kursivt modled — systemet fra jeres præsentationer. Forsiden
   er i dag centreret hele vejen ned, hvilket gør alt lige vigtigt.
2. **Luft.** Sektionspadding er hævet til `clamp(4.5rem, 9vw, 8rem)`, og
   linjeafstanden på overskrifter er strammet fra 1,2 til 1,08.
3. **Produktionerne fylder mere.** Asymmetrisk grid med varierede formater
   frem for seks lige store felter. Arbejdet er det stærkeste argument på siden.
4. **Fladere kort.** Rammer og runde hjørner er væk; i stedet hårfine linjer
   under hvert servicekort. Mindre template-agtigt.
5. **SEO-taggene er dæmpet.** De 23 links er samlet i ét roligt chip-bånd på
   mørk bund i stedet for et stort knapgitter midt på siden.
6. **Gradienten som billede.** Jeres egne gradientfiler ligger som fuldbredde
   baggrund med et scrim, så teksten holder kontrast.
7. **Bevægelse.** Langsom drift på hero-billedet, reveal ved scroll, hover-zoom
   på stills. Alt slået fra ved `prefers-reduced-motion`.

## Ikke ændret

Tekst, tone, navigation, sektionsrækkefølge, kontaktoplysninger og farvevalg.
