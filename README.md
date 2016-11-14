# Lazy Rendered
A wrapper to help developers create versatile user interfaces for modules in Administration Panel of the SilverStripe framework

The main purpose of this module is provide other modules a seamlessly easy way to create a custom interface where you can combine your imagination alongside the functionality that `ModelAdmin` and more

It is very much in its infancy and I implore your urge to submit a PR with anything from the ROADMAP below or anything you feel may benefit this module and other developers

### FEATURES

- Layout
    - Allows you to use `YourModule_Content.ss` as a layout, and injects your `[action]Render()` output to `$Body`
- Breadcrumb Support
    - Use `$CMSBreadcrumbs` in your template to provide breadcrumb support.

### ROADMAP

- `GridField` Adapter for DataRecord Widgets
    - Modify `Breadcrumb()` to account for viewing and editing of records
- Tabset Adapter
    - Allows you to add tabulated pages into your view via your `[action]Render()` method

### EXAMPLES

- None yet, sorry working on it, basically just extend `LazyRendered` instead of `LeftAndMain` and go from there.
