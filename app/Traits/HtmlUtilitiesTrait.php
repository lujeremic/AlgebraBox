<?php

namespace App\Traits;

trait HtmlUtilitiesTrait {

	public static function createBreadCrumb($uriRelativePath) {
		$pathSlugs = explode('/', rtrim(ltrim($uriRelativePath, '/'), '/'));
		$numOfPathSlugs = count($pathSlugs);
		$numOfPathSlugsOffset = $numOfPathSlugs - 1;
		$breadCrumb = array();
		$breadCrumb['number_of_slugs'] = $numOfPathSlugs;
		if ($numOfPathSlugs > 1) {
			// last/landing page doesn't need to have link so we are skiping last value from array
			for ($i = 0; $i < $numOfPathSlugsOffset; $i++) {
				$breadCrumb['breadcrumb_slugs'][$i]['name'] = urldecode($pathSlugs[$i]);
				if (isset($breadCrumb['breadcrumb_slugs'][$i - 1]['path'])) {
					$breadCrumb['breadcrumb_slugs'][$i]['path'] = $breadCrumb['breadcrumb_slugs'][$i - 1]['path'] . '/' . $pathSlugs[$i];
				} else
					$breadCrumb['breadcrumb_slugs'][$i]['path'] = '/' . $pathSlugs[$i];
				$breadCrumb['breadcrumb_slugs'][$i]['active'] = '';
			}
			$breadCrumb['breadcrumb_slugs'][$numOfPathSlugsOffset]['name'] = urldecode($pathSlugs[$numOfPathSlugsOffset]);
			$breadCrumb['breadcrumb_slugs'][$numOfPathSlugsOffset]['path'] = '/' . $pathSlugs[$numOfPathSlugsOffset];
			$breadCrumb['breadcrumb_slugs'][$numOfPathSlugsOffset]['active'] = 'active';
		} else {
			$breadCrumb['breadcrumb_slugs'][0]['name'] = urldecode($pathSlugs[0]);
			$breadCrumb['breadcrumb_slugs'][0]['active'] = 'active';
		}
		return $breadCrumb;
	}

}
