import fs from "node:fs/promises";
import path from "node:path";
import { createHash } from "node:crypto";
import { createRequire } from "node:module";
import { pathToFileURL } from "node:url";

const work = process.cwd();
const runtime = process.env.CLOSING_RUNTIME_DEPS;
const skillDir = process.env.CLOSING_PRESENTATION_SKILL;
const toolsDir = path.join(skillDir, "container_tools");
const runtimeRequire = createRequire(path.join(runtime, "node", "package.json"));
const { FileBlob, PresentationFile } = await import(pathToFileURL(
  runtimeRequire.resolve("@oai/artifact-tool")
).href);
const source = path.resolve("../33half-references-work/output/33-half-pitch-with-references.pptx");
const candidate = path.resolve("closing-draft.pptx");

if (!process.argv.includes("--finalize")) {
  const sourcePresentation = await PresentationFile.importPptx(await FileBlob.load(source));
  const finalPresentation = await PresentationFile.importPptx(await FileBlob.load(candidate));
  for (const [deck, index, name] of [
    [sourcePresentation, 0, "source-cover"],
    ...finalPresentation.slides.items.map((_, index) => [
      finalPresentation, index, "slide-" + String(index + 1).padStart(2, "0"),
    ]),
  ]) {
    const slide = deck.slides.items[index];
    const image = await deck.export({ slide, format: "png", scale: 0.6 });
    await fs.writeFile(path.join("render", name + ".png"), new Uint8Array(await image.arrayBuffer()));
  }
}

if (process.argv.includes("--finalize")) {
  const { finalizePresentation } = await import(pathToFileURL(
    path.join(toolsDir, "artifact_tool_utils.mjs")
  ).href);
  const referenceSha256 = createHash("sha256").update(await fs.readFile(source)).digest("hex");
  await fs.mkdir("output", { recursive: true });
  const result = await finalizePresentation({
    workspaceDir: work,
    candidatePath: candidate,
    finalPath: path.resolve("output/33-half-pitch-with-thank-you.pptx"),
    pythonExecutable: path.join(runtime, "python", "python.exe"),
    integrityValidatorPath: path.join(toolsDir, "inspect_presentation_package_integrity.py"),
    layoutValidatorPath: path.join(toolsDir, "inspect_presentation_layout_geometry.py"),
    layoutArgs: ["--expected-slide-size-emu", "18288000,10287000", "--validate-bullet-geometry"],
    explicitTotalSlideCount: 11,
    verifyArtifactToolImport: true,
    fontPolicy: { basis: "reference", families: ["Anton", "Poppins"], referencePath: source, referenceSha256 },
    receiptPath: path.resolve("validation.json"),
  });
  console.log(JSON.stringify(result));
}
