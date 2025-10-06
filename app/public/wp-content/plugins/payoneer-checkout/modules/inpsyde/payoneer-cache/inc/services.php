<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Cache;

use Syde\Vendor\Dhii\Services\Factories\Alias;
use Syde\Vendor\Dhii\Services\Factories\Constructor;
use Syde\Vendor\Dhii\Services\Factories\Value;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Filesystem\StreamingFileSaver;
return static function () : array {
    return [
        'cache.file_cache.base_dir_factory.segment_separator' => new Value('.'),
        'cache.file_cache.hasher' => new Alias('cache.hasher'),
        'cache.file_cache.base_dir_factory.hasher' => new Alias('cache.file_cache.hasher'),
        'cache.file_cache.string_stream.factory' => new Alias('cache.string_stream.factory'),
        'cache.file_cache.base_dir_factory.string_stream.factory' => new Alias('cache.file_cache.string_stream.factory'),
        'cache.file_cache.base_dir_path_resolver.factory' => new Constructor(BaseDirFilePathResolverFactory::class, ['cache.file_cache.base_dir_factory.hasher', 'cache.file_cache.base_dir_factory.segment_separator', 'cache.file_cache.base_dir_factory.string_stream.factory']),
        'cache.file_cache.file_exists_file_validator' => new Constructor(FileExistsFileValidator::class, []),
        'cache.file_cache.file_validator' => new Constructor(FileExistsFileValidator::class, []),
        'cache.file_cache.stream.factory' => new Alias('cache.file_stream.factory'),
        'cache.file_cache.max_buffer_size' => new Value(1 * 1000),
        // 1MB
        'cache.file_cache.streaming_file_saver.stream.factory' => new Alias('cache.file_cache.stream.factory'),
        'cache.file_cache.streaming_file_saver.max_buffer_size' => new Alias('cache.file_cache.max_buffer_size'),
        'cache.file_cache.streaming_file_saver' => new Constructor(StreamingFileSaver::class, ['cache.file_cache.streaming_file_saver.stream.factory', 'cache.file_cache.streaming_file_saver.max_buffer_size']),
        'cache.file_cache.file_saver' => new Alias('cache.file_cache.streaming_file_saver'),
        'cache.base_dir_file_cache.factory.path_resolver.factory' => new Alias('cache.file_cache.base_dir_path_resolver.factory'),
        'cache.base_dir_file_cache.factory.file_validator' => new Alias('cache.file_cache.file_validator'),
        'cache.base_dir_file_cache.factory.file_saver' => new Alias('cache.file_cache.file_saver'),
        'cache.base_dir_file_cache.factory' => new Constructor(BaseDirFileCacheFactory::class, ['cache.base_dir_file_cache.factory.path_resolver.factory', 'cache.base_dir_file_cache.factory.file_validator', 'cache.base_dir_file_cache.factory.file_saver']),
    ];
};
