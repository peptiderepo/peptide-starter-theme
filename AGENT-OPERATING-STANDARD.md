# Agent Operating Standard — peptiderepo

**Version:** 1.0.0
**Effective:** 2026-04-14
**Applies to:** every AI agent (Claude, Copilot, Cursor, future tools) working in any peptiderepo codebase.

This document is the single source of truth for *how* agents must work in peptiderepo repos. It travels with the code — every repo contains a copy at its root and a `CLAUDE.md` pointer that requires reading this file before any edit.

If this document conflicts with a per-repo `CONVENTIONS.md`, this document wins for **process**; `CONVENTIONS.md` wins for **code style**.

---

## 1. The QA Gate — Non-Negotiable

**No code reaches `main` without a signed QA verdict for its commit SHA.**

The QA reviewer is a separate Claude subagent (the `qa-review` skill) with no prior conversation context. The agent that wrote the code cannot be the agent that approves it. This is enforced by the deploy skills — they refuse to push if no signed `APPROVE` verdict exists for the SHA being deployed.

Verdicts are written to `qa-reviews/<repo>/<YYYY-MM-DD>-<sha>.md` in the workspace root and signed with: commit SHA, reviewer model string, ISO-8601 timestamp, and verdict.

### Verdict types

- `APPROVE` — ship it.
- `APPROVE-WITH-P2-NOTES` — ship it; create follow-up tickets for the noted P2/P3 issues.
- `REJECT-P1` — do not ship; fix the listed issues and re-submit.
- `REJECT-P0` — do not ship; the change has a security, data-loss, or production-impact risk that must be resolved before any further work on the branch.

### Severity definitions

- **P0:** secrets exposure, auth bypass, data loss, production-down risk, unauthorized financial cost (e.g., uncapped LLM spend).
- **P1:** Definition-of-Done violation, missing input sanitization or output escaping, missing authorization on a sensitive action, missing test for new core logic, schema change without migration.
- **P2:** AI-readability gap (missing docblock, file >300 lines, magic value), inconsistent naming, missing `@see` cross-reference.
- **P3:** style nit, opinion-level suggestion.

---

## 2. Definition of Done — every change must satisfy

The QA reviewer runs this checklist against the diff. Any unchecked item is at minimum a P1.

- [ ] Code follows the language/framework's standard coding conventions (per-repo `CONVENTIONS.md`)
- [ ] All input sanitized at boundary, all output escaped at render
- [ ] Authentication and authorization checks on all sensitive actions
- [ ] No hardcoded secrets — credentials live in env or config only
- [ ] All external API calls have error handling, timeout, and retry-with-backoff (max 3 retries)
- [ ] Cost/token impact documented for any AI-touching feature; budget hard-stop respected
- [ ] Unit tests for new core logic; integration tests for framework-specific behavior
- [ ] User-facing strings are translatable/localizable
- [ ] Consistent namespace/prefix on all global identifiers
- [ ] Teardown/uninstall path cleans up ALL project data
- [ ] Every new class has a preamble docblock (what / who triggers / dependencies)
- [ ] Every new public method has typed params, return type, and docblock noting side effects
- [ ] No magic methods, dynamic resolution, or clever indirection without explicit justification
- [ ] No file exceeds 300 lines (split if it does)
- [ ] `ARCHITECTURE.md` and `CONVENTIONS.md` updated if the change affects file structure, data flow, or patterns
- [ ] `@see` cross-references at top of files participating in multi-class flows
- [ ] Event/hook docblocks list known listeners and where they're registered
- [ ] Commits authored as `peptiderepo` (not `ongterence` or any personal identity)

---

## 3. Git Workflow — PR-Gated, Soft-Enforced

All four peptiderepo repos are private on the GitHub free plan, which does not support branch protection. The review gate is enforced at the agent layer via these rules.

### Rules

1. **Never push to `main` directly.** Every change lands via a pull request. No self-merging from agents. No force-pushing to `main`.
2. **Branch naming:** `claude/<scope>-<YYYYMMDD>` — e.g., `claude/cost-tracker-20260414`.
3. **Commit trailer:** every commit ends with `Agent-Session: <session-id-or-description>` so commits correlate back to the conversation that produced them.
4. **PR description template:**
   - **What changed** (one paragraph)
   - **Why** (motivation or incident link)
   - **Risk flags** (schema changes, API contract changes, cost impact, compatibility)
   - **Test plan** (what was run locally; what Terence should smoke-test after merge)
   - **QA verdict path** (`qa-reviews/<repo>/<date>-<sha>.md`)
5. **Emergency push exception:** if the situation genuinely requires pushing to `main` without a PR (site down, CI broken, one-line hotfix), ask Terence in chat first. Every emergency push gets a follow-up PR for the audit trail.

### Mechanics

`gh` CLI is not installed in the Cowork sandbox. Use `curl` with the PAT from `.env.credentials` (workspace root). See `pr-workflow-rollout/project-instructions-addendum.md` for the exact `curl` snippet.

---

## 4. Security Non-Negotiables

- Sanitize all input at the boundary; escape all output at render.
- Authenticate and authorize every sensitive action.
- Secrets in environment/config only — never hardcoded, never committed, never exposed to the browser.
- Use a consistent namespace/prefix on all global identifiers.
- All paid-API calls happen server-side only.

---

## 5. Cost-Conscious AI Integration

Any feature that calls a paid LLM/AI API must:

- Respect a token/dollar budget with a **hard stop** when exhausted.
- Log every call: timestamp, provider, model, prompt tokens, completion tokens, estimated cost.
- Use the cheapest model that does the job; expensive models only where required and configurable.
- Cache identical calls within a configurable window.
- Use structured output (JSON mode) when the API supports it.
- Batch where possible.
- Offer dry-run mode for user-facing operations.
- Fail with retry-and-backoff (max 3); never retry indefinitely.

---

## 6. AI-Readability Requirements

This codebase is maintained by AI agents. Code must be optimized for LLM comprehension.

- Files under 300 lines. Split when they grow.
- Every class has a preamble: *what / who triggers / dependencies*.
- Every public method has typed params, return type, and a docblock listing side effects.
- Names are verb-noun and unambiguous (`generate_post_from_topic()`, not `process()`).
- Constants over magic values (`MAX_RETRIES`, not `3`).
- Event/hook names describe the event (`before_content_generation`, not `hook_1`).
- No clever indirection — flat, explicit call chains.
- Inline comments explain *why*, not *what*.
- `@see` cross-references at the top of files in multi-class flows.

---

## 7. Documentation as Code

Every change that touches structure or data flow must include doc updates **in the same commit**:

- New file → add to `ARCHITECTURE.md` file tree.
- New pattern → update `CONVENTIONS.md`.
- New external integration → update `ARCHITECTURE.md` integrations section.
- Non-obvious decision → add to `ARCHITECTURE.md` "key decisions".

Documentation that drifts from reality actively misleads the next agent. QA will reject changes that modify structure without updating docs (P1).

---

## 8. When This Standard Changes

This file is versioned. Bump the version in the header on any material change and add an entry to the changelog below.

### Changelog

- **1.0.0 (2026-04-14)** — initial standard, established centralized QA gate model.
