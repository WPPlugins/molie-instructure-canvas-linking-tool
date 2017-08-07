<?php

	class Emogrifier
	{
		const CACHE_KEY_CSS = 0;

		const CACHE_KEY_SELECTOR = 1;

		const CACHE_KEY_XPATH = 2;

		const CACHE_KEY_CSS_DECLARATIONS_BLOCK = 3;

		const CACHE_KEY_COMBINED_STYLES = 4;

		const INDEX = 0;

		const MULTIPLIER = 1;

		const ID_ATTRIBUTE_MATCHER = '/(\\w+)?\\#([\\w\\-]+)/';

		const CLASS_ATTRIBUTE_MATCHER = '/(\\w+|[\\*\\]])?((\\.[\\w\\-]+)+)/';

		const CONTENT_TYPE_META_TAG = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';

		const DEFAULT_DOCUMENT_TYPE = '<!DOCTYPE html>';

		private $html = '';

		private $css = '';

		private $excludedSelectors = [];

		private $unprocessableHtmlTags = ['wbr'];

		private $allowedMediaTypes = ['all' => true, 'screen' => true, 'print' => true];

		private $caches = [
			self::CACHE_KEY_CSS => [],
			self::CACHE_KEY_SELECTOR => [],
			self::CACHE_KEY_XPATH => [],
			self::CACHE_KEY_CSS_DECLARATIONS_BLOCK => [],
			self::CACHE_KEY_COMBINED_STYLES => [],
		];

		private $visitedNodes = [];

		private $styleAttributesForNodes = [];

		private $isInlineStyleAttributesParsingEnabled = true;

		private $isStyleBlocksParsingEnabled = true;

		private $shouldKeepInvisibleNodes = true;

		private $xPathRules = [
			// child
			'/\\s+>\\s+/'                              => '/',
			// adjacent sibling
			'/\\s+\\+\\s+/'                            => '/following-sibling::*[1]/self::',
			// descendant
			'/\\s+/'                                   => '//',
			// :first-child
			'/([^\\/]+):first-child/i'                 => '\\1/*[1]',
			// :last-child
			'/([^\\/]+):last-child/i'                  => '\\1/*[last()]',
			// attribute only
			'/^\\[(\\w+|\\w+\\=[\'"]?\\w+[\'"]?)\\]/'  => '*[@\\1]',
			// attribute
			'/(\\w)\\[(\\w+)\\]/'                      => '\\1[@\\2]',
			// exact attribute
			'/(\\w)\\[(\\w+)\\=[\'"]?(\\w+)[\'"]?\\]/' => '\\1[@\\2="\\3"]',
		];

		private $shouldMapCssToHtml = false;

		private $cssToHtmlMap = [
			'background-color' => [
				'attribute' => 'bgcolor',
			],
			'text-align' => [
				'attribute' => 'align',
				'nodes' => ['p', 'div', 'td'],
				'values' => ['left', 'right', 'center', 'justify'],
			],
			'float' => [
				'attribute' => 'align',
				'nodes' => ['table', 'img'],
				'values' => ['left', 'right'],
			],
			'border-spacing' => [
				'attribute' => 'cellspacing',
				'nodes' => ['table'],
			],
		];

		public function __construct($html = '', $css = '')
		{
			$this->setHtml($html);
			$this->setCss($css);
		}

		public function __destruct()
		{
			$this->purgeVisitedNodes();
		}

		public function setHtml($html)
		{
			$this->html = $html;
		}

		public function setCss($css)
		{
			$this->css = $css;
		}

		public function emogrify()
		{
			if ($this->html === '') {
				throw new BadMethodCallException('Please set some HTML first before calling emogrify.', 1390393096);
			}

			$xmlDocument = $this->createXmlDocument();
			$this->process($xmlDocument);
			$data = $xmlDocument->saveHTML();
			$data = preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $data);
			$data = explode("</style>",$data);
			return $data[1];
		}

		public function emogrifyBodyContent()
		{
			if ($this->html === '') {
				throw new BadMethodCallException('Please set some HTML first before calling emogrify.', 1390393096);
			}

			$xmlDocument = $this->createXmlDocument();
			$this->process($xmlDocument);

			$innerDocument = new DOMDocument();
			foreach ($xmlDocument->documentElement->getElementsByTagName('body')->item(0)->childNodes as $childNode) {
				$innerDocument->appendChild($innerDocument->importNode($childNode, true));
			}

			return $innerDocument->saveHTML();
		}

		protected function process(\DOMDocument $xmlDocument)
		{
			$xPath = new DOMXPath($xmlDocument);
			$this->clearAllCaches();

			// Before be begin processing the CSS file, parse the document and normalize all existing CSS attributes.
			// This changes 'DISPLAY: none' to 'display: none'.
			// We wouldn't have to do this if DOMXPath supported XPath 2.0.
			// Also store a reference of nodes with existing inline styles so we don't overwrite them.
			$this->purgeVisitedNodes();

			$nodesWithStyleAttributes = $xPath->query('//*[@style]');
			if ($nodesWithStyleAttributes !== false) {
				/** @var DOMElement $node */
				foreach ($nodesWithStyleAttributes as $node) {
					if ($this->isInlineStyleAttributesParsingEnabled) {
						$this->normalizeStyleAttributes($node);
					} else {
						$node->removeAttribute('style');
					}
				}
			}

			// grab any existing style blocks from the html and append them to the existing CSS
			// (these blocks should be appended so as to have precedence over conflicting styles in the existing CSS)
			$allCss = $this->css;

			if ($this->isStyleBlocksParsingEnabled) {
				$allCss .= $this->getCssFromAllStyleNodes($xPath);
			}

			$cssParts = $this->splitCssAndMediaQuery($allCss);
			$excludedNodes = $this->getNodesToExclude($xPath);
			$cssRules = $this->parseCssRules($cssParts['css']);
			foreach ($cssRules as $cssRule) {
				// query the body for the xpath selector
				$nodesMatchingCssSelectors = @$xPath->query($this->translateCssToXpath($cssRule['selector']));
				// ignore invalid selectors
				if ($nodesMatchingCssSelectors === false) {
					continue;
				}

				/** @var DOMElement $node */
				foreach ($nodesMatchingCssSelectors as $node) {
					if (in_array($node, $excludedNodes, true)) {
						continue;
					}

					// if it has a style attribute, get it, process it, and append (overwrite) new stuff
					if ($node->hasAttribute('style')) {
						// break it up into an associative array
						$oldStyleDeclarations = $this->parseCssDeclarationsBlock($node->getAttribute('style'));
					} else {
						$oldStyleDeclarations = [];
					}
					$newStyleDeclarations = $this->parseCssDeclarationsBlock($cssRule['declarationsBlock']);
					if ($this->shouldMapCssToHtml) {
						$this->mapCssToHtmlAttributes($newStyleDeclarations, $node);
					}
					$data = $this->generateStyleStringFromDeclarationsArrays($oldStyleDeclarations, $newStyleDeclarations);
					if($data[0]!=""){
						$node->setAttribute(
							'wp-style-inline',
							$data[1]
						);
						$node->setAttribute(
							'style',
							$data[0]
						);
					}
				}
			}

			if ($this->isInlineStyleAttributesParsingEnabled) {
				$this->fillStyleAttributesWithMergedStyles();
			}

			if ($this->shouldKeepInvisibleNodes) {
				$this->removeInvisibleNodes($xPath);
			}

			$this->copyCssWithMediaToStyleNode($xmlDocument, $xPath, $cssParts['media']);
		}

		private function mapCssToHtmlAttributes(array $styles, DOMNode $node)
		{
			foreach ($styles as $property => $value) {
				// Strip !important indicator
				$value = trim(str_replace('!important', '', $value));
				$this->mapCssToHtmlAttribute($property, $value, $node);
			}
		}

		private function mapCssToHtmlAttribute($property, $value, DOMNode $node)
		{
			if (!$this->mapSimpleCssProperty($property, $value, $node)) {
				$this->mapComplexCssProperty($property, $value, $node);
			}
		}

		private function mapSimpleCssProperty($property, $value, DOMNode $node)
		{
			if (!isset($this->cssToHtmlMap[$property])) {
				return false;
			}

			$mapping = $this->cssToHtmlMap[$property];
			$nodesMatch = !isset($mapping['nodes']) || in_array($node->nodeName, $mapping['nodes'], true);
			$valuesMatch = !isset($mapping['values']) || in_array($value, $mapping['values'], true);
			if (!$nodesMatch || !$valuesMatch) {
				return false;
			}
		
			$node->setAttribute($mapping['attribute'], $value);

			return true;
		}

		private function mapComplexCssProperty($property, $value, DOMNode $node)
		{
			$nodeName = $node->nodeName;
			$isTable = $nodeName === 'table';
			$isImage = $nodeName === 'img';
			$isTableOrImage = $isTable || $isImage;

			switch ($property) {
				case 'background':
					// Parse out the color, if any
					$styles = explode(' ', $value);
					$first = $styles[0];
					if (!is_numeric(substr($first, 0, 1)) && substr($first, 0, 3) !== 'url') {
						// This is not a position or image, assume it's a color
						$node->setAttribute('bgcolor', $first);
					}
					break;
				case 'width':
					// intentional fall-through
				case 'height':
					// Remove 'px'. This regex only conserves numbers and %
					$number = preg_replace('/[^0-9.%]/', '', $value);
					$node->setAttribute($property, $number);
					break;
				case 'margin':
					if ($isTableOrImage) {
						$margins = $this->parseCssShorthandValue($value);
						if ($margins['left'] === 'auto' && $margins['right'] === 'auto') {
							$node->setAttribute('align', 'center');
						}
					}
					break;
				case 'border':
					if ($isTableOrImage) {
						if ($value === 'none' || $value === '0') {
							$node->setAttribute('border', '0');
						}
					}
					break;
				default:
			}
		}

		private function parseCssShorthandValue($value)
		{
			$values = preg_split('/\\s+/', $value);

			$css = [];
			$css['top'] = $values[0];
			$css['right'] = (count($values) > 1) ? $values[1] : $css['top'];
			$css['bottom'] = (count($values) > 2) ? $values[2] : $css['top'];
			$css['left'] = (count($values) > 3) ? $values[3] : $css['right'];

			return $css;
		}

		private function parseCssRules($css)
		{
			$cssKey = md5($css);
			if (!isset($this->caches[self::CACHE_KEY_CSS][$cssKey])) {
				// process the CSS file for selectors and definitions
				preg_match_all('/(?:^|[\\s^{}]*)([^{]+){([^}]*)}/mis', $css, $matches, PREG_SET_ORDER);

				$cssRules = [];
				/** @var string[] $cssRule */
				foreach ($matches as $key => $cssRule) {
					$cssDeclaration = trim($cssRule[2]);
					if ($cssDeclaration === '') {
						continue;
					}

					$selectors = explode(',', $cssRule[1]);
					foreach ($selectors as $selector) {
						// don't process pseudo-elements and behavioral (dynamic) pseudo-classes;
						// only allow structural pseudo-classes
						if (strpos($selector, ':') !== false && !preg_match('/:\\S+\\-(child|type\\()/i', $selector)) {
							continue;
						}

						$cssRules[] = [
							'selector' => trim($selector),
							'declarationsBlock' => $cssDeclaration,
							// keep track of where it appears in the file, since order is important
							'line' => $key,
						];
					}
				}

				usort($cssRules, [$this, 'sortBySelectorPrecedence']);

				$this->caches[self::CACHE_KEY_CSS][$cssKey] = $cssRules;
			}

			return $this->caches[self::CACHE_KEY_CSS][$cssKey];
		}

		public function disableInlineStyleAttributesParsing()
		{
			$this->isInlineStyleAttributesParsingEnabled = false;
		}

		public function disableStyleBlocksParsing()
		{
			$this->isStyleBlocksParsingEnabled = false;
		}

		public function disableInvisibleNodeRemoval()
		{
			$this->shouldKeepInvisibleNodes = false;
		}

		public function enableCssToHtmlMapping()
		{
			$this->shouldMapCssToHtml = true;
		}

		private function clearAllCaches()
		{
			$this->clearCache(self::CACHE_KEY_CSS);
			$this->clearCache(self::CACHE_KEY_SELECTOR);
			$this->clearCache(self::CACHE_KEY_XPATH);
			$this->clearCache(self::CACHE_KEY_CSS_DECLARATIONS_BLOCK);
			$this->clearCache(self::CACHE_KEY_COMBINED_STYLES);
		}

		private function clearCache($key)
		{
			$allowedCacheKeys = [
				self::CACHE_KEY_CSS,
				self::CACHE_KEY_SELECTOR,
				self::CACHE_KEY_XPATH,
				self::CACHE_KEY_CSS_DECLARATIONS_BLOCK,
				self::CACHE_KEY_COMBINED_STYLES,
			];
			if (!in_array($key, $allowedCacheKeys, true)) {
				throw new InvalidArgumentException('Invalid cache key: ' . $key, 1391822035);
			}

			$this->caches[$key] = [];
		}

		private function purgeVisitedNodes()
		{
			$this->visitedNodes = [];
			$this->styleAttributesForNodes = [];
		}
		
		public function addUnprocessableHtmlTag($tagName)
		{
			$this->unprocessableHtmlTags[] = $tagName;
		}
		
		public function removeUnprocessableHtmlTag($tagName)
		{
			$key = array_search($tagName, $this->unprocessableHtmlTags, true);
			if ($key !== false) {
				unset($this->unprocessableHtmlTags[$key]);
			}
		}

		public function addAllowedMediaType($mediaName)
		{
			$this->allowedMediaTypes[$mediaName] = true;
		}

		public function removeAllowedMediaType($mediaName)
		{
			if (isset($this->allowedMediaTypes[$mediaName])) {
				unset($this->allowedMediaTypes[$mediaName]);
			}
		}

		public function addExcludedSelector($selector)
		{
			$this->excludedSelectors[$selector] = true;
		}

		public function removeExcludedSelector($selector)
		{
			if (isset($this->excludedSelectors[$selector])) {
				unset($this->excludedSelectors[$selector]);
			}
		}

		private function removeInvisibleNodes(\DOMXPath $xPath)
		{
			$nodesWithStyleDisplayNone = $xPath->query(
				'//*[contains(translate(translate(@style," ",""),"NOE","noe"),"display:none")]'
			);
			if ($nodesWithStyleDisplayNone->length === 0) {
				return;
			}

			// The checks on parentNode and is_callable below ensure that if we've deleted the parent node,
			// we don't try to call removeChild on a nonexistent child node
			/** @var DOMNode $node */
			foreach ($nodesWithStyleDisplayNone as $node) {
				if ($node->parentNode && is_callable([$node->parentNode, 'removeChild'])) {
					$node->parentNode->removeChild($node);
				}
			}
		}

		private function normalizeStyleAttributes(\DOMElement $node)
		{
			$normalizedOriginalStyle = preg_replace_callback(
				'/[A-z\\-]+(?=\\:)/S',
				function (array $m) {
					return strtolower($m[0]);
				},
				$node->getAttribute('style')
			);

			// in order to not overwrite existing style attributes in the HTML, we
			// have to save the original HTML styles
			$nodePath = $node->getNodePath();
			if (!isset($this->styleAttributesForNodes[$nodePath])) {
				$this->styleAttributesForNodes[$nodePath] = $this->parseCssDeclarationsBlock($normalizedOriginalStyle);
				$this->visitedNodes[$nodePath] = $node;
			}

			$node->setAttribute('style', $normalizedOriginalStyle);
		}

		private function fillStyleAttributesWithMergedStyles()
		{
			foreach ($this->styleAttributesForNodes as $nodePath => $styleAttributesForNode) {
				$node = $this->visitedNodes[$nodePath];
				$currentStyleAttributes = $this->parseCssDeclarationsBlock($node->getAttribute('style'));
				$node->setAttribute(
					'style',
					$this->generateStyleStringFromDeclarationsArrays(
						$currentStyleAttributes,
						$styleAttributesForNode
					)
				);
			}
		}

		private function generateStyleStringFromDeclarationsArrays(array $oldStyles, array $newStyles)
		{
			foreach($newStyles as $key => $style){
				if($style == "inherit"){
					unset($newStyles[$key]);
				}
			}
			
			$combinedStyles = array_merge($oldStyles, $newStyles);
			
			$cacheKey = serialize($combinedStyles);
			if (isset($this->caches[self::CACHE_KEY_COMBINED_STYLES][$cacheKey])) {
				return $this->caches[self::CACHE_KEY_COMBINED_STYLES][$cacheKey];
			}

			foreach ($oldStyles as $attributeName => $attributeValue) {
				if (!isset($newStyles[$attributeName])) {
					continue;
				}
				$newAttributeValue = $newStyles[$attributeName];
				if ($this->attributeValueIsImportant($attributeValue) && !$this->attributeValueIsImportant($newAttributeValue)) {
					$combinedStyles[$attributeName] = $attributeValue;
				}
			}

			$style = '';
			foreach ($combinedStyles as $attributeName => $attributeValue) {
				$style .= strtolower(trim($attributeName)) . ': ' . trim($attributeValue) . '; ';
			}
			$trimmedStyle = rtrim($style);

			$this->caches[self::CACHE_KEY_COMBINED_STYLES][$cacheKey] = $trimmedStyle;

			
			$style = '';
			foreach ($newStyles as $attributeName => $attributeValue) {
				$style .= strtolower(trim($attributeName)) . ': ' . trim($attributeValue) . '; ';
			}
			$trimmedStyle = rtrim($style);

			$this->caches[self::CACHE_KEY_COMBINED_STYLES][$cacheKey] = $trimmedStyle;

			return array($trimmedStyle, $new_style);
			
		}

		private function attributeValueIsImportant($attributeValue)
		{
			return strtolower(substr(trim($attributeValue), -10)) === '!important';
		}

		private function copyCssWithMediaToStyleNode(\DOMDocument $xmlDocument, DOMXPath $xPath, $css)
		{
			if ($css === '') {
				return;
			}

			$mediaQueriesRelevantForDocument = [];

			foreach ($this->extractMediaQueriesFromCss($css) as $mediaQuery) {
				foreach ($this->parseCssRules($mediaQuery['css']) as $selector) {
					if ($this->existsMatchForCssSelector($xPath, $selector['selector'])) {
						$mediaQueriesRelevantForDocument[] = $mediaQuery['query'];
						break;
					}
				}
			}

			$this->addStyleElementToDocument($xmlDocument, implode($mediaQueriesRelevantForDocument));
		}

		private function extractMediaQueriesFromCss($css)
		{
			preg_match_all('#(?<query>@media[^{]*\\{(?<css>(.*?)\\})(\\s*)\\})#s', $css, $mediaQueries);
			$result = [];
			foreach (array_keys($mediaQueries['css']) as $key) {
				$result[] = [
					'css' => $mediaQueries['css'][$key],
					'query' => $mediaQueries['query'][$key],
				];
			}
			return $result;
		}
		
		private function existsMatchForCssSelector(\DOMXPath $xPath, $cssSelector)
		{
			$nodesMatchingSelector = @$xPath->query($this->translateCssToXpath($cssSelector));

			return $nodesMatchingSelector !== false && $nodesMatchingSelector->length !== 0;
		}

		private function getCssFromAllStyleNodes(\DOMXPath $xPath)
		{
			$styleNodes = $xPath->query('//style');

			if ($styleNodes === false) {
				return '';
			}

			$css = '';
			/** @var DOMNode $styleNode */
			foreach ($styleNodes as $styleNode) {
				$css .= "\n\n" . $styleNode->nodeValue;
				$styleNode->parentNode->removeChild($styleNode);
			}

			return $css;
		}

		protected function addStyleElementToDocument(\DOMDocument $document, $css)
		{
			$styleElement = $document->createElement('style', $css);
			$styleAttribute = $document->createAttribute('type');
			$styleAttribute->value = 'text/css';
			$styleElement->appendChild($styleAttribute);

			$head = $this->getOrCreateHeadElement($document);
			$head->appendChild($styleElement);
		}

		private function getOrCreateHeadElement(\DOMDocument $document)
		{
			$head = $document->getElementsByTagName('head')->item(0);

			if ($head === null) {
				$head = $document->createElement('head');
				$html = $document->getElementsByTagName('html')->item(0);
				$html->insertBefore($head, $document->getElementsByTagName('body')->item(0));
			}

			return $head;
		}

		private function splitCssAndMediaQuery($css)
		{
			$cssWithoutComments = preg_replace('/\\/\\*.*\\*\\//sU', '', $css);

			$mediaTypesExpression = '';
			if (!empty($this->allowedMediaTypes)) {
				$mediaTypesExpression = '|' . implode('|', array_keys($this->allowedMediaTypes));
			}

			$media = '';
			$cssForAllowedMediaTypes = preg_replace_callback(
				'#@media\\s+(?:only\\s)?(?:[\\s{\\(]' . $mediaTypesExpression . ')\\s?[^{]+{.*}\\s*}\\s*#misU',
				function ($matches) use (&$media) {
					$media .= $matches[0];
				},
				$cssWithoutComments
			);

			// filter the CSS
			$search = [
				'import directives' => '/^\\s*@import\\s[^;]+;/misU',
				'remaining media enclosures' => '/^\\s*@media\\s[^{]+{(.*)}\\s*}\\s/misU',
			];

			$cleanedCss = preg_replace($search, '', $cssForAllowedMediaTypes);

			return ['css' => $cleanedCss, 'media' => $media];
		}

		private function createXmlDocument()
		{
			$xmlDocument = new DOMDocument;
			$xmlDocument->encoding = 'UTF-8';
			$xmlDocument->strictErrorChecking = false;
			//$xmlDocument->formatOutput = true;
			$libXmlState = libxml_use_internal_errors(true);
			$xmlDocument->loadHTML($this->getUnifiedHtml());
			libxml_clear_errors();
			libxml_use_internal_errors($libXmlState);
			$xmlDocument->normalizeDocument();

			return $xmlDocument;
		}

		private function getUnifiedHtml()
		{
			$htmlWithoutUnprocessableTags = $this->removeUnprocessableTags($this->html);
			$htmlWithDocumentType = $this->ensureDocumentType($htmlWithoutUnprocessableTags);

			return $this->addContentTypeMetaTag($htmlWithDocumentType);
		}

		private function removeUnprocessableTags($html)
		{
			if (empty($this->unprocessableHtmlTags)) {
				return $html;
			}

			$unprocessableHtmlTags = implode('|', $this->unprocessableHtmlTags);

			return preg_replace(
				'/<\\/?(' . $unprocessableHtmlTags . ')[^>]*>/i',
				'',
				$html
			);
		}

		private function ensureDocumentType($html)
		{
			$hasDocumentType = stripos($html, '<!DOCTYPE') !== false;
			if ($hasDocumentType) {
				return $html;
			}

			return self::DEFAULT_DOCUMENT_TYPE . $html;
		}

		private function addContentTypeMetaTag($html)
		{
			return $html;
		}

		private function sortBySelectorPrecedence(array $a, array $b)
		{
			$precedenceA = $this->getCssSelectorPrecedence($a['selector']);
			$precedenceB = $this->getCssSelectorPrecedence($b['selector']);

			// We want these sorted in ascending order so selectors with lesser precedence get processed first and
			// selectors with greater precedence get sorted last.
			$precedenceForEquals = ($a['line'] < $b['line'] ? -1 : 1);
			$precedenceForNotEquals = ($precedenceA < $precedenceB ? -1 : 1);
			return ($precedenceA === $precedenceB) ? $precedenceForEquals : $precedenceForNotEquals;
		}

		private function getCssSelectorPrecedence($selector)
		{
			$selectorKey = md5($selector);
			if (!isset($this->caches[self::CACHE_KEY_SELECTOR][$selectorKey])) {
				$precedence = 0;
				$value = 100;
				// ids: worth 100, classes: worth 10, elements: worth 1
				$search = ['\\#','\\.',''];

				foreach ($search as $s) {
					if (trim($selector) === '') {
						break;
					}
					$number = 0;
					$selector = preg_replace('/' . $s . '\\w+/', '', $selector, -1, $number);
					$precedence += ($value * $number);
					$value /= 10;
				}
				$this->caches[self::CACHE_KEY_SELECTOR][$selectorKey] = $precedence;
			}

			return $this->caches[self::CACHE_KEY_SELECTOR][$selectorKey];
		}

		private function translateCssToXpath($cssSelector)
		{
			$paddedSelector = ' ' . $cssSelector . ' ';
			$lowercasePaddedSelector = preg_replace_callback(
				'/\\s+\\w+\\s+/',
				function (array $matches) {
					return strtolower($matches[0]);
				},
				$paddedSelector
			);
			$trimmedLowercaseSelector = trim($lowercasePaddedSelector);
			$xPathKey = md5($trimmedLowercaseSelector);
			if (!isset($this->caches[self::CACHE_KEY_XPATH][$xPathKey])) {
				$roughXpath = '//' . preg_replace(
					array_keys($this->xPathRules),
					$this->xPathRules,
					$trimmedLowercaseSelector
				);
				$xPathWithIdAttributeMatchers = preg_replace_callback(
					self::ID_ATTRIBUTE_MATCHER,
					[$this, 'matchIdAttributes'],
					$roughXpath
				);
				$xPathWithIdAttributeAndClassMatchers = preg_replace_callback(
					self::CLASS_ATTRIBUTE_MATCHER,
					[$this, 'matchClassAttributes'],
					$xPathWithIdAttributeMatchers
				);

				// Advanced selectors are going to require a bit more advanced emogrification.
				// When we required PHP 5.3, we could do this with closures.
				$xPathWithIdAttributeAndClassMatchers = preg_replace_callback(
					'/([^\\/]+):nth-child\\(\\s*(odd|even|[+\\-]?\\d|[+\\-]?\\d?n(\\s*[+\\-]\\s*\\d)?)\\s*\\)/i',
					[$this, 'translateNthChild'],
					$xPathWithIdAttributeAndClassMatchers
				);
				$finalXpath = preg_replace_callback(
					'/([^\\/]+):nth-of-type\\(\s*(odd|even|[+\\-]?\\d|[+\\-]?\\d?n(\\s*[+\\-]\\s*\\d)?)\\s*\\)/i',
					[$this, 'translateNthOfType'],
					$xPathWithIdAttributeAndClassMatchers
				);

				$this->caches[self::CACHE_KEY_SELECTOR][$xPathKey] = $finalXpath;
			}
			return $this->caches[self::CACHE_KEY_SELECTOR][$xPathKey];
		}

		private function matchIdAttributes(array $match)
		{
			return ($match[1] !== '' ? $match[1] : '*') . '[@id="' . $match[2] . '"]';
		}

		private function matchClassAttributes(array $match)
		{
			return ($match[1] !== '' ? $match[1] : '*') . '[contains(concat(" ",@class," "),concat(" ","' .
				implode(
					'"," "))][contains(concat(" ",@class," "),concat(" ","',
					explode('.', substr($match[2], 1))
				) . '"," "))]';
		}

		private function translateNthChild(array $match)
		{
			$parseResult = $this->parseNth($match);

			if (isset($parseResult[self::MULTIPLIER])) {
				if ($parseResult[self::MULTIPLIER] < 0) {
					$parseResult[self::MULTIPLIER] = abs($parseResult[self::MULTIPLIER]);
					$xPathExpression = sprintf(
						'*[(last() - position()) mod %u = %u]/self::%s',
						$parseResult[self::MULTIPLIER],
						$parseResult[self::INDEX],
						$match[1]
					);
				} else {
					$xPathExpression = sprintf(
						'*[position() mod %u = %u]/self::%s',
						$parseResult[self::MULTIPLIER],
						$parseResult[self::INDEX],
						$match[1]
					);
				}
			} else {
				$xPathExpression = sprintf('*[%u]/self::%s', $parseResult[self::INDEX], $match[1]);
			}

			return $xPathExpression;
		}

		private function translateNthOfType(array $match)
		{
			$parseResult = $this->parseNth($match);

			if (isset($parseResult[self::MULTIPLIER])) {
				if ($parseResult[self::MULTIPLIER] < 0) {
					$parseResult[self::MULTIPLIER] = abs($parseResult[self::MULTIPLIER]);
					$xPathExpression = sprintf(
						'%s[(last() - position()) mod %u = %u]',
						$match[1],
						$parseResult[self::MULTIPLIER],
						$parseResult[self::INDEX]
					);
				} else {
					$xPathExpression = sprintf(
						'%s[position() mod %u = %u]',
						$match[1],
						$parseResult[self::MULTIPLIER],
						$parseResult[self::INDEX]
					);
				}
			} else {
				$xPathExpression = sprintf('%s[%u]', $match[1], $parseResult[self::INDEX]);
			}

			return $xPathExpression;
		}

		private function parseNth(array $match)
		{
			if (in_array(strtolower($match[2]), ['even','odd'], true)) {
				// we have "even" or "odd"
				$index = strtolower($match[2]) === 'even' ? 0 : 1;
				return [self::MULTIPLIER => 2, self::INDEX => $index];
			}
			if (stripos($match[2], 'n') === false) {
				// if there is a multiplier
				$index = (int) str_replace(' ', '', $match[2]);
				return [self::INDEX => $index];
			}

			if (isset($match[3])) {
				$multipleTerm = str_replace($match[3], '', $match[2]);
				$index = (int) str_replace(' ', '', $match[3]);
			} else {
				$multipleTerm = $match[2];
				$index = 0;
			}

			$multiplier = str_ireplace('n', '', $multipleTerm);

			if ($multiplier === '') {
				$multiplier = 1;
			} elseif ($multiplier === '0') {
				return [self::INDEX => $index];
			} else {
				$multiplier = (int) $multiplier;
			}

			while ($index < 0) {
				$index += abs($multiplier);
			}

			return [self::MULTIPLIER => $multiplier, self::INDEX => $index];
		}

		private function parseCssDeclarationsBlock($cssDeclarationsBlock)
		{
			if (isset($this->caches[self::CACHE_KEY_CSS_DECLARATIONS_BLOCK][$cssDeclarationsBlock])) {
				return $this->caches[self::CACHE_KEY_CSS_DECLARATIONS_BLOCK][$cssDeclarationsBlock];
			}

			$properties = [];
			$declarations = preg_split('/;(?!base64|charset)/', $cssDeclarationsBlock);

			foreach ($declarations as $declaration) {
				$matches = [];
				if (!preg_match('/^([A-Za-z\\-]+)\\s*:\\s*(.+)$/', trim($declaration), $matches)) {
					continue;
				}

				$propertyName = strtolower($matches[1]);
				$propertyValue = $matches[2];
				$properties[$propertyName] = $propertyValue;
			}
			$this->caches[self::CACHE_KEY_CSS_DECLARATIONS_BLOCK][$cssDeclarationsBlock] = $properties;

			return $properties;
		}

		private function getNodesToExclude(\DOMXPath $xPath)
		{
			$excludedNodes = [];
			foreach (array_keys($this->excludedSelectors) as $selectorToExclude) {
				foreach ($xPath->query($this->translateCssToXpath($selectorToExclude)) as $node) {
					$excludedNodes[] = $node;
				}
			}

			return $excludedNodes;
		}
	}

?>