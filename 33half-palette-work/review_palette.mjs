import fs from "node:fs/promises";
import path from "node:path";
import { createHash } from "node:crypto";
import { createRequire } from "node:module";
import { pathToFileURL } from "node:url";

const workspaceDir = process.cwd();
const runtime = process.env.PALETTE_RUNTIME;
const toolsDir = path.join(process.env.PALETTE_SKILL, "container_tools");
const source = path.resolve("../33half-closing-work/output/33-half-pitch-with-thank-you.pptx");
const candidatePath = path.resolve("palette-draft.pptx");
const finalPath = path.resolve("output/33-half-pitch-with-simplified-palette.pptx");

if (process.argv.includes("--finalize")) {
  const { finalizePresentation } = await import(pathToFileURL(
    path.join(toolsDir, "artifact_tool_utils.mjs")
  ).href);
  const candidateHash = createHash("sha256").update(await fs.readFile(candidatePath)).digest("hex");
  try {
    await finalizePresentation({
      workspaceDir, candidatePath, finalPath,
      pythonExecutable: path.join(runtime, "python", "python.exe"),
      integrityValidatorPath: path.join(toolsDir, "inspect_presentation_package_integrity.py"),
      layoutValidatorPath: path.join(toolsDir, "inspect_presentation_layout_geometry.py"),
      layoutArgs: ["--expected-slide-size-emu", "18288000,10287000"],
      explicitTotalSlideCount: 11,
      verifyArtifactToolImport: true,
      receiptPath: path.resolve("validation.json"),
    });
  } catch (error) {
    // Some Windows workspace locations disallow the finalizer's publication hard link.
    if (error.code !== "EPERM" || error.syscall !== "link" || error.dest !== finalPath) throw error;
    await fs.copyFile(candidatePath, finalPath, 1);
  }
  const finalHash = createHash("sha256").update(await fs.readFile(finalPath)).digest("hex");
  if (candidateHash !== finalHash) throw new Error("Final deck differs from the validated draft.");
  console.log(finalPath);
} else {
  const runtimeRequire = createRequire(path.join(runtime, "node", "package.json"));
  const { FileBlob, PresentationFile } = await import(pathToFileURL(
    runtimeRequire.resolve("@oai/artifact-tool")
  ).href);
  const presentation = await PresentationFile.importPptx(await FileBlob.load(candidatePath));
  if (presentation.slides.items.length !== 11) throw new Error("Unexpected slide count.");
  const slide = presentation.slides.items[7];
  const image = await presentation.export({ slide, format: "png", scale: 0.6 });
  await fs.writeFile("render/palette.png", new Uint8Array(await image.arrayBuffer()));
}
