<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Advertise;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdvertiseController extends Controller
{
    /**
     * Click tracking: log a click event + buffered daily counter, then
     * 302-redirect to the ad target link. Invalid/inactive ads go home.
     *
     * Route: GET ads/click/{id} (advertise_click)
     */
    public function index(Request $request, $id)
    {
        if (! Advertise::tableReady('advertises')) {
            return redirect('/');
        }

        $advertise = Advertise::find($id);

        if ($advertise === null || ! $advertise->isDisplayable()) {
            return redirect('/');
        }

        Advertise::recordEvent((int) $advertise->id, 'click', $request->ip(), $request->userAgent());
        Advertise::bufferStat((int) $advertise->id, 'click');

        $target = (string) ($advertise->getRawOriginal('url') ?? '');

        if (! Str::startsWith($target, ['http://', 'https://', '/'])) {
            return redirect('/');
        }

        return redirect()->away($target, 302);
    }

    /**
     * Impression tracking pixel (used as the VAST <Impression> URL).
     * Logs a view event + buffered daily counter and returns a 1x1 GIF.
     *
     * Route: GET ads/impression/{id} (advertise_impression)
     */
    public function impression(Request $request, $id)
    {
        if (Advertise::tableReady('advertises')) {
            $advertise = Advertise::find($id);

            if ($advertise !== null) {
                Advertise::recordEvent((int) $advertise->id, 'view', $request->ip(), $request->userAgent());
                Advertise::bufferStat((int) $advertise->id, 'view');
            }
        }

        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($gif, 200, [
            'Content-Type' => 'image/gif',
            'Content-Length' => (string) strlen($gif),
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Minimal VAST 4.x inline linear document for a video advertise.
     * Impression tracking hits ads/impression/{id} (advertise_events table)
     * and the click-through goes through ads/click/{id}.
     *
     * Route: GET ads/vast/{id} (advertise_vast)
     */
    public function vast($id)
    {
        if (! Advertise::tableReady('advertises')) {
            abort(404);
        }

        $advertise = Advertise::find($id);

        if ($advertise === null || ! $advertise->isVideo() || ! $advertise->isDisplayable()) {
            abort(404);
        }

        $mediaFile = $advertise->videoSource();

        if ($mediaFile === null) {
            abort(404);
        }

        $adId = (int) $advertise->id;
        $title = htmlspecialchars((string) $advertise->name, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $duration = $advertise->vastDuration();
        $impressionUrl = route('advertise_impression', ['id' => $adId]);
        $clickThroughUrl = route('advertise_click', ['id' => $adId]);
        $mimeType = Str::endsWith(strtolower(strtok($mediaFile, '?')), '.webm') ? 'video/webm' : 'video/mp4';

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<VAST version="4.2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="vast.xsd">
  <Ad id="sobhesahel-{$adId}" sequence="1">
    <InLine>
      <AdSystem version="1.0">SobheSahel</AdSystem>
      <AdTitle>{$title}</AdTitle>
      <Impression id="sobhesahel-imp-{$adId}"><![CDATA[{$impressionUrl}]]></Impression>
      <AdServingId>sobhesahel-{$adId}</AdServingId>
      <Creatives>
        <Creative id="creative-{$adId}" sequence="1">
          <Linear>
            <Duration>{$duration}</Duration>
            <MediaFiles>
              <MediaFile id="media-{$adId}" delivery="progressive" type="{$mimeType}" width="640" height="360"><![CDATA[{$mediaFile}]]></MediaFile>
            </MediaFiles>
            <VideoClicks>
              <ClickThrough id="click-{$adId}"><![CDATA[{$clickThroughUrl}]]></ClickThrough>
            </VideoClicks>
          </Linear>
        </Creative>
      </Creatives>
    </InLine>
  </Ad>
</VAST>
XML;

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
