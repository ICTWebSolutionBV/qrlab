<?php

declare(strict_types=1);

namespace Tests\Unit;

use Cose\BigInteger as CoseBigInteger;
use Cose\Key\Key;
use Cose\Key\RsaKey;
use PHPUnit\Framework\TestCase;

/**
 * Regression canary for the arbitrary-precision arithmetic layer underneath passkey
 * verification.
 *
 * brick/math is not referenced by any application code, but it sits under the WebAuthn
 * stack: Cose\BigInteger wraps it directly, Cose\Key\RsaKey uses it to turn a COSE RSA
 * public key into the PEM that openssl then verifies signatures against, and
 * spomky-labs/pki-framework uses it for the ASN.1 integer encoding in that same path.
 * A behaviour change there breaks passkey login rather than failing loudly at install,
 * so these tests pin the conversions with fixed vectors.
 *
 * The fixtures are a throwaway RSA public key plus a signature generated over a known
 * message. No private key is committed - the signature is precomputed, so the test
 * verifies rather than signs.
 */
final class WebAuthnArithmeticTest extends TestCase
{
    /**
     * Modulus (n) of the fixture key, as the raw big-endian bytes COSE stores.
     */
    private const N_B64 = '0i3jHwV6hu8Cw1CALXVF6Hng5MBeunET4Tcq/GlDo1SbvYAYJBAaRurh3wZ11X1KBN6gOfSeaKwCNoZ05y6C'
        . '9hYs3/ar8w0nYDzoZjD2iTAMoM5iO3GcHvlBJvZ55O9IFRRVegY540MwKbiEdNvCZSTBP25uvMDLhBt+BA7itLsK'
        . 'SG9U3qCD/ADByrw8pwZ3XiWkW7bJh8skpL/b0RWI46ZleNo4xLMAEEijRjzAsHawXd4jRoGtuXlqw+M9dmyk39Ao'
        . 's/HMYP5CHDISy7Q42kpY0G47vVZHg40b2eptAXkP/RxM3a4EBLwWlOcYr60zF10xAov9c1+ckEbyovpJIQ==';

    /**
     * Public exponent (e), 65537.
     */
    private const E_B64 = 'AQAB';

    private const MESSAGE = 'qrlab-webauthn-brickmath-canary';

    private const SIG_B64 = 'O+NlGXJAcv5qJDggqerQpaamgu/x4n2pGRZ46Du5axNhI8C+xP+HrcxWw7HJwKP/aa0VSyUoKgy3bZri'
        . 'Q3+9rB6y5rDpw45LMoJtGbPZpCH7yzwRBbaiafmfuPIGILxDe7mOtVSjld35gXnBPZBjx5tARrRz942WkFko+vH9'
        . 'MedxaCBX7MPyy6elE153tMGs2oFEIjSDY6rps6ksn0fmPbQ2RNtT+MhBnwCN/H+gRgTtm8Wxtws9HhM+LgOWIbGR'
        . 'NursBkBVm7Z/MuKrKB/sVgSV1BXnnhjArW75bHXNwBjHaGSHxtVUX+iMgpZ6RO3fCncsWYhgUTMDryOzsdu7Sg==';

    private const EXPECTED_PEM = <<<'PEM'
        -----BEGIN PUBLIC KEY-----
        MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0i3jHwV6hu8Cw1CALXVF
        6Hng5MBeunET4Tcq/GlDo1SbvYAYJBAaRurh3wZ11X1KBN6gOfSeaKwCNoZ05y6C
        9hYs3/ar8w0nYDzoZjD2iTAMoM5iO3GcHvlBJvZ55O9IFRRVegY540MwKbiEdNvC
        ZSTBP25uvMDLhBt+BA7itLsKSG9U3qCD/ADByrw8pwZ3XiWkW7bJh8skpL/b0RWI
        46ZleNo4xLMAEEijRjzAsHawXd4jRoGtuXlqw+M9dmyk39Aos/HMYP5CHDISy7Q4
        2kpY0G47vVZHg40b2eptAXkP/RxM3a4EBLwWlOcYr60zF10xAov9c1+ckEbyovpJ
        IQIDAQAB
        -----END PUBLIC KEY-----
        PEM;

