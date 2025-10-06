<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Template;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\RegexTrait;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\StringStreamFactoryInterface;
use Syde\Vendor\Psr\Http\Message\StreamInterface;
use RangeException;
use RuntimeException;
use Stringable;
/**
 * A template that works by replacing placeholders.
 *
 * @psalm-type MapOfScalars = array<string, Stringable|scalar|array<string, Stringable|scalar>>
 * @psalm-type Options = array<string, Stringable|scalar|MapOfScalars>
 * @psalm-type Context = array<string, Stringable|scalar|Options>
 */
class StreamingPlaceholderTemplate implements TemplateInterface
{
    use RegexTrait;
    /**
     * @var StreamInterface
     */
    protected $stream;
    /**
     * @var StringStreamFactoryInterface
     */
    protected $streamFactory;
    /**
     * @var string
     */
    protected $tokenStart;
    /**
     * @var string
     */
    protected $tokenEnd;
    /**
     * @var string|null
     */
    protected $default;
    public function __construct(StreamInterface $stream, StringStreamFactoryInterface $streamFactory, string $tokenStart, string $tokenEnd, ?string $default)
    {
        $this->stream = $stream;
        $this->streamFactory = $streamFactory;
        $this->tokenStart = $tokenStart;
        $this->tokenEnd = $tokenEnd;
        $this->default = $default;
    }
    /**
     * @inheritDoc
     *
     * @throws RuntimeException If problem rendering.
     */
    public function render(array $context) : StreamInterface
    {
        $template = (string) $this->stream;
        $output = $this->replaceTokens($template, $context, $this->tokenStart, $this->tokenEnd, $this->default);
        $output = wp_unslash($output);
        $output = $this->createStreamFromString($output);
        return $output;
    }
    /**
     * Replaces tokens in an input string with values from map.
     *
     * @param string $input The input string.
     * @param Context $source The map of keys to their values.
     * @param string $tokenStart The string that designates the start of a token.
     * @param string $tokenEnd The string that designates the end of a token.
     * @param string|null $default The default token value, if not null.
     *                             Otherwise, causes a missing source mapping to throw.
     *
     * @return string The input string with tokens replaced.
     *
     * @throws RangeException If one of the tokens in `$input` does not have a corresponding value
     *                        in $source. Requires `$default` to be `null`.
     * @throws RuntimeException If problem replacing.
     */
    protected function replaceTokens(string $input, array $source, string $tokenStart, string $tokenEnd, ?string $default) : string
    {
        $delim = '/';
        $tokenStart = preg_quote($tokenStart, $delim);
        $tokenEnd = preg_quote($tokenEnd, $delim);
        $regex = "{$delim}{$tokenStart}(.*?){$tokenEnd}{$delim}";
        $matches = [];
        $this->pregMatchAll($regex, $input, $matches);
        /** @psalm-var list<list<string>> $matches */
        $output = $input;
        foreach ($matches[0] as $i => $token) {
            $key = $matches[1][$i];
            $key = $this->normalizeTokenKey($key);
            $value = $this->getSourceValue($source, $key, $default);
            assert(is_string($value));
            /** @psalm-suppress RedundantConditionGivenDocblockType */
            assert(is_string($token));
            $delim = '!';
            $tokenExpr = $delim . preg_quote($token, $delim) . $delim;
            $output = $this->pregReplace($tokenExpr, $value, $output);
        }
        return $output;
    }
    /**
     * Normalizes a token key.
     *
     * @param string $key The key to normalize.
     *
     * @return string The normalized key.
     * @throws RangeException If key cannot be normalized.
     * @throws RuntimeException If problem normalizing.
     */
    protected function normalizeTokenKey(string $key) : string
    {
        $key = trim($key);
        if ($this->pregMatch('![\\s]!', $key)) {
            throw new RangeException(sprintf('Key "%1$s" is invalid: cannot  contain whitespace', $key));
        }
        return $key;
    }
    /**
     * Creates a stream that exposes the specified string.
     *
     * @param string $string The string to create a stream from.
     *
     * @return StreamInterface The new stream.
     *
     * @throws RuntimeException If problem creating.
     */
    protected function createStreamFromString(string $string) : StreamInterface
    {
        return $this->streamFactory->createStreamFromString($string);
    }
    /**
     * Retrieves a value from a map by key,
     *
     * @param array $source The value map.
     * @param string $key The value key.
     * @param string|null $default The default value to be used when a key is missing.
     *                             If `null`, will cause missing keys to throw.
     *
     * @return mixed The retrieved value.
     * @throws RangeException If a value is missing. Requires `$defult` to be `null`.
     * @throws RuntimeException If problem retrieving.
     *
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
     */
    protected function getSourceValue(array $source, string $key, ?string $default)
    {
        // phpcs:enable
        if (!array_key_exists($key, $source)) {
            if ($default === null) {
                throw new RangeException(sprintf('Key "%1$s" not found in source map', $key));
            }
            return $default;
        }
        return $source[$key];
    }
}
