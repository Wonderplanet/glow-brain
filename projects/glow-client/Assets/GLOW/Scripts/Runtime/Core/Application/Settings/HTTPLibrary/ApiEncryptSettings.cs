using GLOW.Core.Constants;
using UnityHTTPLibrary;
using UnityHTTPLibrary.Cryptography;

namespace GLOW.Core.Application.Settings.HTTPLibrary
{
    public class ApiEncryptSettings: IEncryptionSettings
    {
        public IRequestEncryptor RequestEncryptor =>
#if DISABLE_API_REQUEST_ENCRYPTION
            // NOTE: nullを返すと暗号化リクエストを行わない
            null;
#else
            new AesRequestEncryptor(Credentials.HttpEncryptionPw);
#endif // DISABLE_API_REQUEST_ENCRYPTION

        public IResponseDecryptor ResponseDecryptor => new AesResponseDecryptor(Credentials.HttpDecryptionPw);
    }
}
