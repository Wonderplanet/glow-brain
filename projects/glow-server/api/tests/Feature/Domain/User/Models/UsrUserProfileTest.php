<?php

namespace Tests\Feature\Domain\User;

use App\Domain\User\Models\UsrUserProfile;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class UsrUserProfileTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * データが暗号化されているかを確認するメソッド
     * データを復号化してみて、エラーが発生しないことを確認
     */
    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 同じ値を暗号化しても毎回異なるデータになるので、DBの値が暗号化されていることを確認する
     *
     * @return void
     */
    public function test_setBirthDate_暗号化してデータを設定できる()
    {
        // Setup
        $usrUserProfile = UsrUserProfile::factory()->create([
            'birth_date' => '',
        ]);
        $intBirthDate = 20051201;

        // Exercise
        $usrUserProfile->setBirthDate($intBirthDate);

        // Verify
        $actual = $usrUserProfile->getAttribute('birth_date');
        $this->assertNotEquals($intBirthDate, $actual);
        $this->assertTrue($this->isEncrypted($actual));
    }

    public function test_getBirthDate_暗号化してデータを設定できる()
    {
        // Setup
        $intBirthDate = 20051202;
        $usrUserProfile = UsrUserProfile::factory()->create([
            'birth_date' => Crypt::encryptString((string) $intBirthDate),
        ]);

        // Exercise
        $birthDate = $usrUserProfile->getBirthDate();

        // Verify
        $this->assertEquals($intBirthDate, (int) $birthDate);
    }
}
