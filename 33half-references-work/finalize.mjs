import fs from "node:fs/promises";
import path from "node:path";
import { createHash } from "node:crypto";
import { pathToFileURL } from "node:url";

const workspaceDir = process.cwd();
const skillDir = process.env.REFERENCES_PRESENTATION_SKILL;
const toolDir = path.join(skillDir, "container_tools");
const { finalizePresentation } = await import(pathToFileURL(
  path.join(toolDir, "artifact_tool_utils.mjs")
).href);
const referencePath = path.resolve("input/source.pptx");
const referenceSha256 = createHash("sha256")
  .update(await fs.readFile(referencePath)).digest("hex");
await fs.mkdir("output", {recursive:true});

const result = await finalizePresentation({
  workspaceDir,
  candidatePath:path.resolve("deck-draft.pptx"),
  finalPath:path.resolve("output/33-half-pitch-with-references.pptx"),
  pythonExecutable:process.env.REFERENCES_RUNTIME_PYTHON,
  integrityValidatorPath:path.join(toolDir, "inspect_presentation_package_integrity.py"),
  layoutValidatorPath:path.join(toolDir, "inspect_presentation_layout_geometry.py"),
  layoutArgs:["--expected-slide-size-emu", "18288000,10287000", "--validate-bullet-geometry", "--validate-heading-fit"],
  explicitTotalSlideCount:10,
  verifyArtifactToolImport:true,
  fontPolicy:{
    basis:"reference",
    families:["Anton", "Poppins"],
    referencePath,
    referenceSha256,
  },
  receiptPath:path.resolve("validation.json"),
});
console.log(JSON.stringify(result));
