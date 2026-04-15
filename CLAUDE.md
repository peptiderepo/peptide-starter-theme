# Agent Bootstrap — peptide-starter-theme

**Read these files in order before making any edit:**

1. `AGENT-OPERATING-STANDARD.md` — process rules, QA gate, Definition of Done. Required reading on every session.
2. `ARCHITECTURE.md` — this repo's structure, data flow, integrations, key decisions.
3. `CONVENTIONS.md` — this repo's code style and extension patterns.

## Critical rules (full detail in AGENT-OPERATING-STANDARD.md)

- **No direct pushes to `main`.** PR-only. The deploy skills will refuse to push without a signed QA verdict for the commit SHA.
- **Commits authored as `peptiderepo`** (`git config user.name "peptiderepo"` / `user.email "peptiderepo@users.noreply.github.com"`). Verify before pushing.
- **Branch naming:** `claude/<scope>-<YYYYMMDD>`.
- **Commit trailer:** `Agent-Session: <session-id-or-description>`.
- **Doc-in-same-commit:** any change to file structure or data flow must update `ARCHITECTURE.md` / `CONVENTIONS.md` in the same commit.

## QA gate

Before any deploy, the `qa-review` skill spawns a fresh subagent to review the diff against `AGENT-OPERATING-STANDARD.md`. The verdict is written to `qa-reviews/<repo>/<date>-<sha>.md` in the workspace root and must be `APPROVE` or `APPROVE-WITH-P2-NOTES` for the deploy to proceed.

The agent that wrote the code cannot self-approve. This is structural, not advisory.
