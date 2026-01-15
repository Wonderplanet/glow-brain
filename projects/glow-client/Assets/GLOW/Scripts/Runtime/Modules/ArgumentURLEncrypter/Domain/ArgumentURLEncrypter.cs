using System;
using System.Collections.Generic;
using System.IO;
using System.Text;
using System.Security.Cryptography;

namespace GLOW.Modules.ArgumentURLEncrypter.Domain
{
    public static class ArgumentURLEncrypter
    {
        const int KeySize = 32;
        const int Iterations = 1000;
        
        /// <summary>
        /// BNEレギュレーションに従った引数の暗号化を行う
        /// </summary>
        /// <param name="userId">アプリユーザ ID</param>
        /// <param name="commonKey">品証システムの共通鍵</param>
        /// <param name="saltStr">暗号キーに付与するランダムデータ</param>
        /// <returns>Dictionary<string, string>{uid, dt, tm, rid}</returns>
        public static Dictionary<string, string> Encrypt(string userId, string commonKey, string saltStr)
        {
            var encUserId = Convert.ToBase64String(Encoding.UTF8.GetBytes(userId));
            var keys = new string[5]; // index 0 は未使用
            var now = DateTime.Now;
            var yyyymmdd = now.ToString("yyyyMMdd");
            
            var hhmi = now.ToString("HHmm");
            var mmyyyydd = now.ToString("MMyyyydd").ToCharArray();
            Array.Reverse(mmyyyydd);
            keys[1] = string.Join("", mmyyyydd);
            var mihh = now.ToString("mmHH").ToCharArray();
            Array.Reverse(mihh);
            keys[2] = string.Join("", mihh);
            keys[3] = commonKey;
            var rnd = (new Random()).Next(10000).ToString("0000"); // 0 以上、10000 未満
            keys[4] = rnd;
            var key = keys[2] + keys[4] + keys[1] + keys[3];
            var salt = Encoding.UTF8.GetBytes(saltStr);
            var hashKey = (new Rfc2898DeriveBytes(key, salt, Iterations,
                HashAlgorithmName.SHA1)).GetBytes(KeySize);
            
            var aes = new AesManaged(); // Mode: Default(CBC), Padding: Default(PKCS7)
            aes.Mode = CipherMode.CBC;
            aes.Padding = PaddingMode.PKCS7;
            aes.GenerateIV(); // AES の IV は 16 バイト
            var iv = aes.IV;
            var encryptor = aes.CreateEncryptor(hashKey, iv);
            byte[] encrypted;
            using (MemoryStream ms = new MemoryStream())
            {
                using (CryptoStream cs = new CryptoStream(ms, encryptor, CryptoStreamMode.Write))
                {
                    using (StreamWriter sw = new StreamWriter(cs))
                    {
                        sw.Write(encUserId);
                    }
                    encrypted = ms.ToArray();
                }
            }
            var encryptedStr = Convert.ToBase64String(encrypted);
            encryptedStr = BitConverter.ToString(Encoding.UTF8.GetBytes(encryptedStr)).Replace("-", 
                string.Empty);
            var ivStr = BitConverter.ToString(iv).Replace("-", string.Empty);
            var result = saltStr + ivStr + encryptedStr;
            var paramDictionary = new Dictionary<string, string>()
            {
                {"uid", result},    // 暗号化されたユーザ ID
                {"dt", yyyymmdd},   // 日付
                {"tm", hhmi},       // 時間
                {"rid", rnd},       // 4 桁のランダム値
            };
            return paramDictionary;
        }
    }
}