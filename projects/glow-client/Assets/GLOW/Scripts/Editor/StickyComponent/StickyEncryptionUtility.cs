using System;
using System.Text;
using WondlerPlanet.CheatProtectKit.Encryption;

namespace GLOW.Editor.StickyComponent
{
    public static class StickyEncryptionUtility
    {
        const string AES_PASSWORD = "Gl0wSt1ckyAESK3y!2024";
        const string AES_SALT = "St1ckyS@lt#GLOW";
        const string XOR_KEY = "Gl0wSt1ckyN0t3K3y!";
        
        public static string EncryptTextAES(string plainText)
        {
            if (string.IsNullOrEmpty(plainText)) return string.Empty;
            
            try
            {
                var plainBytes = Encoding.UTF8.GetBytes(plainText);
                var encryptedBytes = AesEncryption.Encrypt(plainBytes, AES_PASSWORD, AES_SALT);
                return Convert.ToBase64String(encryptedBytes);
            }
            catch (Exception e)
            {
                UnityEngine.Debug.LogError($"AES encryption failed: {e.Message}");
                return string.Empty;
            }
        }
        
        public static string DecryptTextAES(string encryptedText)
        {
            if (string.IsNullOrEmpty(encryptedText)) return string.Empty;
            
            try
            {
                var encryptedBytes = Convert.FromBase64String(encryptedText);
                var decryptedBytes = AesEncryption.Decrypt(encryptedBytes, AES_PASSWORD, AES_SALT);
                return Encoding.UTF8.GetString(decryptedBytes);
            }
            catch (Exception e)
            {
                UnityEngine.Debug.LogError($"AES decryption failed: {e.Message}");
                return string.Empty;
            }
        }
        
        public static string DecryptTextXOR(string encryptedText)
        {
            if (string.IsNullOrEmpty(encryptedText)) return string.Empty;
            
            try
            {
                var encryptedBytes = Convert.FromBase64String(encryptedText);
                var keyBytes = Encoding.UTF8.GetBytes(XOR_KEY);
                var decryptedBytes = new byte[encryptedBytes.Length];
                
                for (int i = 0; i < encryptedBytes.Length; i++)
                {
                    decryptedBytes[i] = (byte)(encryptedBytes[i] ^ keyBytes[i % keyBytes.Length]);
                }
                
                return Encoding.UTF8.GetString(decryptedBytes);
            }
            catch
            {
                return string.Empty;
            }
        }
    }
}