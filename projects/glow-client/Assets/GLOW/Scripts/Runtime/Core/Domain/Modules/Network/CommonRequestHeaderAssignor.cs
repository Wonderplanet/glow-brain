using System.Collections.Generic;
using System.Text;
using UnityHTTPLibrary;
using WPFramework.Domain.Modules;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Core.Domain.Modules.Network
{
    public sealed class CommonRequestHeaderAssignor : ICommonRequestHeaderAssignor
    {
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        [Inject] IEncryptionSettings EncryptionSettings { get; }

        void ICommonRequestHeaderAssignor.SetRequestHeaders(ServerApi context)
        {
            var headers = new Dictionary<string, string>(context.AdditionalRequestHeaders ?? new Dictionary<string, string>());
            var applicationSystemInfoInfo = SystemInfoProvider.GetApplicationSystemInfo();

            headers[RequestHeader.Common.BundleVersion] = applicationSystemInfoInfo.Version;
            headers[RequestHeader.Common.Platform] = applicationSystemInfoInfo.PlatformId;
            headers[RequestHeader.Common.ApplicationLanguage] = applicationSystemInfoInfo.ApplicationRegionCode;

            // NOTE: 暗号化リクエストの設定がある場合は暗号化リクエストを行う
            //       開発環境でのみこのリクエストパラメータが判断に利用される予定
            //       本番環境では常に暗号化リクエストしか受け取らないようになる
            var requestEncrypted = EncryptionSettings?.RequestEncryptor != null;
            headers[RequestHeader.Common.RequestEncrypted] = requestEncrypted.ToString().ToLower();

            var builder = new StringBuilder();
            foreach (var header in headers)
            {
                builder.AppendLine($"{header.Key}:{header.Value}");
            }
            ApplicationLog.Log(nameof(CommonRequestHeaderAssignor), $"Set Request Headers\n{builder}");

            context.AdditionalRequestHeaders = headers;
        }
    }
}
