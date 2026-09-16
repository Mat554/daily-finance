---
name: zenith
description: Delegate a long-running, multi-step coding mission to the Zenith orchestrator. Zenith coordinates Claude Code, Codex, or Hermes as a multi-agent system over MCP/ACP and keeps working until every contract assertion is independently verified — it is designed for tasks where the main failure mode is premature completion, not inability to make progress.
---

# /zenith — Run a mission with the Zenith orchestrator (Hermes variant)

## What this skill does

When you run `/zenith <prompt>` in Hermes, this skill tells the host agent to switch into **Zenith Orchestrator** mode for the duration of the mission. Instead of doing the work directly, the host will:

1. Read `.hermes/orchestrator_prompt.md` and treat its contents as its primary operating role.
2. Drive the mission through the **Zenith MCP tools** — `start_project`, `submit_plan`, `advance_project`, `decide_attention`, `end_mission`, `abort_project`, `inspect_project` — instead of editing files itself.
3. Adaptively dispatch workers and validators, register reusable skills, replan when evidence demands it, and only stop when contract assertions are independently verified.

Zenith is an **agent harness for long-running work**. Its dominant failure mode is not "can't make progress" — it is "stops too early." Zenith's job is to keep reopening the gap between the current project state and the original requirement until that gap is closed.

## How to invoke

After this skill is installed, invoke it the same way you would invoke any skill — type `/zenith` followed by your mission prompt:

```text
/zenith Add CSV export to the /history page, with filters by date range, type, and category, and add a download button that respects the active filters.
```

The host agent will read `.hermes/orchestrator_prompt.md`, assume the orchestrator role, and run Zenith against your prompt.

## First line of every mission

The canonical first message Zenith expects in Hermes is:

```text
First read .hermes/orchestrator_prompt.md and treat it as your primary role, then use Zenith to run this mission.

<your instruction or query>
```

(`/zenith` packages that exact prompt for you — you only need to write the instruction. Note the path is `.hermes/`, not `.claude/` or `.codex/`.)

## Is Zenith running? Start it if not

Zenith is **lazy**: the CLI installs on demand, but a project's MCP wiring only happens after `zenith init` has staged `.mcp.json` into your workspace, and Hermes has registered the MCP server. Check before you start a mission.

### One-time setup (per machine)

```bash
# 1. Install the Python package (Python 3.11+, uv required)
cd /path/to/zenith
uv sync

# 2. Install ACP adapters for the harnesses you actually use
npm install -g @agentclientprotocol/claude-agent-acp   # Claude Code workers
npm install -g @agentclientprotocol/codex-acp          # Codex workers
# Hermes ships its own ACP runtime — no extra install needed (`hermes acp` is the worker ACP command).

# 3. Smoke test
uv run zenith --help
```

### Per-workspace setup (run once per project)

```bash
# From the zenith repo
uv run zenith init --workspace-dir /path/to/your-app --agent hermes
```

This stages:

- `.mcp.json` — points Hermes at the `zenith-server` MCP server.
- `.hermes/agents/` — subagent definitions (e.g. `contract-review`, `feature-reviewer`, `flow-validator`, `investigator`).
- `.hermes/skills/` — bundled Zenith skills (`engineering-mission-playbook`, `optimization-mission-playbook`, `agent-browser`, `scrutiny-validator`, `user-testing-validator`, `benchmark-validator`).
- `.hermes/orchestrator_prompt.md` — the system prompt that turns Hermes into the Zenith Orchestrator.

### Per-session startup

Launch Hermes **from inside the initialized workspace** so the MCP wiring is picked up:

```bash
cd /path/to/your-app
hermes
```

Then type `/zenith <your prompt>`.

### Quick health check

```bash
uv run zenith list-projects        # shows known project buckets
uv run zenith inspect-tasks --help # dry-run the compact task list renderer
```

If `list-projects` works but no project bucket exists yet, that is normal — the bucket is created the first time the orchestrator calls `start_project(brief, workspace_dir)`.

## When to use Zenith

Use `/zenith` when the mission is **multi-step, evidence-bearing, and easy to under-finish**:

- New feature, full vertical slice, or product surface area that touches more than a few files.
- Bug hunt where "obvious fixes" keep missing the real root cause.
- Migration, port, or refactor that must preserve behavior across a real surface (browser, API, CLI, jobs, migrations, generated artifacts).
- Mix of implementation + validation + benchmark/perf where you want independent verification, not just green tests.
- Multi-day work where a single long context window will not hold everything.

Skip `/zenith` for one-line edits, single-file tweaks, pure questions, or tasks where "looks done to me" is good enough. Zenith's overhead is real — its value is preventing premature completion, not adding ceremony to simple work.

## How Zenith thinks (so you can read its output)

The orchestrator follows a strict order every time it wakes:

1. **Investigate** the real actor, workflow, surface, environment, oracle, and risks before any planning.
2. **Pick a domain playbook** — `engineering-mission-playbook` (default) for durable codebase behavior; `optimization-mission-playbook` for metric search.
3. **Write `mission.md`** — the durable accepted scope charter (not the task list, not the contract).
4. **Author atomic `VAL-*` contract assertions** under `contract/` — one falsifiable promise per file, each with `Surface`, `Needs`, `Behavior`, and `Evidence`.
5. **Adversarial contract review** via the `contract-review` subagent (at least two passes for non-trivial missions). Fix gaps before planning tasks.
6. **Plan tasks** preserving the two-layer shape: many atomic assertions → fewer coherent `work` tasks, each task may own many related assertions → independent validators per assertion → gates per milestone.
7. **Confirm with you** before calling `submit_plan`. After that, drive execution through `advance_project` — workers implement, validators prove, gates seal.
8. **Handle attention honestly**: when the runtime returns `attention_needed`, diagnose the earliest invalid artifact (scope, contract, task, skill, setup, oracle, or evidence), patch it, and replan. Do not retry unchanged work when the real defect is upstream.
9. **Close only from evidence**, not from an empty task list — every live assertion needs validator evidence or an explicit accepted-risk decision.

If you read Zenith output and it asks you to confirm scope, read the scope charter it produced and confirm — that confirmation gate is on purpose.

## Background

- Repo: https://github.com/Intelligent-Internet/zenith
- Technical report: [`technical_report/Technical_Report.pdf`](https://github.com/Intelligent-Internet/zenith/blob/main/technical_report/Technical_Report.pdf) — *From RALPH to Zenith: Designing Harnesses for Long-Running Agents*, Intelligent Internet (2026)
- Benchmark: Zenith on GPT-5.5 ranks #1 on the Frontier SWE leaderboard (avg rank 2.06, 92% dominance) and beats RALPH on mean rank at less than half the per-task cost ($176 vs $408).
- License: Apache 2.0 (code), CC BY 4.0 (technical report).