    private static function modulus(): string
    {
        return base64_decode(str_replace(' ', '', self::N_B64), true);
    }

    private static function exponent(): string
    {
        return base64_decode(self::E_B64, true);
    }

    private static function rsaKey(): RsaKey
    {
        return RsaKey::create([
            Key::TYPE => Key::TYPE_RSA,
            RsaKey::DATA_N => self::modulus(),
            RsaKey::DATA_E => self::exponent(),
        ]);
    }

    /**
     * The COSE -> PEM conversion runs binary -> Brick\Math\BigInteger (base 16 in,
     * base 10 out) -> pki-framework ASN.1 -> PEM. Pinning the exact output catches a
     * change in either the base conversion or the integer encoding.
     */
    public function test_cose_rsa_public_key_converts_to_the_expected_pem(): void
    {
        $pem = self::rsaKey()->asPem();

        $this->assertSame(
            self::normalisePem(self::EXPECTED_PEM),
            self::normalisePem($pem),
            'COSE->PEM conversion changed. The arithmetic layer under passkey verification '
            . '(brick/math via Cose\\Key\\RsaKey and spomky-labs/pki-framework) no longer '
            . 'produces the same key encoding.'
        );
    }

    /**
     * Functional half: a PEM that merely looks right is not enough - openssl has to be
     * able to verify a real signature with it.
     */
    public function test_derived_pem_verifies_a_known_signature(): void
    {
        $pem = self::rsaKey()->asPem();
        $signature = base64_decode(str_replace(' ', '', self::SIG_B64), true);

        $result = openssl_verify(self::MESSAGE, $signature, $pem, OPENSSL_ALGO_SHA256);

        $this->assertSame(1, $result, 'Signature verification through the derived PEM failed.');
    }

    /**
     * A tampered message must not verify - guards against the assertion above passing
     * for the wrong reason.
     */
    public function test_derived_pem_rejects_a_tampered_message(): void
    {
        $pem = self::rsaKey()->asPem();
        $signature = base64_decode(str_replace(' ', '', self::SIG_B64), true);

        $result = openssl_verify(self::MESSAGE . 'x', $signature, $pem, OPENSSL_ALGO_SHA256);

        $this->assertNotSame(1, $result, 'A tampered message verified against the signature.');
    }

    /**
     * Cose\BigInteger wraps brick/math directly. Round-tripping the modulus exercises
     * createFromBinaryString/toBytes, which is where a base-conversion or leading-zero
     * change would surface.
     */
    public function test_cose_big_integer_round_trips_the_modulus(): void
    {
        $modulus = self::modulus();

        $roundTripped = CoseBigInteger::createFromBinaryString($modulus)->toBytes();

        $this->assertSame(bin2hex($modulus), bin2hex($roundTripped));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('modPowVectors')]
    public function test_cose_big_integer_mod_pow(int $base, int $exponent, int $modulus, int $expected): void
    {
        $result = CoseBigInteger::createFromDecimal($base)
            ->modPow(
                CoseBigInteger::createFromDecimal($exponent),
                CoseBigInteger::createFromDecimal($modulus)
            );

        $this->assertSame(
            $expected,
            (int) hexdec(bin2hex($result->toBytes()) ?: '0')
        );
    }

    /**
     * @return array<string, array{int, int, int, int}>
     */
    public static function modPowVectors(): array
    {
        return [
            'rsa-like exponent' => [2, 65537, 3233, self::refModPow(2, 65537, 3233)],
            'textbook rsa'      => [790, 17, 3233, self::refModPow(790, 17, 3233)],
            'identity'          => [7, 1, 13, 7],
            'zero exponent'     => [9, 0, 5, 1],
        ];
    }

    /**
     * Independent reference implementation, so the expectations are not produced by the
     * same library under test.
     */
    private static function refModPow(int $base, int $exponent, int $modulus): int
    {
        $result = 1;
        $base %= $modulus;
        while ($exponent > 0) {
            if ($exponent % 2 === 1) {
                $result = ($result * $base) % $modulus;
            }
            $exponent = intdiv($exponent, 2);
            $base = ($base * $base) % $modulus;
        }

        return $result;
    }

    private static function normalisePem(string $pem): string
    {
        return preg_replace('/\s+/', '', $pem) ?? '';
    }
}
