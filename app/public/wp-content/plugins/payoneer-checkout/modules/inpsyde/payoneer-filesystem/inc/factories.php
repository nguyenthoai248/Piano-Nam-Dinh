<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factories\Constructor;
use Syde\Vendor\Dhii\Services\Factories\Value;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\FileStreamFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\PrefixMatchingPathResolverFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\StreamingFileSaver;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\StringStreamFactory;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\UriFactory;
return static function () : array {
    return [
        'filesystem.file_stream.factory' => new Constructor(FileStreamFactory::class, []),
        'filesystem.string_stream.factory' => new Constructor(StringStreamFactory::class, []),
        'filesystem.uri.factory' => new Constructor(UriFactory::class, []),
        'filesystem.streaming_file_saver.max_buffer_size' => new Value(1 * 1000),
        // 1MB
        'filesystem.streaming_file_saver' => new Constructor(StreamingFileSaver::class, ['filesystem.file_stream.factory', 'filesystem.streaming_file_saver.max_buffer_size']),
        'filesystem.prefix_matching_path_resolver.factory' => new Constructor(PrefixMatchingPathResolverFactory::class, []),
    ];
};
