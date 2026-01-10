using GLOW.Core.Constants;
using WonderPlanet.HashCalculator;
using WondlerPlanet.CheatProtectKit.Encryption;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;
using WPFramework.Modules.Compressors;

namespace GLOW.Core.Data.DataStores.Decryptor
{
    public class MasterDataDecryptor : IMasterDataDecryptor
    {
        ObscuredString _hash;

        /// <summary>
        /// hashをsaltとして使ってデータを復号化するクラス
        /// 圧縮 → 暗号化の順で処理されている想定
        /// </summary>
        /// <param name="hash">salt生成用のhash値</param>
        public MasterDataDecryptor(string hash)
        {
            _hash = hash;
        }

        byte[] IMasterDataDecryptor.Decrypt(byte[] data)
        {
            // NOTE: Masterのファイルハッシュ値からsaltを作成する
            //       (laravel-wp-encryptionの処理に合わせている)
            using var generator = new SHA256HashGenerator();
            ObscuredString salt = generator.GetHash(_hash);

            data = AesEncryption.Decrypt(data, Credentials.MstDataDecryptionPw, salt);

            // NOTE: Gzip圧縮がかかっている想定
            data = GzipCompressor.Decompress(data);

            return data;
        }
    }
}