# Veriscope & IVMS 101

IVMS 101 provides a standard data model for transmitting required originator and beneficiary information. It is designed for use primarily by VASPs, other obliged entities undertaking virtual asset services, and travel rule solution providers.

Before continuing please review the document found here: [https://intervasp.org/](https://intervasp.org/)
Note: definitions for fields are described in the document.

IVMS provides a standard schema for data that represent VASPs and users on their respective platforms.

The standard represents each VASP and user as either Originator or Beneficiary.
For example:
```
{
  "originator": {...},
  "beneficiary": {... },
  "originatingVASP": {...},
  "beneficiaryVASP": {...}
}
```
In the case where your VASP is the exchange sending the crypto transaction (in the case of a withdrawal), your exchange and your user is the originator.  If your exchange is receiving the crypto transaction (in the case of a deposit), your exchange and your user is the beneficiary.

Since both originating and beneficiary VASPs are entities, each object in the schema is represented by a legalPerson.
This object in the schema should include name, geographicAddress, nationalIdentification, customerIdentification.

Here is an example of an originating VASP.

```
"originatingVASP": {
    "legalPerson": {
      "name": {
        "nameIdentifier": [
          {
            "legalPersonName": "VASP A",
            "legalPersonNameIdentifierType": "LEGL"
          }
        ]
      },
      "geographicAddress": [
        {
          "addressType": "GEOG",
          "streetName": "Potential Street",
          "buildingNumber": "123",
          "buildingName": "Suite 2B",
          "postcode": "10001",
          "townName": "New York City",
          "countrySubDivision": "NY",
          "country": "US"
        }
      ],
      "nationalIdentification": {
        "nationalIdentifier": "506700T7Z685VUOZL877",
        "nationalIdentifierType": "LEIX"
      },
      "customerIdentification": "0xE844664fAb49B9115F1f43eefaB09F2d1D1852e4"
    }
  },

```

Note: customerIdentification in this case is the TrustAnchor Account.


Again, depending on whether you are the originating or beneficiary VASP, you are required to complete the appropriate element in the schema:

```
"originatingVASP": {...},
"beneficiaryVASP": {...}
```

For users on your platform, they can be categorized as either naturalPerson or legalPerson depending if they are an individual or entity.

The following examples show an individual as the originator and entity as the beneficiary.
```
  "originator": {
    "originatorPersons": [
      {
        "naturalPerson": {
          "name": {
            "nameIdentifier": [
              {
                "primaryIdentifier": "Satoshi",
                "secondaryIdentifier": "Nakamoto",
                "nameIdentifierType": "LEGL"
              }
            ]
          },
          "geographicAddress": [
            {
              "addressType": "GEOG",
              "streetName": "Potential Street",
              "buildingNumber": "123",
              "buildingName": "Cheese Hut",
              "postcode": "91361",
              "townName": "Thousand Oaks",
              "countrySubDivision": "California",
              "country": "US"
            }
          ],
          "nationalIdentification": {
            "nationalIdentifier": "864 118 996",
            "nationalIdentifierType": "RAID",
            "countryOfIssue": "US",
            "registrationAuthority": "RA000628"
          },
          "customerIdentification": "0xA3a8C1C840A8C2049472065b2664E01E0e8A8b67",
          "dateAndPlaceOfBirth": {
            "dateOfBirth": "1986-11-21",
            "placeOfBirth": "New York City"
          },
          "countryOfResidence": "US"
        }
      }
    ],
    "accountNumber": [
      "1HB5XMLmzFVj8ALj6mfBsbifRoD4miY36v"
    ]
  },
```

```
"beneficiary": {
    "beneficiaryPersons": [
      {
        "legalPerson": {
          "name": {
            "nameIdentifier": [
              {
                "legalPersonName": "Paycase Inc",
                "legalPersonNameIdentifierType": "LEGL"
              }
            ]
          },
          "geographicAddress": [
            {
              "addressType": "GEOG",
              "streetName": "Potential Street",
              "buildingNumber": "123",
              "buildingName": "Townhouse",
              "postcode": "M4B 1B3",
              "townName": "Toronto",
              "countrySubDivision": "Ontario",
              "country": "CA"
            }
          ],
          "nationalIdentification": {
            "nationalIdentifier": "024181096",
            "nationalIdentifierType": "LEIX",
            "countryOfIssue": "CA",
            "registrationAuthority": "RA000589"
          },
          "customerIdentification": "0x3E9181d09E56AdEF3bbc8BAb664Ce6B268Bf6e62",
          "countryOfRegistration": "CA"
        }
      }
    ],
    "accountNumber": [
      "1BVMFfPXJy2TY1x6wm8gow3N5Amw4Etm5h"
    ]
  },
```


Note: the originator includes an accountNumber which is the withdrawal crypto address.

customerIdentification is the user id (shyft ID) of the user in the attestation.

accountNumber for the beneficiary is the deposit address for the user on the beneficiary VASP.

Veriscope includes a json schema validator that will confirm you have completed the json whether you are the originator and beneficiary VASP prior to sharing the information with your counterparty.

It is important that you note how to translate your entity and users information into the schema accurately.

There are a number of open json schema validators that you can use to verify your json file validates against the schema.

[https://www.jsonschemavalidator.net/](https://www.jsonschemavalidator.net/)

By pasting the schema in the left textview, you can use it to verify your json file in the right textview.

Provided here is the schema: json-schema.json

And an example full json: full-example.json

Pasting each in the validator should produce a message:
No errors found. JSON validates against the schema

#  POST a JSON file with curl  to test  IVMS101 validator

Use the syntax (`curl -X POST -H "Content-Type: application/json" -d @FILENAME DESTINATION`) to specify a file instead.

You can post a json file with `curl` like so:

```
curl -X POST -H "Content-Type: application/json" -d @complete-example.json http://localhost:8000/api/v1/ivms101-validate/complete
```
response
```
Valid IVMS101
```
Testing as Originator

```
curl -X POST -H "Content-Type: application/json" -d @originator-example.json http://localhost:8000/api/v1/ivms101-validate/originator
```
response
```
Valid IVMS101
```
Testing as Beneficiary
```
curl -X POST -H "Content-Type: application/json" -d @beneficiary-example.json http://localhost:8000/api/v1/ivms101-validate/beneficiary
```
response
```
Valid IVMS101
```

Example of malformed json
```
curl -X POST -H "Content-Type: application/json" -d @malformed-full-example.json http://localhost:8000/api/v1/ivms101-validate/complete
```
response
```
{
    "message": "JSON Schema Validation Error",
    "fields": {
        "originator": [
            {
                "schema": {
                    "id": null,
                    "base": "file:\/\/complete-ivms101-v1.json#",
                    "root": "file:\/\/complete-ivms101-v1.json#",
                    "draft": "2020-12",
                    "path": "#\/definitions\/Originator",
                    "contents": {
                        "title": "Originator",
                        "type": "object",
                        "$pragma": {
                            "cast": "object"
                        },
                        "properties": {
                            "originatorPersons": {
                                "type": "array",
                                "items": {
                                    "$ref": "#\/definitions\/Person"
                                }
                            },
                            "accountNumber": {
                                "type": "array",
                                "items": {
                                    "type": "string",
                                    "pattern": "^[a-zA-Z0-9' ]{1,100}$"
                                }
                            }
                        },
                        "required": [
                            "originatorPersons",
                            "accountNumber"
                        ]
                    }
                },
                "error": {
                    "keyword": "required",
                    "args": {
                        "missing": [
                            "originatorPersons"
                        ]
                    },
                    "message": "The required properties ({missing}) are missing",
                    "formattedMessage": "The required properties (originatorPersons) are missing"
                },
                "data": {
                    "type": "object",
                    "value": {
                        "originatorPersons1": [
                            {
                                "naturalPerson": {
                                    "name": {
                                        "nameIdentifier": [
                                            {
                                                "primaryIdentifier": "Satoshi",
                                                "secondaryIdentifier": "Nakamoto",
                                                "nameIdentifierType": "LEGL"
                                            }
                                        ]
                                    },
                                    "geographicAddress": [
                                        {
                                            "addressType": "GEOG",
                                            "streetName": "Potential Street",
                                            "buildingNumber": "123",
                                            "buildingName": "Cheese Hut",
                                            "postcode": "91361",
                                            "townName": "Thousand Oaks",
                                            "countrySubDivision": "California",
                                            "country": "US"
                                        }
                                    ],
                                    "nationalIdentification": {
                                        "nationalIdentifier": "864 118 996",
                                        "nationalIdentifierType": "RAID",
                                        "countryOfIssue": "US",
                                        "registrationAuthority": "RA000628"
                                    },
                                    "customerIdentification": "0xA3a8C1C840A8C2049472065b2664E01E0e8A8b67",
                                    "dateAndPlaceOfBirth": {
                                        "dateOfBirth": "1986-11-21",
                                        "placeOfBirth": "New York City"
                                    },
                                    "countryOfResidence": "US"
                                }
                            }
                        ],
                        "accountNumber": [
                            "1HB5XMLmzFVj8ALj6mfBsbifRoD4miY36v"
                        ]
                    },
                    "fullPath": [
                        "originator"
                    ]
                }
            }
        ]
    },
    "code": "validation_failed"
}
```