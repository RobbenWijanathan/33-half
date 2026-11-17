import fs from "node:fs/promises";
import path from "node:path";
import { pathToFileURL } from "node:url";

const moduleRoot = process.env.CODEX_PRIMARY_RUNTIME_NODE_MODULES;
const artifactTool = path.join(moduleRoot, "@oai", "artifact-tool", "dist", "artifact_tool.mjs");
const { Presentation, PresentationFile } = await import(pathToFileURL(artifactTool).href);
const presentation = Presentation.create({slideSize: {width: 1920, height: 1080}});
const slide = presentation.slides.add();
slide.background.fill = "#F8E1BE";

const palette = {plum: "#360021", burgundy: "#820030"};

function addText(name, value, x, y, width, height, style) {
  const shape = slide.shapes.add({
    name,
    geometry: "textbox",
    position: {left:x, top:y, width, height},
    fill: "none",
    line: {fill:"none", width:0},
  });
  shape.text = value;
  shape.text.style = {
    color:palette.plum,
    wrap:"none",
    autoFit:"none",
    verticalAlignment:"middle",
    insets:{left:0, right:0, top:0, bottom:0},
    ...style,
  };
}

addText("References title", "REFERENCES", 105, 93, 1707, 182, {
  typeface:"Anton", fontSize:150
});

const entries = [
  {
    name:"GS SHOP",
    lines:["Video game retailer with", "a trade-in program."],
    alt:"GS SHOP storefront at Central Park Mall, Jakarta",
    source:"https://www.gsshop.id/trade-in-tukar-tambah-games",
    photoPage:"https://www.gsshop.id/toko/25/lokasi-gsshop-Jakarta-GS-SHOP-Central-Park-Mall-Lt1",
  },
  {
    name:"KICK AVENUE",
    lines:["Sneaker resale marketplace", "that authenticates items."],
    alt:"Kick Avenue sneaker showcase, photographed by Kompas.com",
    source:"https://www.kickavenue.com/en/helps/general-faq/125-how-is-the-authentication-process-at-kick-avenue",
    photoPage:"https://lifestyle.kompas.com/read/2020/07/21/171202920/kick-avenue-jamin-hanya-jual-barang-original-di-marketplace-nya",
  },
  {
    name:"NOBLE KNIGHT GAMES",
    lines:["Tabletop game store that", "buys and trades used games."],
    alt:"Interior of the Noble Knight Games tabletop game store",
    source:"https://www.nobleknight.com/about",
    photoPage:"https://www.yelp.com/biz/noble-knight-games-fitchburg-2",
  },
  {
    name:"ATLAS RECORDS",
    lines:["Used vinyl shop with separate", "record and sleeve grades."],
    alt:"Close-up of vinyl grooves from the Atlas Records grading guide",
    source:"https://atlasrecords.co.uk/pages/grading",
    photoPage:"https://atlasrecords.co.uk/cdn/shop/files/record-vinyl-playing-music.jpg?v=1704899661",
  },
];

for (const [index, entry] of entries.entries()) {
  const x = 108+index*433;
  const file = await fs.readFile(path.join("crops", `reference-${index}.jpeg`));
  slide.images.add({
    blob:file, contentType:"image/jpeg", alt:entry.alt, fit:"cover",
    position:{left:x, top:332, width:405, height:316},
  });
  addText(entry.name, entry.name, x, 676, 405, 54, {typeface:"Anton", fontSize:42});
  for (const [line, value] of entry.lines.entries()) {
    addText(`${entry.name} description ${line+1}`, value, x, 746+35*line, 405, 35, {
      typeface:"Poppins", fontSize:26,
    });
  }
}

slide.shapes.add({
  name:"Footer rule", geometry:"rect",
  position:{left:108, top:1011, width:1704, height:2},
  fill:palette.burgundy, line:{fill:"none", width:0},
});
addText("Slide number", "06 / 10", 1640, 1019, 172, 38, {
  typeface:"Poppins", fontSize:24, alignment:"right"
});

const notes = entries.map(({name,source,photoPage}) =>
  `${name} — business reference: ${source}\nImage: ${photoPage}`);
slide.speakerNotes.text = notes.join("\n\n");

const draft = await PresentationFile.exportPptx(presentation);
await draft.save("refs-slide.pptx");
