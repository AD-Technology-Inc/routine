# AD. Routine

> Adaptive AI Goal & Routine Operating System

AD. Routine is a production-oriented goal execution platform that combines deterministic scheduling, behavioral analytics, capacity modeling, and AI-assisted planning into a single adaptive system.

Unlike traditional task managers, habit trackers, or calendar applications, AD. Routine focuses on execution. It continuously transforms goals into actionable work, schedules tasks according to real-world constraints, and adapts recommendations based on user behavior and performance.

## Philosophy

AD. Routine is built around a simple principle:

> Goals are outcomes. Tasks are commitments. Schedules are decisions.

Most productivity systems require users to manually decide what to work on every day. AD. Routine automates that process through a scheduling engine that evaluates priorities, dependencies, energy requirements, available capacity, and historical performance.

The result is a system that helps users focus on execution rather than planning.

---

## Core Features

### Goal Management

* Hierarchical goal organization
* Goal progress tracking
* Goal-specific task planning
* Goal archiving and lifecycle management

### Intelligent Task Modeling

* Task priorities and classifications
* Energy-level requirements
* Dependency management
* Flexible scheduling policies
* Task splitting and merging
* Subtask support

### Deterministic Scheduling Engine

* Rolling 7-day planning window
* Capacity-aware scheduling
* Dependency resolution
* Priority-based allocation
* Energy-aware task placement
* Automatic task grouping
* Adaptive rescheduling
* Split and defer logic

### Routine System

* Daily and recurring routines
* Routine instance generation
* Progress tracking
* Frequency-based automation

### Analytics & Momentum Tracking

* Completion rate monitoring
* Energy-performance analysis
* Behavioral pattern detection
* Workload effectiveness tracking
* Historical productivity snapshots

### AI-Assisted Planning

* Goal decomposition
* Roadmap generation
* Weekly performance reviews
* Context-aware coaching
* Conversational planning assistant

---

## Architecture

```text
AD. Routine
├── Laravel Backend
│   ├── Goals
│   ├── Tasks
│   ├── Scheduling Engine
│   ├── Routines
│   ├── Analytics
│   ├── AI Services
│   ├── Events & Jobs
│   └── REST API
│
└── Vue Frontend
    ├── Dashboard
    ├── Goal Workspace
    ├── Task Board
    ├── Routine Management
    ├── Analytics
    └── AI Coach
```

---

## Scheduling Engine

The scheduling engine is the core of AD. Routine.

Instead of assigning tasks directly to calendar dates, AD. Routine generates rolling execution plans based on:

* Priority
* Dependencies
* User capacity
* Energy requirements
* Scheduling flexibility
* Historical behavior

The engine evaluates pending work and produces an optimized schedule that fits within the user's available capacity.

Key capabilities include:

* Constraint-based planning
* Dependency-aware ordering
* Task grouping
* Task splitting
* Capacity packing
* Rolling-window regeneration
* Missed-task recovery

---

## Event-Driven Architecture

AD. Routine uses an event-driven workflow to keep schedules, analytics, and recommendations synchronized.

Examples:

* Completing a task triggers schedule regeneration
* Skipping work updates analytics models
* Daily jobs generate routines and snapshots
* AI reviews use historical performance data
* Schedule generation updates cached execution plans

This approach enables asynchronous processing and scalable execution workflows.

---

## Technology Stack

### Backend

* Laravel 13
* PHP 8.3+
* MySQL
* Redis
* Queue Workers

### Frontend

* Vue 3
* TypeScript
* Inertia.js
* Tailwind CSS v4
* Pinia

### AI Layer

* OpenAI / Anthropic (optional)
* AI Roadmap Generation
* AI Weekly Reviews
* AI Coach Chat

---

## Analytics Engine

AD. Routine continuously analyzes execution behavior to identify patterns such as:

* Low-performance weekdays
* Energy mismatches
* Frequently skipped task clusters
* Estimation inaccuracies
* Workload imbalance

The system transforms these observations into actionable recommendations designed to improve consistency and execution quality.

---

## API Overview

```http
GET    /api/goals
POST   /api/goals

GET    /api/tasks/{task}
POST   /api/tasks/{task}/complete
POST   /api/tasks/{task}/skip

GET    /api/schedule/today
GET    /api/schedule/window

GET    /api/routines/today

GET    /api/analytics/summary

POST   /api/goals/{goal}/ai-plan
POST   /api/goals/{goal}/ai-review
POST   /api/chat
```

---

## Design Goals

* Deterministic before AI
* Execution over planning
* Capacity-aware scheduling
* Observable user behavior
* Adaptive recommendations
* Event-driven architecture
* Production-ready scalability

---

## Status

AD. Routine is currently designed as a production-ready MVP architecture featuring deterministic scheduling, analytics, routine orchestration, and AI-assisted coaching built on Laravel, Vue, Redis, and modern event-driven patterns.
