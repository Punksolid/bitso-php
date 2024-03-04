<?php

namespace BitsoAPI;

use JsonException;
use PHPUnit\Framework\MockObject\Exception;
use Tests\TestCase;

class PayoutsTest extends TestCase
{
    /**
     * @throws Exception
     * @throws JsonException
     */
    public function testWithdrawals()
    {
        $jsonWithdrawalsResponse = <<<'JSON'
{
    "success": true,
    "payload": [
        {
            "wid": "6cb5560edeafddc6e62a1b32355bc24b",
            "status": "complete",
            "created_at": "2023-03-07T19:47:33+00:00",
            "currency": "usd",
            "method": "usdc_trf",
            "method_name": "Circle Transfer",
            "amount": "1500.00",
            "asset": "usdc",
            "network": "circle",
            "protocol": "usdc_trf",
            "integration": "circle-api",
            "details": {
                "origin_id": "6ffbb40c5900c3ddd99dffff",
                "transactionHash": "2FVarUCJvJS21AAL8uqvSBAUrkJQfNZcYjC4V37yCydtCPDeunUavAKSFPLDrqtoKRRodLNjU9JMCXnDKiZag3Fd",
                "address": "*****ksqQcKF7KcDNnNuSmiQmdq7k9CG************",
                "addressTag": null,
                "chain": "SOL"
            }
        },
        {
            "wid": "99bb74cb53d2cda48d49c55434ce804d",
            "status": "complete",
            "created_at": "2023-02-21T19:00:24+00:00",
            "currency": "usd",
            "method": "usdc_trf",
            "method_name": "Circle Transfer",
            "amount": "1500.00",
            "asset": "usdc",
            "network": "circle",
            "protocol": "usdc_trf",
            "integration": "circle-api",
            "details": {
                "origin_id": "6ffbb40c5900c3ddd92dffff",
                "transactionHash": "5123eb30628e2b6c9b4bd019a2a713b667a03f12e474f7e08b3715b6a0464e94",
                "address": "*****7IF6LWK7P7MDEVSCWR7DPUWV3NY3DTQEVFL4NAT************",
                "addressTag": "memo",
                "chain": "xlm"
            }
        },
        {
            "wid": "69623449dc75d6d1ffd06a6a32fda15c",
            "status": "complete",
            "created_at": "2023-02-21T18:57:44+00:00",
            "currency": "usd",
            "method": "usdc_trf",
            "method_name": "Circle Transfer",
            "amount": "1500.00",
            "asset": "usdc",
            "network": "circle",
            "protocol": "usdc_trf",
            "integration": "circle-api",
            "details": {
                "origin_id": "4ffbb40c5900c3ddd92dffff",
                "transactionHash": "0x792112c2ed9524d087fd4aa1be00709baaababa81f4c5b6a0a1e49361fe95e9a",
                "address": "*****888A0050A4F8098b3C812E189************",
                "addressTag": null,
                "chain": "MATIC"
            }
        }
    ]
}
JSON;

        $clientMock = $this->getMockClient($jsonWithdrawalsResponse);
        $bitsoMock = $this->createMock(Bitso::class);

        $payouts = new Payouts($clientMock, $bitsoMock);
        $clientMock->expects($this->once())
            ->method('getData')
            ->willReturn(['data' => 'test']);

        $result = $payouts->withdrawals();

        $this->assertArrayHasKey('wid', $result[0]);
        $this->assertArrayHasKey('status', $result[0]);
        $this->assertArrayHasKey('created_at', $result[0]);
    }

