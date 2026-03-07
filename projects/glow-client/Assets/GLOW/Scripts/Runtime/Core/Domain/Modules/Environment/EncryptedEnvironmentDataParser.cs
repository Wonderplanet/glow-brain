using System;
using System.Text;
using GLOW.Core.Constants;
using Newtonsoft.Json;
using WonderPlanet.HashCalculator;
using WondlerPlanet.CheatProtectKit.Encryption;
using WPFramework.Domain.Modules;
using WPFramework.Modules.Environment;
using Zenject;

namespace GLOW.Core.Domain.Modules.Environment
{
    public sealed class EncryptedEnvironmentDataParser : IEnvironmentDataParser
    {
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }

        T IEnvironmentDataParser.Parse<T>(byte[] bytes)
        {
            return Parse<T>(bytes);
        }

        T IEnvironmentDataParser.Parse<T>(string text)
        {
            if (IsEncrypted(text))
            {
                return Parse<T>(Convert.FromBase64String(text));
            }

            return JsonConvert.DeserializeObject<T>(text);
        }

        bool IsEncrypted(string text)
        {
            if (string.IsNullOrEmpty(text))
            {
                return false;
            }

            var trimmedText = text.Trim();
            if (trimmedText.StartsWith("{") || trimmedText.StartsWith("["))
            {
                return false;
            }

            try
            {
                Convert.FromBase64String(trimmedText);
                return true;
            }
            catch
            {
                return false;
            }
        }
        
        string BuildClientVersionHash(string applicationVersion, string fixedIdentifier)
        {
            // NOTE: https://wonderplanet.atlassian.net/wiki/spaces/SEED/pages/257130746/SEED+_
            var hashGenerator = new MD5HashGenerator();
            var arrangeApplicationVersion = applicationVersion.Replace(".", "_");
            var hashTarget = $"{applicationVersion}_{fixedIdentifier}";
            var hash = hashGenerator.GetHash(hashTarget);
            var targetString = $"{arrangeApplicationVersion}_{hash}";
            return hashGenerator.GetHash(targetString);
        }
        
        T Parse<T>(byte[] bytes)
        {
            try
            {
                var applicationVersion = SystemInfoProvider.GetApplicationSystemInfo().Version;
                var salt = BuildClientVersionHash(applicationVersion, Credentials.EnvironmentDataKey);
                    
                var decryptedBytes = AesEncryption.Decrypt(bytes, Credentials.EnvironmentDataPw, salt);
                var decryptedText = Encoding.UTF8.GetString(decryptedBytes);
                return JsonConvert.DeserializeObject<T>(decryptedText);
            }
            catch (Exception ex)
            {
                throw new InvalidOperationException($"Failed to decrypt environment data: {ex.Message}", ex);
            }
        }
    }
}