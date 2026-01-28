using System.IO;
using System.Text;
using GLOW.Core.Modules.Authenticate.Log;
using Newtonsoft.Json;
using UnityEngine;
using UnityHTTPLibrary.Authenticate.Support.FilePermission.iOS;
using UnityHTTPLibrary.Cryptography;

namespace GLOW.Core.Modules.Authenticate.Token
{
    internal class GlowIDTokenStorage
    {
        readonly string _environmentIdentifier;

        string Path =>
            System.IO.Path.Combine(Application.persistentDataPath,
                string.IsNullOrEmpty(_environmentIdentifier)
                    ? @"authenticate_id_token.bin"
                    : $"{_environmentIdentifier}_authenticate_id_token.bin");

        public GlowIDTokenStorage(string environmentIdentifier)
        {
            _environmentIdentifier = environmentIdentifier;
        }

        public void Write(string idToken, string identifier, string password)
        {
            // NOTE: 何で取得したidTokenなのかの情報を付与する
            var tokenData = new GlowIDTokenData(idToken, identifier);
            var jsonString = JsonConvert.SerializeObject(tokenData);

            // NOTE: パスワードは必要な時にもらうようにする
            File.WriteAllBytes(Path,
                AesEncryption.EncryptSaltInManaged(
                    Encoding.ASCII.GetBytes(jsonString), password));

            iOSFilePermission.SetFilePermission(Path);

            GlowAuthenticationLogger.Log($"{Path}に認証情報を保存します");
        }

        public GlowIDTokenData Read(string password)
        {
            GlowAuthenticationLogger.Log($"{Path}から認証情報を読み込みます");

            var bytes = File.ReadAllBytes(Path);
            // NOTE: パスワードは必要な時にもらうようにする
            var jsonString =
                Encoding.ASCII.GetString(
                    AesEncryption.DecryptSaltInManaged(bytes, password));
            return JsonConvert.DeserializeObject<GlowIDTokenData>(jsonString);
        }

        public bool Exists()
        {
            return File.Exists(Path);
        }

        public void Delete()
        {
            if (!Exists())
            {
                return;
            }

            File.Delete(Path);

            GlowAuthenticationLogger.Log($"{Path}の認証情報を削除しました");
        }
    }
}
