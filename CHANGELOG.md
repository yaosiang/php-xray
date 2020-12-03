# Changelog

## [1.3.0] - 2020-12-03
### Added
- Helper for building sampler.
- Helper for running callback inside segment.

## [1.2.0] - 2019-01-24
### Changed
- Refactored ```getCurrentSegment()``` to drastically reduce the cost of searching large collections of segments. This may change behavior in applications which begin earlier segments after later segments (e.g with asynchronous workloads). This can be solved by adding segments to a parent at the point ```begin()``` is called rather than before.

## [1.1.0] - 2018-07-13
### Added
- A service's version can be recorded on traces
- A client's IP address and user agent can be recorded on traces
- User identifiers can be recorded on traces
- Annotations and metadata can be added to subsegments and traces

## [1.0.1] - 2018-06-01
### Added
- Traces larger than a single UDP packet will now be fragmented rather than dropped

## [1.0.0] - 2018-05-19
### Added
- Initial release
