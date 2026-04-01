# Project - Folder Structure
_SOURCE: Source classes_
# Source classes
###  
```
└── assets/
    └── classes/
        └── Bootstrap3Renderer.php
        └── FormHandler.php
        └── Manager.php
        └── Manager/
            ├── Library.php
            ├── LibraryException.php
            ├── LibraryFile.php
            ├── LibrarySubfolder.php
        └── ManagerException.php
        └── Series/
            ├── Episode.php
            ├── Season.php
            ├── Series.php
            ├── SeriesForm.php
            ├── SeriesList.php
        └── SeriesCollection.php
        └── UI.php

```
_SOURCE: Pages_
# Pages
###  
```
└── pages/
    └── _frame.php
    └── add.php
    └── archive.php
    └── archived.php
    └── delete.php
    └── edit.php
    └── edit/
        ├── tab-seasons.php
        ├── tab-settings.php
        ├── tab-summary.php
    └── favorite.php
    └── fetch.php
    └── library.php
    └── library/
        ├── files-list.php
        ├── folders-list.php
        ├── index-status.php
        ├── name-aliases.php
    └── list.php
    └── login.php
    └── unarchive.php
    └── unfavorite.php

```
_SOURCE: Frontend assets_
# Frontend assets
###  
```
└── css/
    ├── bootstrap.min.css
    ├── bootswatch.min.css
    ├── main.css
└── js/
    └── add.js
    └── bootstrap.min.js
    └── jquery-1.11.2.min.js
    └── list.js

```
_SOURCE: Tests_
# Tests
###  
```
└── tests/
    └── classes/
        ├── SeriesManagerSuite.php
    └── files/
        ├── library-2/
        │   └── Almas.Not.Normal.S01E05.mp4
    └── testsuites/
        └── Library/
            └── IndexingTests.php

```
_SOURCE: Localization_
# Localization
###  
```
└── localization/
    └── de_DE-series-manager-classes-server.ini
    └── fr_FR-series-manager-classes-server.ini
    └── index.php
    └── storage.json

```
_SOURCE: Documentation_
# Documentation
###  
```
└── docs/
    └── agents/
        └── project-manifest/
            └── README.md
            └── api-surface.md
            └── constraints.md
            └── data-flows.md
            └── tech-stack.md

```
---
**File Statistics**
- **Size**: 2.67 KB
- **Lines**: 117
File: `project-folder-structure.md`
