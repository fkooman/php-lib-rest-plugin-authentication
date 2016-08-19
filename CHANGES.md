# Release History

## 3.0.0 (2016-08-19)
- update `fkooman/rest`

## 2.0.1 (2016-01-21)
- add `DummyAuthentication` to facilitate testing

## 2.0.0 (2015-11-19)
- major refactoring of the code
- major API change for authentication plugins
- API change for applications: they can no longer directly use the 
  authentication plugins, but MUST use this library
- no longer possible to support two authentication plugins on one endpoint, it 
  was not used anywhere. Technically this is an API change, so bump major 
  version

## 1.0.2 (2015-10-13)
- fix small bug when mentioned friendly name of authentication plugin 
  does not exist
- fix tests with new `fkooman/http`

## 1.0.1 (2015-09-12)
- fix unit tests running on CentOS 6 
- require only PHP >=5.3.0

## 1.0.0
- initial release
