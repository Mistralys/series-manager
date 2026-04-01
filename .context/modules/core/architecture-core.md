# Core - Architecture
_SOURCE: Public class signatures_
# Public class signatures
```
// Structure of documents
└── assets/
    └── classes/
        └── Bootstrap3Renderer.php
        └── FormHandler.php
        └── Manager.php
        └── ManagerException.php
        └── SeriesCollection.php
        └── UI.php

```
###  Path: `\assets\classes/Bootstrap3Renderer.php`

```php
namespace Mistralys\SeriesManager;

use AppUtils\OutputBuffering as OutputBuffering;
use HTML_QuickForm2_Container_Group as HTML_QuickForm2_Container_Group;
use HTML_QuickForm2_Element_Button as HTML_QuickForm2_Element_Button;
use HTML_QuickForm2_Element_InputCheckbox as HTML_QuickForm2_Element_InputCheckbox;
use HTML_QuickForm2_Element_InputRadio as HTML_QuickForm2_Element_InputRadio;
use HTML_QuickForm2_Node as HTML_QuickForm2_Node;
use HTML_QuickForm2_Renderer as HTML_QuickForm2_Renderer;

class Bootstrap3Renderer extends HTML_QuickForm2_Renderer
{
	public const RENDERER_ID = 'Bootstrap3Renderer';

	public function renderHidden(HTML_QuickForm2_Node $element): void
	{
		/* ... */
	}


	public function renderElement(HTML_QuickForm2_Node $element): void
	{
		/* ... */
	}


	public function startGroup(HTML_QuickForm2_Container_Group $group): void
	{
		/* ... */
	}


	public function finishGroup(HTML_QuickForm2_Container_Group $group): void
	{
		/* ... */
	}


	public function getID(): string
	{
		/* ... */
	}


	public function reset(): self
	{
		/* ... */
	}


	public function startForm(HTML_QuickForm2_Node $form): void
	{
		/* ... */
	}


	public function finishForm(HTML_QuickForm2_Node $form): void
	{
		/* ... */
	}


	public function startContainer(HTML_QuickForm2_Node $container): void
	{
		/* ... */
	}


	public function finishContainer(HTML_QuickForm2_Node $container): void
	{
		/* ... */
	}


	public function __toString()
	{
		/* ... */
	}
}


```
###  Path: `\assets\classes/FormHandler.php`

```php
namespace Mistralys\SeriesManager;

use HTML_QuickForm2 as HTML_QuickForm2;
use HTML_QuickForm2_Renderer as HTML_QuickForm2_Renderer;
use HTML_QuickForm2_Renderer_Proxy as HTML_QuickForm2_Renderer_Proxy;

class FormHandler
{
	public const RENDERER_ID = 'Bootstrap3';

	public function getForm(): HTML_QuickForm2
	{
		/* ... */
	}


	public function isValid(): bool
	{
		/* ... */
	}


	public function getValues(): array
	{
		/* ... */
	}


	public function render(): string
	{
		/* ... */
	}


	public function display(): void
	{
		/* ... */
	}


	public function addHiddenVar(string $name, string $value): self
	{
		/* ... */
	}
}


```
###  Path: `\assets\classes/Manager.php`

```php
namespace Mistralys\SeriesManager;

use Adrenth\Thetvdb\Client as Client;
use AppLocalize\Localization as Localization;
use AppUtils\FileHelper\JSONFile as JSONFile;
use AppUtils\Request as Request;
use Mistralys\ChangelogParser\ChangelogParser as ChangelogParser;
use Mistralys\SeriesManager\Series\Series as Series;
use Throwable as Throwable;

class Manager
{
	public const REQUEST_PARAM_PAGE = 'page';
	const SESSION_DARK_MODE = 'darkmode';
	const REQUEST_VAR_RETURN_PAGE = 'returnPage';

	public static function getInstance(): Manager
	{
		/* ... */
	}


	public static function isDarkMode(): bool
	{
		/* ... */
	}


	public static function getDocumentTitle(): string
	{
		/* ... */
	}


	public static function setDocumentTitle(?string $title): void
	{
		/* ... */
	}


	public static function getName(): string
	{
		/* ... */
	}


	public function getVersion(): string
	{
		/* ... */
	}


	public function getPages(): array
	{
		/* ... */
	}


	public function getPageID(): string
	{
		/* ... */
	}


	public function start(): void
	{
		/* ... */
	}


	public function getContent(): string
	{
		/* ... */
	}


	/**
	 * @return SeriesCollection
	 */
	public function getSeries(): SeriesCollection
	{
		/* ... */
	}


	public function getSelectedID(): string
	{
		/* ... */
	}


	public function getSelected(): ?Series
	{
		/* ... */
	}


	public function isLoggedIn(): bool
	{
		/* ... */
	}


	public function isPasswordValid(string $password): bool
	{
		/* ... */
	}


	public function encodePassword(string $password): string
	{
		/* ... */
	}


	public function createClient(): Client
	{
		/* ... */
	}


	public function getCustomLinkDefs(): array
	{
		/* ... */
	}


	/**
	 * @param string $searchTerm
	 * @return array<int,array{label:string,url:string}>
	 */
	public function prepareCustomLinks(string $searchTerm): array
	{
		/* ... */
	}


	/**
	 * @param array<int,array{label:string,template:string}> $linkDefs
	 * @return array<int,array{label:string,url:string}>
	 */
	public function prepareLinks(array $linkDefs, string $searchTerm): array
	{
		/* ... */
	}


	public function getURLAdd(array $params = []): string
	{
		/* ... */
	}


	public function getURL(array $params = []): string
	{
		/* ... */
	}


	/**
	 * @param array<string,string|number> $params
	 * @return never
	 */
	public function redirectToReturnPage(array $params = []): void
	{
		/* ... */
	}


	public static function initLocalization(): void
	{
		/* ... */
	}


	public function setDarkMode(bool $enabled): self
	{
		/* ... */
	}


	/**
	 * @param string $url
	 * @return never
	 */
	public function redirect(string $url): void
	{
		/* ... */
	}
}


```
###  Path: `\assets\classes/ManagerException.php`

```php
namespace Mistralys\SeriesManager;

use AppUtils\BaseException as BaseException;

class ManagerException extends BaseException
{
	public const ERROR_UNKNOWN_SEASON = 180601;
}


```
###  Path: `\assets\classes/SeriesCollection.php`

```php
namespace Mistralys\SeriesManager;

use AppUtils\FileHelper\JSONFile as JSONFile;
use Mistralys\SeriesManager\Series\Series as Series;

class SeriesCollection
{
	public function add(array $data): Series
	{
		/* ... */
	}


	/**
	 * @return Series[]
	 */
	public function getAll(): array
	{
		/* ... */
	}


	public function handle_sortByName(Series $a, Series $b): int
	{
		/* ... */
	}


	public function getByIMDBID(string $id): ?Series
	{
		/* ... */
	}


	public function IMDBIDExists(string $id): bool
	{
		/* ... */
	}


	public function delete(Series $targetSeries): void
	{
		/* ... */
	}


	public function save(): void
	{
		/* ... */
	}


	public function fetchData(bool $clearCache = false): array
	{
		/* ... */
	}
}


```
###  Path: `\assets\classes/UI.php`

```php
namespace Mistralys\SeriesManager;

class UI
{
	public static function prettyBool(bool $value): string
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 6.36 KB
- **Lines**: 439
File: `modules/core/architecture-core.md`
