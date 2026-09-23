<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use LogicException;
use stdClass;

class SilexClient
{
    /**
     * @return array<int, string>
     */
    public function listWebsites(): array
    {
        $websites = $this->request()
            ->get('/api/website', [
                'connectorId' => config('silex.connector_id'),
            ])
            ->throw()
            ->json();

        if (! is_array($websites) || ! array_is_list($websites)) {
            throw new LogicException('Silex website list must be a JSON array.');
        }

        $websiteIds = [];

        foreach ($websites as $website) {
            if (! is_array($website) || ! isset($website['websiteId']) || ! is_string($website['websiteId']) || $website['websiteId'] === '') {
                throw new LogicException('Silex website list contains an invalid websiteId.');
            }

            $websiteIds[] = $website['websiteId'];
        }

        return array_values(array_unique($websiteIds));
    }

    public function createWebsiteAndDetermineId(string $name): string
    {
        $websiteIdsBefore = $this->listWebsites();

        $this->request()
            ->put('/api/website?connectorId='.rawurlencode((string) config('silex.connector_id')), [
                'name' => $name,
                'connectorUserSettings' => new stdClass,
            ])
            ->throw();

        $websiteIdsAfter = $this->listWebsites();
        $newWebsiteIds = array_values(array_diff($websiteIdsAfter, $websiteIdsBefore));

        if (count($newWebsiteIds) !== 1) {
            throw new LogicException('Silex website creation did not yield exactly one new websiteId.');
        }

        return $newWebsiteIds[0];
    }

    public function buildEditorUrl(string $websiteId, string $pageId, string $pageName): string
    {
        return rtrim((string) config('silex.editor_url'), '/').'/?'.http_build_query([
            'id' => $websiteId,
            'lang' => config('silex.lang'),
            'connectorId' => config('silex.connector_id'),
            'pageId' => $pageId,
            'pageName' => $pageName,
        ], '', '&', PHP_QUERY_RFC3986);
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('silex.api_url'), '/'))
            ->connectTimeout(3)
            ->timeout(10);
    }
}
