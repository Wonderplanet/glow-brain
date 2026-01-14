# Support Traitsガイド

## TestRewardTrait

報酬送信処理

```php
use Tests\Support\Traits\TestRewardTrait;

class ItemServiceTest extends TestCase
{
    use TestRewardTrait;

    public function test_apply_アイテム使用()
    {
        $this->itemService->apply($usrUserId, $platform, $mstItem, $amount, $now);
        $this->sendRewards($usrUserId, $platform, $now); // 報酬送信
        $this->saveAll();
    }
}
```

## TestMissionTrait

ミッション状態確認

```php
use Tests\Support\Traits\TestMissionTrait;

$this->checkUsrMissionStatus(
    $usrMissions,
    $mstMissionId,
    $isExist,
    $isClear,
    $clearedAt,
    $isReceiveReward,
    $receivedRewardAt
);
```

## TestMultipleApiRequestsTrait

複数APIリクエスト（Scenario Testで必須）

```php
use Tests\Support\Traits\TestMultipleApiRequestsTrait;

class TutorialScenarioTest extends BaseControllerTestCase
{
    use TestMultipleApiRequestsTrait;

    public function test_チュートリアル完了フロー()
    {
        // 1回目
        $response1 = $this->sendRequest('tutorial/update_status', $params1);
        $this->resetAppForNextRequest($usrUserId); // 必須

        // 2回目
        $response2 = $this->sendRequest('tutorial/update_status', $params2);
        $this->resetAppForNextRequest($usrUserId); // 必須
    }
}
```

## TestBankKpiTrait

Bank KPIログ確認

```php
use Tests\Support\Traits\TestBankKpiTrait;

$this->checkBankLogByEventId(
    $result['currency_summary']->getUserId(),
    BankKPIF001EventId::USER_REGISTERED->value
);
```
