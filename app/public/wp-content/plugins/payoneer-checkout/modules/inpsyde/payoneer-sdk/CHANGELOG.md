# Change log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [next-version] - yyyy-mm-dd

## [2.8.0] - 2023-09-29
### Added
- ProcessingModel added to the List.

## [2.7.0] - 2023-09-27
### Added
- Products added to List.

## [2.6.0] - 2023-07-24
### Added
- TaxCode field to the Product entity.

## [2.5.0] - 2023-05-11
### Added
- Custom LIST TTL can be set via CreateListCommand.

## [2.4.0] - 2023-03-27
### Changed
- Style.lang is now optional.

## [2.3.0] - 2023-03-22
### Added
- `withApiClient()` method for commands.

## [2.2.0] - 2023-03-16
### Changed
- Style.lang is optional.

## [2.1.0] - 2023-02-28
### Changed
- Shipping address is optional.

## [2.0.0] - 2023-02-07
### Added
- Tax and Net amount to the Payment entity.

## [1.2.0] - 2023-02-28
### Changed
- Shipping address is optional.

## [1.1.0] - 2023-01-09
### Added
- Default country is added to the `CreateListCommand` and `UpdateListCommand`.
- For default country value, `US` is used.

## [1.0.0] - 2022-11-02
### Changed
- Package considered stable now.

## [0.1.0-alpha28] - 2022-11-02
### Fixed
- Notices when preparing log message because of missing array elements.

## [0.1.0-alpha27] - 2022-11-01
### Added
- CreateListCommand and UpdateListCommand now contain System object.

## [0.1.0-alpha26] - 2022-10-28
### Fixed
- `Customer.Name` is now redacted in exception handling.

## [0.1.0-alpha25] - 2022-10-07
### Added
- `Customer` entity now contains `Name`.

## [0.1.0-alpha24] - 2022-09-30
### Fixed
- Added missed interface parameter.

## [0.1.0-alpha23] - 2022-09-30
### Added
- `allowDelete` flag added to the `LIST` entity.
- `password` added to the `Registration` entity.

### Changed
- `payment` field made optional for `LIST` creation with `UPDATE` operation type.
- Arguments order is changed for the `LIST` creation.

## [0.1.0-alpha22] - 2022-09-02
### Added
- Registration entity added to the Customer entity.

## [0.1.0-alpha21] - 2022-08-23
### Added
- TaxAmount and NetAmount to product.

## [0.1.0-alpha20] - 2022-07-12
### Fixed
- Type errors in Command interfaces.

## [0.1.0-alpha19] - 2022-07-12
### Added
- CreateListCommand.

## [0.1.0-alpha18] - 2022-07-06
### Changed
- Redact customer data before throwing exceptions.

## [0.1.0-alpha17] - 2022-07-06
### Added
- Include request body if API itself fails.

## [0.1.0-alpha16] - 2022-07-06
### Added
- Include request payload in exception message when LIST fails.

## [0.1.0-alpha15] - 2022-07-04
### Added
- System entity containing information about merchant's system.

## [0.1.0-alpha14] - 2022-06-02
### Added
- Exception for the `TRY_ANOTHER_ACCOUNT` interaction code.

## [0.1.0-alpha13] - 2022-04-20
### Added
- Support for products list in `UPDATE` and `PAYOUT` requests.

## [0.1.0-alpha12] - 2022-04-12
### Changed
- Changed order of constructor arguments in `Callback` to match `CallbackFactory` #21

## [0.1.0-alpha11] - 2022-04-11
### Fixed
- Missed division field in the request body in #21

## [0.1.0-alpha10] - 2022-04-08
### Fixed
- Wrong exception type in #20

## [0.1.0-alpha9] - 2022-04-08
### Added
- Add division field support in #18
- Introduce postal code in #19
### Changed
- Refactor commands in #15

## [0.1.0-alpha8] - 2022-04-01
### Added
- Implement parameters in redirect entity in #17

## [0.1.0-alpha7] - 2022-04-01
### Added
- Implement redirect entities in #16

## [0.1.0-alpha6] - 2022-03-30
### Added
- Entities and entity fields required for risk engine.

## [0.1.0-alpha5] - 2022-02-23
### Added
- Status entity with Factory, Serializer and Deserializer.

## [0.1.0-alpha4] - 2022-01-31
### Added
- Style entity with Factory, Serializer and Deserializer.
- Support for LIST session language (with Style object).

### Fixed
- Payout command path template.
- Payout request body serialization.

### Changed
- List now includes optional Style object.

## [0.1.0-alpha3] - 2022-01-11
### Added
- Header entity with Factory, Serializer, and Deserializer.
### Changed
- Use serializers to prepare the UPDATE LIST request body instead of manually building it.
### Fixed
- Missing fields in the UPDATE LIST request body.
- Format of Notification Headers in Callback when sending LIST requests.

## [0.1.0-alpha2] - 2021-12-22
### Added
- Integration type when creating a new LIST session.
### Changed
- Use serializers to prepare LIST request body instad of manually building it.
### Fixed
- Missing fields in the LIST request body.

## [0.1.0-alpha1] - 2021-11-18
Initial version.