    public function testWithdrawalMethods()
    {
        $jsonWithdrawalMethodsResponse = <<<JSON
{
  "success": true,
  "payload": [
    {
      "method": "bt",
      "integration": "bt",
      "sub_method": null,
      "name": "Bitso Transfer",
      "network_name": "Bitso Transfer",
      "network_description": "To other Bitso users, immediately without fees.",
      "required_fields": [
        "method",
        "amount",
        "currency"
      ],
      "optional_fields": [
        "notes",
        "refcode",
        "email",
        "phone",
        "emoji",
        "origin_id"
      ],
      "currency_configurations": [
        {
          "currency": "mxn",
          "legal_operating_entity": {
            "legal_operation_entity": "Nvio Pagos Mexico",
            "country_code": "MX"
          },
          "fee": {
            "amount": "0.00000000",
            "type": "fixed"
          },
          "limits": {
            "system_min": "0.00000000",
            "system_max": "36000.00000000",
            "tx_limit": "36000.00000000",
            "status": "upgradeable"
          },
          "status": {
            "type": "active",
            "description": "Ok"
          },
          "asset": "mxn"
        }
      ],
      "consumer_contacts_enabled": false,
      "method_description": "To other Bitso accounts.",
      "icon_config": {
        "path": "\/assets\/icon\/funding_methods\/",
        "name": "bt"
      },
      "tags": [
        {
          "title": "Zero-fee",
          "type": "success"
        },
        {
          "title": "Immediate",
          "type": "success"
        }
      ],
      "network": "bt",
      "protocol": "bt",
      "security": {
        "modes": [
          {
            "type": "PIN",
            "name": "UNKNOWN",
            "from_amount": 0,
            "to_amount": 9223372036854775807
          }
        ]
      },
      "contract": "none",
      "taxes": []
    },
    {
      "method": "praxis",
      "integration": "praxis",
      "sub_method": null,
      "name": "CLABE",
      "network_name": "Bank",
      "network_description": "CLABE or Debit Card <br> No fees",
      "required_fields": [
        "beneficiary",
        "clabe"
      ],
      "optional_fields": [
        "sender_rfc",
        "rfc",
        "bank_code",
        "notes_ref",
        "numeric_ref",
        "origin_id",
        "client_withdrawal_id",
        "max_fee"
      ],
      "currency_configurations": [
        {
          "currency": "mxn",
          "legal_operating_entity": {
            "legal_operation_entity": "Nvio Pagos Mexico",
            "country_code": "MX"
          },
          "fee": {
            "amount": "0.00000000",
            "type": "fixed"
          },
          "limits": {
            "system_min": "0.00100000",
            "system_max": "36000.00000000",
            "tx_limit": "36000.00000000",
            "status": "upgradeable"
          },
          "status": {
            "type": "active",
            "description": "Ok"
          },
          "asset": "mxn"
        }
      ],
      "consumer_contacts_enabled": true,
      "method_description": "To any bank account via SPEI.",
      "icon_config": {
        "path": "\/assets\/icon\/withdrawal_methods\/",
        "name": "bank"
      },
      "tags": [
        {
          "title": "Zero-fee",
          "type": "SUCCESS"
        },
        {
          "title": "Up to 24 business hours",
          "type": "INFO"
        }
      ],
      "network": "spei",
      "protocol": "clabe",
      "security": {
        "modes": [
          {
            "type": "PIN",
            "name": "UNKNOWN",
            "from_amount": 0,
            "to_amount": 10999.99999999
          },
          {
            "from_amount": 10999.99999999,
            "to_amount": 9223372036854775807,
            "name": "withdrawal_high_risk",
            "type": "OTP"
          }
        ]
      },
      "contract": "none",
      "taxes": []
    },
    {
      "method": "praxis",
      "integration": "praxis",
      "sub_method": null,
      "name": "Debit card",
      "network_name": "Bank",
      "network_description": "CLABE or Debit Card <br> No fees",
      "required_fields": [
        "beneficiary",
        "clabe",
        "institution_code"
      ],
      "optional_fields": [
        "sender_rfc",
        "rfc",
        "bank_code",
        "notes_ref",
        "numeric_ref",
        "origin_id",
        "client_withdrawal_id",
        "max_fee"
      ],
      "currency_configurations": [
        {
          "currency": "mxn",
          "legal_operating_entity": {
            "legal_operation_entity": "Nvio Pagos Mexico",
            "country_code": "MX"
          },
          "fee": {
            "amount": "0.00000000",
            "type": "fixed"
          },
          "limits": {
            "system_min": "0.00100000",
            "system_max": "36000.00000000",
            "tx_limit": "36000.00000000",
            "status": "upgradeable"
          },
          "status": {
            "type": "active",
            "description": "Ok"
          },
          "asset": "mxn"
        }
      ],
      "consumer_contacts_enabled": true,
      "method_description": "To any debit card via SPEI.",
      "icon_config": {
        "path": "\/assets\/icon\/withdrawal_methods\/",
        "name": "debitcard"
      },
      "tags": [
        {
          "title": "Zero-fee",
          "type": "SUCCESS"
        },
        {
          "title": "Up to 24 business hours",
          "type": "INFO"
        }
      ],
      "network": "spei",
      "protocol": "debitcard",
      "security": {
        "modes": [
          {
            "type": "PIN",
            "name": "UNKNOWN",
            "from_amount": 0,
            "to_amount": 10999.99999999
          },
          {
            "from_amount": 10999.99999999,
            "to_amount": 9223372036854775807,
            "name": "withdrawal_high_risk",
            "type": "OTP"
          }
        ]
      },
      "contract": "none",
      "taxes": []
    },
    {
      "method": "praxis",
      "integration": "praxis",
      "sub_method": null,
      "name": "Mobile number",
      "network_name": "Bank",
      "network_description": "CLABE or Debit Card <br> No fees",
      "required_fields": [
        "beneficiary",
        "clabe",
        "institution_code"
      ],
      "optional_fields": [
        "sender_rfc",
        "rfc",
        "bank_code",
        "notes_ref",
        "numeric_ref",
        "origin_id",
        "client_withdrawal_id",
        "max_fee"
      ],
      "currency_configurations": [
        {
          "currency": "mxn",
          "legal_operating_entity": {
            "legal_operation_entity": "Nvio Pagos Mexico",
            "country_code": "MX"
          },
          "fee": {
            "amount": "0.00000000",
            "type": "fixed"
          },
          "limits": {
            "system_min": "0.00100000",
            "system_max": "36000.00000000",
            "tx_limit": "36000.00000000",
            "status": "upgradeable"
          },
          "status": {
            "type": "active",
            "description": "Ok"
          },
          "asset": "mxn"
        }
      ],
      "consumer_contacts_enabled": true,
      "method_description": "To any mobile number via SPEI.",
      "icon_config": {
        "path": "\/assets\/icon\/withdrawal_methods\/",
        "name": "phonenum"
      },
      "tags": [
        {
          "title": "Zero-fee",
          "type": "SUCCESS"
        },
        {
          "title": "Up to 24 business hours",
          "type": "INFO"
        }
      ],
      "network": "spei",
      "protocol": "phonenum",
      "security": {
        "modes": [
          {
            "type": "PIN",
            "name": "UNKNOWN",
            "from_amount": 0,
            "to_amount": 10999.99999999
          },
          {
            "from_amount": 10999.99999999,
            "to_amount": 9223372036854775807,
            "name": "withdrawal_high_risk",
            "type": "OTP"
          }
        ]
      },
      "contract": "none",
      "taxes": []
    }
  ]
}
JSON;
        $client = $this->getMockClient($jsonWithdrawalMethodsResponse);

        $payouts = new Payouts($client, $this->createMock(Bitso::class));

        // assertions
        $this->assertIsArray($payouts->withdrawalMethods('mxn'));
        $this->assertArrayHasKey('method', $payouts->withdrawalMethods('mxn')[0]);

    }

    public function getMockClient(string $json)
    {
        $bitso_client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response = json_decode($json, true);

        $bitso_client->method('getData')
            ->willReturn($response);

        return $bitso_client;
    }
}
