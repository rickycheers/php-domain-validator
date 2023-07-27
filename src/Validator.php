<?php

namespace DomainValidity;

use DomainValidity\Host\Host;

class Validator
{
    public const VERSION = '0.1.0';

    /**
     * @param array<string> $publicSuffixList
     */
    public function __construct(
        protected array $publicSuffixList,
    ) {
    }

    public static function hostVersionedKey(string $key): string
    {
        return 'v' . self::VERSION . urlencode($key);
    }

    public function validate(string $host): Host
    {
        $host = new Host($host);

        $parts = $host->exploded();
        $host->tld($this->getTld($parts));

        return $host;
    }

    public function domain(string $host): string
    {
        $host = $this->validate($host);

        return strval($host->domain());
    }

    public function tld(string $host): string
    {
        $host = $this->validate($host);

        return strval($host->tld());
    }

    /**
     * @param array<string> $parts
     */
    protected function getTld(array $parts, ?string $tld = null): string
    {
        $current = end($parts) . ($tld ? ".{$tld}" : '');
        unset($parts[count($parts) - 1]);

        foreach ($this->publicSuffixList as $key => $item) {
            if (strpos($item, $current) !== false) {
                return $this->getTld($parts, $current);
            }
        }

        return strval($tld);
    }
}
