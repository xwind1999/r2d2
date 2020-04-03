# 1. Rename room to component

Date: 2020-04-02

## Status

Accepted

## Context

As we'll eventually need to store components other than rooms, we would need to store the relationships in a separate table.


## Decision

We decided to rename "room" to "component", and add an extra column "is_reservable" to this new table, so we can differentiate between rooms and other components.

## Consequences

* Extra table "experience_component" to hold the experience X components relationships
* Table "room" renamed to "component"
* New field added to the `component` table: `is_reservable`
